<?php

namespace App\Http\Controllers\Api\V1\Payment;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Api\V1\BaseController;
use App\Services\PawaPayService;
use App\Models\Payment\PawaPayTransaction;
use App\Models\Payment\UserWallet;
use App\Models\Payment\DriverWallet;
use App\Models\Payment\UserWalletHistory;
use App\Models\Payment\DriverWalletHistory;
use App\Base\Constants\Masters\WalletRemarks;
use App\Base\Constants\Auth\Role;
use App\Jobs\Notifications\SendPushNotification;
use Illuminate\Support\Facades\DB;

/**
 * @group PawaPay
 * @authenticated
 * Mobile Money deposit & payout via PawaPay
 */
class PawaPayController extends BaseController
{
    public function __construct(private PawaPayService $pawaPay)
    {
    }

    // =========================================================================
    // DEPOSIT  (recharge wallet)
    // =========================================================================

    /**
     * Initiate a deposit (add money to wallet via mobile money).
     *
     * @bodyParam amount        numeric  required  Amount to deposit (e.g. 5000)
     * @bodyParam phone         string   required  Mobile money phone e.g. 237612345678
     * @bodyParam provider      string   required  MTN_MOMO_CMR or ORANGE_CMR
     */
    public function initiateDeposit(Request $request)
    {
        $request->validate([
            'amount'   => 'required|numeric|min:100',
            'phone'    => 'required|string',
            'provider' => 'required|string|in:MTN_MOMO_CMR,ORANGE_CMR',
        ]);

        $user       = auth()->user();
        $currency   = $user->countryDetail?->currency_code ?? 'XAF';
        $depositId  = (string) Str::uuid();
        $amount     = (string) intval($request->amount);

        if (empty($this->pawaPay->getToken())) {
            return $this->respondFailed('pawapay_not_configured');
        }

        // Call PawaPay
        $result = $this->pawaPay->initiateDeposit(
            $depositId,
            $request->phone,
            $request->provider,
            $amount,
            $currency,
            'Merci E recharge'
        );

        $pawapayStatus = $result['status'] ?? 'UNKNOWN';

        if ($pawapayStatus === 'REJECTED') {
            return response()->json([
                'success' => false,
                'message' => 'deposit_rejected',
                'reason'  => $result['rejectionReason'] ?? null,
            ], 422);
        }

        // Store pending transaction
        PawaPayTransaction::create([
            'transaction_id' => $depositId,
            'type'           => 'deposit',
            'user_id'        => $user->id,
            'amount'         => $request->amount,
            'currency'       => $currency,
            'phone'          => $request->phone,
            'provider'       => $request->provider,
            'status'         => 'pending',
            'pawapay_status' => $pawapayStatus,
        ]);

        return response()->json([
            'success'    => true,
            'message'    => 'deposit_initiated',
            'deposit_id' => $depositId,
            'status'     => $pawapayStatus, // ACCEPTED or DUPLICATE_IGNORED
        ]);
    }

    /**
     * Check deposit status (polling from the app).
     *
     * @urlParam deposit_id string required The depositId returned at initiation.
     */
    public function depositStatus(string $depositId)
    {
        $tx = PawaPayTransaction::where('transaction_id', $depositId)
            ->where('user_id', auth()->id())
            ->where('type', 'deposit')
            ->firstOrFail();

        // If already finalised, return from DB
        if (in_array($tx->status, ['completed', 'failed'])) {
            return response()->json([
                'success' => true,
                'status'  => $tx->status,
            ]);
        }

        // Poll PawaPay for latest status
        $result        = $this->pawaPay->checkDepositStatus($depositId);
        $pawapayStatus = $result['status'] ?? null;

        if ($pawapayStatus === 'COMPLETED') {
            $this->creditWallet($tx);
        } elseif (in_array($pawapayStatus, ['FAILED', 'TIMED_OUT', 'REJECTED'])) {
            $tx->update([
                'status'         => 'failed',
                'pawapay_status' => $pawapayStatus,
                'failure_reason' => json_encode($result['failureReason'] ?? null),
            ]);
        }

        return response()->json([
            'success' => true,
            'status'  => $tx->fresh()->status,
        ]);
    }

    /**
     * PawaPay callback for deposits (called by PawaPay server, not the app).
     * Route must be public (no auth middleware).
     */
    public function depositCallback(Request $request)
    {
        Log::info('PawaPay deposit callback', $request->all());

        $depositId     = $request->input('depositId');
        $pawapayStatus = $request->input('status');

        $tx = PawaPayTransaction::where('transaction_id', $depositId)
            ->where('type', 'deposit')
            ->where('status', 'pending')
            ->first();

        if (!$tx) {
            return response()->json(['success' => true]); // idempotent
        }

        if ($pawapayStatus === 'COMPLETED') {
            $this->creditWallet($tx);
        } else {
            $tx->update([
                'status'         => 'failed',
                'pawapay_status' => $pawapayStatus,
                'failure_reason' => json_encode($request->input('failureReason')),
            ]);
        }

        return response()->json(['success' => true]);
    }

    // =========================================================================
    // PAYOUT  (driver withdrawal to mobile money)
    // =========================================================================

    /**
     * Initiate a payout (driver withdraws earnings to mobile money).
     *
     * @bodyParam amount        numeric  required  Amount to withdraw
     * @bodyParam phone         string   required  Mobile money phone e.g. 237612345678
     * @bodyParam provider      string   required  MTN_MOMO_CMR or ORANGE_CMR
     */
    public function initiatePayout(Request $request)
    {
        $request->validate([
            'amount'   => 'required|numeric|min:100',
            'phone'    => 'required|string',
            'provider' => 'required|string|in:MTN_MOMO_CMR,ORANGE_CMR',
        ]);

        $user = auth()->user();

        if (!$user->hasRole('driver')) {
            return $this->respondFailed('only_drivers_can_withdraw');
        }

        $driver       = $user->driver;
        $currency     = $user->countryDetail?->currency_code ?? 'XAF';
        $amount       = floatval($request->amount);
        $driverWallet = $driver->driverWallet;

        // Balance check
        if (!$driverWallet || $driverWallet->amount_balance < $amount) {
            return $this->respondFailed('insufficient_wallet_balance');
        }

        // Prevent multiple pending payouts
        $hasPending = PawaPayTransaction::where('driver_id', $driver->id)
            ->where('type', 'payout')
            ->where('status', 'pending')
            ->exists();

        if ($hasPending) {
            return $this->respondFailed('payout_already_pending');
        }

        if (empty($this->pawaPay->getToken())) {
            return $this->respondFailed('pawapay_not_configured');
        }

        $payoutId = (string) Str::uuid();

        // Deduct from wallet immediately (refunded if payout fails)
        $driverWallet->amount_balance -= $amount;
        $driverWallet->save();

        DriverWalletHistory::create([
            'user_id'        => $driver->id,
            'amount'         => $amount,
            'transaction_id' => $payoutId,
            'remarks'        => WalletRemarks::WITHDRAWN_FROM_WALLET,
            'is_credit'      => false,
        ]);

        // Call PawaPay
        $result        = $this->pawaPay->initiatePayout(
            $payoutId,
            $request->phone,
            $request->provider,
            (string) intval($amount),
            $currency,
            'Merci E retrait'
        );

        $pawapayStatus = $result['status'] ?? 'UNKNOWN';

        if ($pawapayStatus === 'REJECTED') {
            // Refund immediately
            $this->refundDriverWallet($driver, $amount, $payoutId);
            return response()->json([
                'success' => false,
                'message' => 'payout_rejected',
                'reason'  => $result['rejectionReason'] ?? null,
            ], 422);
        }

        // Store transaction
        PawaPayTransaction::create([
            'transaction_id' => $payoutId,
            'type'           => 'payout',
            'user_id'        => $user->id,
            'driver_id'      => $driver->id,
            'amount'         => $amount,
            'currency'       => $currency,
            'phone'          => $request->phone,
            'provider'       => $request->provider,
            'status'         => 'pending',
            'pawapay_status' => $pawapayStatus,
        ]);

        return response()->json([
            'success'   => true,
            'message'   => 'payout_initiated',
            'payout_id' => $payoutId,
            'status'    => $pawapayStatus,
        ]);
    }

    /**
     * Check payout status (polling from the app).
     *
     * @urlParam payout_id string required The payoutId returned at initiation.
     */
    public function payoutStatus(string $payoutId)
    {
        $tx = PawaPayTransaction::where('transaction_id', $payoutId)
            ->where('user_id', auth()->id())
            ->where('type', 'payout')
            ->firstOrFail();

        if (in_array($tx->status, ['completed', 'failed'])) {
            return response()->json([
                'success' => true,
                'status'  => $tx->status,
            ]);
        }

        $result        = $this->pawaPay->checkPayoutStatus($payoutId);
        $pawapayStatus = $result['status'] ?? null;

        if ($pawapayStatus === 'COMPLETED') {
            $tx->update([
                'status'         => 'completed',
                'pawapay_status' => $pawapayStatus,
            ]);
        } elseif (in_array($pawapayStatus, ['FAILED', 'TIMED_OUT', 'REJECTED'])) {
            $tx->update([
                'status'         => 'failed',
                'pawapay_status' => $pawapayStatus,
                'failure_reason' => json_encode($result['failureReason'] ?? null),
            ]);
            // Refund driver wallet
            $this->refundDriverWallet($tx->driver, $tx->amount, $tx->transaction_id);
        }

        return response()->json([
            'success' => true,
            'status'  => $tx->fresh()->status,
        ]);
    }

    /**
     * PawaPay callback for payouts (called by PawaPay server, not the app).
     * Route must be public (no auth middleware).
     */
    public function payoutCallback(Request $request)
    {
        Log::info('PawaPay payout callback', $request->all());

        $payoutId      = $request->input('payoutId');
        $pawapayStatus = $request->input('status');

        $tx = PawaPayTransaction::where('transaction_id', $payoutId)
            ->where('type', 'payout')
            ->where('status', 'pending')
            ->first();

        if (!$tx) {
            return response()->json(['success' => true]);
        }

        if ($pawapayStatus === 'COMPLETED') {
            $tx->update([
                'status'         => 'completed',
                'pawapay_status' => $pawapayStatus,
            ]);
        } else {
            $tx->update([
                'status'         => 'failed',
                'pawapay_status' => $pawapayStatus,
                'failure_reason' => json_encode($request->input('failureReason')),
            ]);
            // Refund driver
            if ($tx->driver) {
                $this->refundDriverWallet($tx->driver, $tx->amount, $tx->transaction_id);
            }
        }

        return response()->json(['success' => true]);
    }

    // =========================================================================
    // Private helpers
    // =========================================================================

    private function creditWallet(PawaPayTransaction $tx): void
    {
        if ($tx->status === 'completed') {
            return; // already credited (idempotent)
        }

        $user = $tx->user;

        if ($user->hasRole('driver')) {
            $wallet = DriverWallet::firstOrCreate(['user_id' => $user->driver->id]);
            $wallet->increment('amount_added', $tx->amount);
            $wallet->increment('amount_balance', $tx->amount);

            DriverWalletHistory::create([
                'user_id'        => $user->driver->id,
                'amount'         => $tx->amount,
                'transaction_id' => $tx->transaction_id,
                'remarks'        => WalletRemarks::MONEY_DEPOSITED_TO_E_WALLET,
                'is_credit'      => true,
            ]);
        } else {
            $wallet = UserWallet::firstOrCreate(['user_id' => $user->id]);
            $wallet->increment('amount_added', $tx->amount);
            $wallet->increment('amount_balance', $tx->amount);

            UserWalletHistory::create([
                'user_id'        => $user->id,
                'amount'         => $tx->amount,
                'transaction_id' => $tx->transaction_id,
                'remarks'        => WalletRemarks::MONEY_DEPOSITED_TO_E_WALLET,
                'is_credit'      => true,
            ]);
        }

        $tx->update([
            'status'         => 'completed',
            'pawapay_status' => 'COMPLETED',
        ]);

        // Push notification
        $this->notifyWalletCredited($user, $tx->amount, $tx->currency);
    }

    private function refundDriverWallet($driver, float $amount, string $transactionId): void
    {
        if (!$driver) return;

        $wallet = $driver->driverWallet;
        if ($wallet) {
            $wallet->increment('amount_balance', $amount);

            DriverWalletHistory::create([
                'user_id'        => $driver->id,
                'amount'         => $amount,
                'transaction_id' => $transactionId . '-refund',
                'remarks'        => WalletRemarks::MONEY_DEPOSITED_TO_E_WALLET,
                'is_credit'      => true,
            ]);
        }
    }

    private function notifyWalletCredited($user, float $amount, string $currency): void
    {
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
                $body  = strip_tags($translation->push_body ?? $notification->push_body);
                dispatch(new SendPushNotification($user, $title, $body));
            }
        } catch (\Throwable $e) {
            Log::error('PawaPay wallet notification error: ' . $e->getMessage());
        }
    }
}
