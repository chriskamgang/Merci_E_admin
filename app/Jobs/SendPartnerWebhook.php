<?php

namespace App\Jobs;

use App\Models\Request\Request;
use App\Services\Partners\PartnerRegistry;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * POSTs a signed JSON event to an integration partner's webhook URL.
 *
 * Headers:
 *   X-MerciE-Event:     event name (see constants)
 *   X-MerciE-Event-Id:  unique id of this event (stable across retries; use it for idempotency)
 *   X-MerciE-Timestamp: unix seconds at send time
 *   X-MerciE-Signature: "sha256=" . hex(HMAC-SHA256(secret, "{timestamp}.{raw body}"))
 *
 * The payload is built at send time from the current database state (driver, bill),
 * while "event"/"occurred_at" describe the transition that triggered it.
 */
class SendPartnerWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public const DRIVER_ASSIGNED = 'driver_assigned';

    public const DRIVER_ARRIVED = 'driver_arrived';

    public const TRIP_STARTED = 'trip_started';

    public const COMPLETED = 'completed';

    public const CANCELLED = 'cancelled';

    public const BILL_AVAILABLE = 'bill_available';

    /** Events for which the delivery code (ride OTP) is relevant to the partner. */
    private const OTP_EVENTS = [self::DRIVER_ASSIGNED, self::DRIVER_ARRIVED, self::TRIP_STARTED];

    public int $tries = 8;

    public int $timeout = 30;

    public string $eventId;

    public function __construct(
        public string $requestId,
        public string $event,
        public string $occurredAt,
    ) {
        $this->eventId = (string) Str::uuid();
    }

    /** Seconds between attempts (10s, 30s, 1m, 5m, 15m, 30m, 1h). */
    public function backoff(): array
    {
        return [10, 30, 60, 300, 900, 1800, 3600];
    }

    public function handle(): void
    {
        $request = Request::find($this->requestId);
        if (! $request) {
            return;
        }

        $partner = PartnerRegistry::config($request->user_id);
        if (! $partner || empty($partner['webhook_url']) || empty($partner['webhook_secret'])) {
            Log::warning('Partner webhook skipped: partner webhook URL/secret not configured', [
                'user_id' => $request->user_id, 'request_id' => $request->id, 'event' => $this->event,
            ]);

            return;
        }

        $body = json_encode($this->payload($request), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $timestamp = time();

        $response = Http::timeout(config('partners.webhook_timeout', 15))
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => 'MerciE-Webhooks/1.0',
                'X-MerciE-Event' => $this->event,
                'X-MerciE-Event-Id' => $this->eventId,
                'X-MerciE-Timestamp' => (string) $timestamp,
                'X-MerciE-Signature' => PartnerRegistry::sign($partner['webhook_secret'], $timestamp, $body),
            ])
            ->withBody($body, 'application/json')
            ->post($partner['webhook_url']);

        if (! $response->successful()) {
            // Throwing lets the queue retry with backoff.
            throw new RuntimeException("Partner webhook {$this->event} for request {$request->id} failed with HTTP {$response->status()}");
        }
    }

    public function failed(Throwable $e): void
    {
        Log::error('Partner webhook permanently failed', [
            'request_id' => $this->requestId, 'event' => $this->event, 'event_id' => $this->eventId, 'error' => $e->getMessage(),
        ]);
    }

    public function payload(Request $request): array
    {
        $driver = $this->safe(fn () => $request->driverDetail);
        $bill = $this->safe(fn () => $request->requestBill);

        $payload = [
            'event_id' => $this->eventId,
            'event' => $this->event,
            'occurred_at' => $this->occurredAt,
            'request' => [
                'id' => $request->id,
                'request_number' => $request->request_number,
                'partner_reference' => $request->partner_reference,
                'transport_type' => $request->transport_type,
                'status' => $this->currentStatus($request),
                'payment_opt' => $request->payment_opt === null ? null : (int) $request->payment_opt,
                'is_paid' => (bool) $request->is_paid,
                'otp' => in_array($this->event, self::OTP_EVENTS, true) ? ($request->ride_otp ? (string) $request->ride_otp : null) : null,
                'driver' => $driver ? [
                    'name' => $driver->name,
                    'mobile' => $this->safe(fn () => $driver->masked_mobile_number) ?? $driver->mobile,
                    'vehicle_type' => $this->safe(fn () => $request->vehicle_type_name),
                    'car_number' => $driver->car_number,
                    'car_color' => $driver->car_color,
                    'car_make' => $this->safe(fn () => $driver->car_make_name),
                    'car_model' => $this->safe(fn () => $driver->car_model_name),
                ] : null,
                'bill' => $bill ? [
                    'total_amount' => (float) $bill->total_amount,
                    'currency' => $bill->requested_currency_code ?: $request->requested_currency_code,
                ] : null,
                'cancellation' => $request->is_cancelled ? [
                    'cancelled_by' => $this->cancelledBy($request->cancel_method),
                    'no_driver_found' => (string) $request->cancel_method === '0' && ! $request->driver_id,
                    'reason' => $request->custom_reason ?: null,
                ] : null,
                'timestamps' => [
                    'created_at' => $this->iso($request->created_at),
                    'accepted_at' => $this->iso($request->accepted_at),
                    'arrived_at' => $this->iso($request->arrived_at),
                    'trip_started_at' => $request->is_trip_start ? $this->iso($request->trip_start_time) : null,
                    'completed_at' => $this->iso($request->completed_at),
                    'cancelled_at' => $this->iso($request->cancelled_at),
                ],
            ],
        ];

        return $payload;
    }

    private function currentStatus(Request $request): string
    {
        return match (true) {
            (bool) $request->is_completed => self::COMPLETED,
            (bool) $request->is_cancelled => self::CANCELLED,
            (bool) $request->is_trip_start => self::TRIP_STARTED,
            (bool) $request->is_driver_arrived => self::DRIVER_ARRIVED,
            (bool) $request->driver_id => self::DRIVER_ASSIGNED,
            default => 'searching',
        };
    }

    private function cancelledBy($method): string
    {
        return match ((string) $method) {
            '0' => 'system',
            '1' => 'user',
            '2' => 'driver',
            '3' => 'dispatcher',
            default => 'unknown',
        };
    }

    private function iso($value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value, config('app.timezone', 'UTC'))->toIso8601String();
        } catch (Throwable) {
            return null;
        }
    }

    private function safe(callable $fn)
    {
        try {
            return $fn();
        } catch (Throwable) {
            return null;
        }
    }
}
