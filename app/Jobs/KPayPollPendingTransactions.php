<?php

namespace App\Jobs;

use App\Jobs\Notifications\SendPushNotification;
use App\Models\Payment\PawaPayTransaction;
use App\Services\KPayService;
use App\Services\WalletService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Polls KPay for pending KPay deposits / payouts and settles them.
 *
 * Every settlement goes through WalletService, which locks the transaction row and only
 * acts while it is still `pending` — so running this job concurrently with itself or with
 * the app's status-polling endpoints can never credit or refund twice.
 */
class KPayPollPendingTransactions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function handle(KPayService $kpay): void
    {
        if (! $kpay->isConfigured()) {
            return;
        }

        // pawapay_transactions is shared with GFSolutions (provider = GFSOLUTIONS):
        // only poll KPay for KPay rows.
        $pendingTxs = PawaPayTransaction::where('status', 'pending')
            ->whereIn('provider', WalletService::KPAY_PROVIDERS)
            ->where('created_at', '>=', now()->subHours(24))
            ->get();

        if ($pendingTxs->isEmpty()) {
            return;
        }

        Log::info("KPay polling: {$pendingTxs->count()} pending transactions");

        foreach ($pendingTxs as $tx) {
            try {
                $this->pollTransaction($tx, $kpay);
            } catch (\Throwable $e) {
                Log::error("KPay poll error for {$tx->transaction_id}: {$e->getMessage()}");
            }
        }
    }

    private function pollTransaction(PawaPayTransaction $tx, KPayService $kpay): void
    {
        if ($tx->type === 'deposit') {
            $result = $kpay->checkDepositStatus($tx->transaction_id);
        } elseif ($tx->type === 'payout') {
            $result = $kpay->checkWithdrawalStatus($tx->transaction_id);
        } else {
            return;
        }

        $kpayStatus = $result['status'] ?? null;

        if (! $kpayStatus || $kpayStatus === 'PENDING' || $kpayStatus === 'PROCESSING') {
            return; // Still in progress
        }

        if ($kpayStatus === 'COMPLETED') {
            if ($tx->type === 'deposit') {
                if ($credited = WalletService::completeDeposit($tx, $kpayStatus)) {
                    self::notifyWalletCredited($credited->user, (float) $credited->amount, (string) $credited->currency);
                    Log::info("KPay deposit {$tx->transaction_id} COMPLETED (wallet credited)");
                }
            } elseif (WalletService::completePayout($tx, $kpayStatus)) {
                Log::info("KPay payout {$tx->transaction_id} COMPLETED");
            }
        } elseif (in_array($kpayStatus, ['FAILED', 'CANCELLED'])) {
            // Marks failed and (for payouts) refunds the driver — only once.
            if (WalletService::failTransaction($tx, $kpayStatus, $result['failureReason'] ?? null)) {
                Log::info("KPay {$tx->type} {$tx->transaction_id} {$kpayStatus}");
            }
        }
    }

    public static function notifyWalletCredited($user, float $amount, string $currency): void
    {
        if (! $user) {
            return;
        }

        try {
            $notification = DB::table('notification_channels')
                ->where('topics', 'User Wallet Amount')
                ->first();

            if ($notification && $notification->push_notification == 1) {
                $lang = $user->lang ?? 'en';
                $translation = DB::table('notification_channels_translations')
                    ->where('notification_channel_id', $notification->id)
                    ->where('locale', $lang)
                    ->first()
                    ?? DB::table('notification_channels_translations')
                        ->where('notification_channel_id', $notification->id)
                        ->where('locale', 'en')
                        ->first();

                $title = $translation->push_title ?? $notification->push_title;
                $body = strip_tags($translation->push_body ?? $notification->push_body);
                dispatch(new SendPushNotification($user, $title, $body));
            }
        } catch (\Throwable $e) {
            Log::error('KPay wallet notification error: '.$e->getMessage());
        }
    }
}
