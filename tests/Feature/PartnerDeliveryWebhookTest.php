<?php

namespace Tests\Feature;

use App\Jobs\SendPartnerWebhook;
use App\Models\Admin\Driver;
use App\Models\Request\Request as TripRequest;
use App\Models\Request\RequestBill;
use App\Models\Request\RequestMeta;
use App\Models\User;
use App\Services\Partners\PartnerRegistry;
use App\Services\Partners\WaitingRequestCanceller;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

/**
 * Integration-partner accounts (EstuaireAchats deliveries): no auto-cancel of the previous
 * waiting request, and signed outbound webhooks on every request state change.
 * Runs on the phpunit.xml sqlite :memory: connection with a minimal hand-built schema.
 */
class PartnerDeliveryWebhookTest extends TestCase
{
    private const PARTNER_ID = 50;

    private const SECRET = 'test-webhook-secret-0123456789';

    private const URL = 'https://partner.test/api/v1/delivery/webhook/merci-e';

    protected function setUp(): void
    {
        parent::setUp();

        $this->assertSame('sqlite', DB::connection()->getDriverName(), 'refusing to run outside sqlite');

        config(['partners.partners' => [
            self::PARTNER_ID => ['webhook_url' => self::URL, 'webhook_secret' => self::SECRET],
        ]]);

        Schema::create('users', function (Blueprint $t) {
            $t->increments('id');
            $t->string('name')->nullable();
            $t->string('mobile')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::create('drivers', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('user_id')->nullable();
            $t->string('name')->nullable();
            $t->string('mobile')->nullable();
            $t->string('car_number')->nullable();
            $t->string('car_color')->nullable();
            $t->string('car_make')->nullable();
            $t->string('car_model')->nullable();
            $t->string('custom_make')->nullable();
            $t->string('custom_model')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::create('requests', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('request_number')->nullable();
            $t->unsignedInteger('user_id');
            $t->string('partner_reference')->nullable();
            $t->unsignedInteger('driver_id')->nullable();
            $t->string('transport_type')->nullable();
            $t->unsignedInteger('payment_opt')->nullable();
            $t->boolean('is_paid')->default(false);
            $t->string('ride_otp')->nullable();
            $t->boolean('is_driver_started')->default(false);
            $t->boolean('is_driver_arrived')->default(false);
            $t->boolean('is_trip_start')->default(false);
            $t->boolean('is_completed')->default(false);
            $t->boolean('is_cancelled')->default(false);
            $t->string('cancel_method')->nullable();
            $t->string('reason')->nullable();
            $t->string('custom_reason')->nullable();
            $t->string('requested_currency_code')->nullable();
            $t->uuid('zone_type_id')->nullable();
            $t->timestamp('accepted_at')->nullable();
            $t->timestamp('arrived_at')->nullable();
            $t->timestamp('trip_start_time')->nullable();
            $t->timestamp('completed_at')->nullable();
            $t->timestamp('cancelled_at')->nullable();
            $t->timestamps();
        });
        Schema::create('requests_meta', function (Blueprint $t) {
            $t->increments('id');
            $t->uuid('request_id');
            $t->unsignedInteger('user_id');
            $t->unsignedInteger('driver_id')->nullable();
            $t->boolean('active')->default(true);
            $t->timestamps();
        });
        Schema::create('request_bills', function (Blueprint $t) {
            $t->increments('id');
            $t->uuid('request_id');
            $t->double('total_amount')->default(0);
            $t->string('requested_currency_code')->nullable();
            $t->timestamps();
        });
    }

    private function user(int $id): User
    {
        DB::table('users')->insert(['id' => $id, 'name' => "User {$id}", 'mobile' => "65000000{$id}"]);

        return User::find($id);
    }

    private function waitingRequest(int $userId, ?string $reference = null): TripRequest
    {
        $request = TripRequest::create([
            'request_number' => 'REQ_'.uniqid(), 'user_id' => $userId, 'transport_type' => 'delivery',
            'payment_opt' => 1, 'ride_otp' => '4321', 'requested_currency_code' => 'XAF', 'partner_reference' => $reference,
        ]);
        RequestMeta::create(['request_id' => $request->id, 'user_id' => $userId, 'driver_id' => 7, 'active' => 1]);

        return $request;
    }

    private function driver(): Driver
    {
        DB::table('drivers')->insert(['id' => 7, 'name' => 'Paul Livreur', 'mobile' => '690000007', 'car_number' => 'CE-123-AB', 'car_color' => 'Rouge']);

        return Driver::find(7);
    }

    // --- auto-cancel of the previous waiting request ---------------------------------

    public function test_normal_user_new_request_still_cancels_previous_waiting_request(): void
    {
        Queue::fake();
        $user = $this->user(10);
        $previous = $this->waitingRequest(10);

        WaitingRequestCanceller::cancelPreviousFor($user);

        $previous->refresh();
        $this->assertTrue((bool) $previous->is_cancelled);
        $this->assertSame('1', (string) $previous->cancel_method);
        $this->assertSame(0, RequestMeta::where('user_id', 10)->count());
        Queue::assertNotPushed(SendPartnerWebhook::class);
    }

    public function test_partner_second_request_does_not_cancel_the_first(): void
    {
        Queue::fake();
        $partner = $this->user(self::PARTNER_ID);
        $first = $this->waitingRequest(self::PARTNER_ID, 'order-1');

        WaitingRequestCanceller::cancelPreviousFor($partner);

        $first->refresh();
        $this->assertFalse((bool) $first->is_cancelled);
        $this->assertSame(1, RequestMeta::where('user_id', self::PARTNER_ID)->count());
        Queue::assertNothingPushed();
    }

    // --- observer: one webhook per transition --------------------------------------------

    public function test_webhook_job_dispatched_on_each_partner_transition(): void
    {
        Queue::fake();
        $this->user(self::PARTNER_ID);
        $this->driver();
        $request = $this->waitingRequest(self::PARTNER_ID, 'order-42');

        $request->update(['driver_id' => 7, 'accepted_at' => now(), 'is_driver_started' => true]);
        $request->update(['is_driver_arrived' => true, 'arrived_at' => now()]);
        $request->update(['is_driver_arrived' => true]); // no change -> no event
        $request->update(['is_trip_start' => true, 'trip_start_time' => now()]);
        $request->update(['is_completed' => true, 'completed_at' => now()]);
        RequestBill::create(['request_id' => $request->id, 'total_amount' => 1500, 'requested_currency_code' => 'XAF']);

        $events = [];
        Queue::assertPushed(SendPartnerWebhook::class, function (SendPartnerWebhook $job) use (&$events, $request) {
            $this->assertSame($request->id, $job->requestId);
            $events[] = $job->event;

            return true;
        });
        $this->assertSame(['driver_assigned', 'driver_arrived', 'trip_started', 'completed', 'bill_available'], $events);
    }

    public function test_no_driver_cancellation_emits_cancelled_webhook(): void
    {
        Queue::fake();
        $this->user(self::PARTNER_ID);
        $request = $this->waitingRequest(self::PARTNER_ID, 'order-7');

        // Same update as NoDriversFoundHelper / NoDriverFoundNotifyJob / AssignDriversForRegularRides.
        TripRequest::find($request->id)->update(['is_cancelled' => true, 'cancel_method' => 0, 'cancelled_at' => date('Y-m-d H:i:s')]);
        // A second cancel write on an already-cancelled request is not a new transition.
        TripRequest::find($request->id)->update(['is_cancelled' => true]);

        Queue::assertPushed(SendPartnerWebhook::class, 1);
        Queue::assertPushed(SendPartnerWebhook::class, fn ($job) => $job->event === 'cancelled');
    }

    public function test_no_webhook_for_non_partner_requests(): void
    {
        Queue::fake();
        $this->user(11);
        $this->driver();
        $request = $this->waitingRequest(11);

        $request->update(['driver_id' => 7]);
        $request->update(['is_driver_arrived' => true]);
        $request->update(['is_trip_start' => true]);
        $request->update(['is_completed' => true]);
        RequestBill::create(['request_id' => $request->id, 'total_amount' => 1500]);

        Queue::assertNothingPushed();
    }

    // --- job: signed HTTP call -------------------------------------------------------------

    public function test_job_posts_signed_payload_to_partner_webhook(): void
    {
        Http::fake([self::URL => Http::response(['received' => true], 200)]);
        $this->user(self::PARTNER_ID);
        $this->driver();
        $request = $this->waitingRequest(self::PARTNER_ID, 'order-42');
        TripRequest::withoutEvents(fn () => $request->update(['driver_id' => 7, 'accepted_at' => now()]));

        $job = new SendPartnerWebhook($request->id, SendPartnerWebhook::DRIVER_ASSIGNED, now()->toIso8601String());
        $job->handle();

        Http::assertSentCount(1);
        Http::assertSent(function (HttpRequest $http) use ($request, $job) {
            $body = $http->body();
            $timestamp = (int) $http->header('X-MerciE-Timestamp')[0];
            $expected = 'sha256='.hash_hmac('sha256', $timestamp.'.'.$body, self::SECRET);

            $this->assertSame(self::URL, $http->url());
            $this->assertSame($expected, $http->header('X-MerciE-Signature')[0]);
            $this->assertSame('driver_assigned', $http->header('X-MerciE-Event')[0]);
            $this->assertSame($job->eventId, $http->header('X-MerciE-Event-Id')[0]);
            $this->assertLessThan(60, abs(time() - $timestamp));

            $payload = json_decode($body, true);
            $this->assertSame('driver_assigned', $payload['event']);
            $this->assertSame($request->id, $payload['request']['id']);
            $this->assertSame('order-42', $payload['request']['partner_reference']);
            $this->assertSame('driver_assigned', $payload['request']['status']);
            $this->assertSame('4321', $payload['request']['otp']);
            $this->assertSame('Paul Livreur', $payload['request']['driver']['name']);
            $this->assertSame('CE-123-AB', $payload['request']['driver']['car_number']);
            $this->assertStringNotContainsString(self::SECRET, $body);

            return true;
        });
    }

    public function test_job_throws_on_non_2xx_so_the_queue_retries(): void
    {
        Http::fake([self::URL => Http::response('down', 503)]);
        $this->user(self::PARTNER_ID);
        $request = $this->waitingRequest(self::PARTNER_ID, 'order-1');

        $this->expectException(RuntimeException::class);
        (new SendPartnerWebhook($request->id, SendPartnerWebhook::CANCELLED, now()->toIso8601String()))->handle();
    }

    public function test_job_payload_for_completed_includes_bill_and_no_otp(): void
    {
        Http::fake([self::URL => Http::response('', 204)]);
        $this->user(self::PARTNER_ID);
        $request = $this->waitingRequest(self::PARTNER_ID, 'order-9');
        TripRequest::withoutEvents(fn () => $request->update(['driver_id' => 7, 'is_trip_start' => true, 'is_completed' => true, 'completed_at' => now()]));
        RequestBill::withoutEvents(fn () => RequestBill::create(['request_id' => $request->id, 'total_amount' => 2500, 'requested_currency_code' => 'XAF']));

        (new SendPartnerWebhook($request->id, SendPartnerWebhook::COMPLETED, now()->toIso8601String()))->handle();

        Http::assertSent(function (HttpRequest $http) {
            $payload = json_decode($http->body(), true);
            $this->assertSame('completed', $payload['request']['status']);
            $this->assertSame(2500.0, (float) $payload['request']['bill']['total_amount']);
            $this->assertSame('XAF', $payload['request']['bill']['currency']);
            $this->assertNull($payload['request']['otp']);
            $this->assertNotNull($payload['request']['timestamps']['completed_at']);

            return true;
        });
    }

    public function test_cancelled_request_does_not_block_a_new_attempt_with_same_reference(): void
    {
        Queue::fake();
        $this->user(self::PARTNER_ID);
        $first = $this->waitingRequest(self::PARTNER_ID, 'order-77');

        // While active, the same reference resolves to the existing request (retry-safe create).
        $this->assertSame($first->id, PartnerRegistry::activeRequestFor(self::PARTNER_ID, 'order-77')?->id);

        // "No driver found" cancels it: a new attempt must be allowed.
        $first->update(['is_cancelled' => true, 'cancel_method' => 0]);
        $this->assertNull(PartnerRegistry::activeRequestFor(self::PARTNER_ID, 'order-77'));

        $second = $this->waitingRequest(self::PARTNER_ID, 'order-77');
        $this->assertSame($second->id, PartnerRegistry::activeRequestFor(self::PARTNER_ID, 'order-77')?->id);

        // Completed requests don't block either; other users' references are never matched.
        $second->update(['is_completed' => true]);
        $this->assertNull(PartnerRegistry::activeRequestFor(self::PARTNER_ID, 'order-77'));
        $this->assertNull(PartnerRegistry::activeRequestFor(999, 'order-77'));
    }

    public function test_signature_helper_matches_documented_scheme(): void
    {
        $this->assertSame(
            'sha256='.hash_hmac('sha256', '1700000000.{"a":1}', 'k'),
            PartnerRegistry::sign('k', 1700000000, '{"a":1}')
        );
    }
}
