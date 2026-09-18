<?php

namespace App\Http\Controllers\Api\V1\Payment;

use App\Base\Constants\Masters\WalletRemarks;
use App\Http\Controllers\Api\V1\BaseController;
use App\Jobs\KPayPollPendingTransactions;
use App\Models\Payment\DriverWallet;
use App\Models\Payment\DriverWalletHistory;
use App\Models\Payment\PawaPayTransaction;
use App\Services\KPayService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * @group KPay
 *
 * @authenticated
 * Mobile Money deposit & withdrawal via KPay
 */
class KPayController extends BaseController
{
    public function __construct(private KPayService $kpay) {}

    /**
     * Validation rules shared by deposit & withdrawal: numeric, >= 100, whole FCFA.
     */
    private function amountRules(): array
    {
        return [
            'required',
            'numeric',
            'min:100',
            function ($attribute, $value, $fail) {
                if (! is_numeric($value) || floor((float) $value) != (float) $value) {
                    $fail('The amount must be a whole number of FCFA.');
                }
            },
        ];
    }

    /**
     * Short external id that fits the char(36) transaction_id column: PREFIX- + 32 hex chars.
     */
    private function newExternalId(string $prefix): string
    {
        return $prefix.'-'.str_replace('-', '', (string) Str::uuid());
    }

    /**
     * Try to replace the provisional transaction_id with KPay's own id (used by the app and the
     * status checks). Never throws: if it fails (e.g. id too long for the column) we keep ours.
     */
    private function attachKPayId(PawaPayTransaction $tx, ?string $kpayId, string $kpayStatus): void
    {
        try {
            $tx->pawapay_status = substr($kpayStatus, 0, 30);
            if ($kpayId && $kpayId !== $tx->transaction_id) {
                $tx->transaction_id = $kpayId;
            }
            $tx->save();
        } catch (\Throwable $e) {
            Log::error("KPay: could not attach KPay id {$kpayId} to {$tx->external_id}: {$e->getMessage()}");
            $tx->refresh();
        }
    }

    /**
     * A definite rejection: explicit FAILED/CANCELLED, or a 4xx (other than timeout) with no id.
     * Anything else (5xx, network error, unknown body) is ambiguous and must stay pending.
     */
    private function isDefinitelyRejected(array $result): bool
    {
        $status = $result['status'] ?? null;
        if (in_array($status, ['FAILED', 'CANCELLED'], true)) {
            return true;
        }

        $http = (int) ($result['_http_status'] ?? 0);

        return empty($result['id']) && $http >= 400 && $http < 500 && ! in_array($http, [408, 409], true);
    }

    // =========================================================================
    // DEPOSIT (recharge wallet)
    // =========================================================================

    /**
     * Initiate a deposit (add money to wallet via mobile money).
     */
    public function initiateDeposit(Request $request)
    {
        $request->validate([
            'amount' => $this->amountRules(),
            'phone' => 'required|string|max:30',
            'provider' => 'required|string|in:MTN_MOMO_CMR,ORANGE_CMR',
        ]);

        $user = auth()->user();

        if (! $this->kpay->isConfigured()) {
            return $this->respondFailed('kpay_not_configured');
        }

        $externalId = $this->newExternalId('DEP');
        $amount = (int) $request->amount;
        $currency = $user->countryDetail?->currency_code ?? 'XAF';

        // Record the pending deposit BEFORE calling KPay so a customer can never be charged
        // without us having a row to credit against.
        $row = [
            'transaction_id' => $externalId,
            'type' => 'deposit',
            'user_id' => $user->id,
            'amount' => $amount,
            'currency' => $currency,
            'phone' => $request->phone,
            'provider' => $request->provider,
            'status' => 'pending',
            'pawapay_status' => 'INITIATING',
        ];
        if (WalletService::hasExternalIdColumn()) {
            $row['external_id'] = $externalId;
        }
        $tx = PawaPayTransaction::create($row);

        try {
            $result = $this->kpay->initiateDeposit($externalId, $request->phone, $request->provider, $amount, 'Paiement Merci E');
        } catch (\Throwable $e) {
            // Outcome unknown: keep the row pending, the polling job will resolve it.
            Log::error("KPay deposit init error {$externalId}: {$e->getMessage()}");

            return response()->json([
                'success' => false,
                'message' => 'deposit_status_unknown',
            ], 502);
        }

        $kpayStatus = $result['status'] ?? 'UNKNOWN';
        $kpayId = isset($result['id']) ? (string) $result['id'] : null;

        if ($this->isDefinitelyRejected($result)) {
            WalletService::failTransaction($tx, $kpayStatus, $result['failureReason'] ?? $result['message'] ?? null);

            return response()->json([
                'success' => false,
                'message' => 'deposit_rejected',
                'reason' => $result['failureReason'] ?? $result['message'] ?? null,
            ], 422);
        }

        $this->attachKPayId($tx, $kpayId, $kpayStatus);

        // Dispatch background polling job (delayed 10s, then scheduler picks up too)
        KPayPollPendingTransactions::dispatch()->delay(now()->addSeconds(10));

        return response()->json([
            'success' => true,
            'message' => 'deposit_initiated',
            'deposit_id' => $tx->transaction_id,
            'status' => $kpayStatus,
        ]);
    }

    /**
     * Check deposit status (polling from the app).
     */
    public function depositStatus(string $depositId)
    {
        $tx = PawaPayTransaction::where('transaction_id', $depositId)
            ->where('user_id', auth()->id())
            ->where('type', 'deposit')
            ->whereIn('provider', WalletService::KPAY_PROVIDERS)
            ->firstOrFail();

        if (in_array($tx->status, ['completed', 'failed'])) {
            return response()->json([
                'success' => true,
                'status' => $tx->status,
            ]);
        }

        // Also poll KPay directly for immediate feedback
        $result = $this->kpay->checkDepositStatus($depositId);
        $kpayStatus = $result['status'] ?? null;

        if ($kpayStatus === 'COMPLETED') {
            // Atomic + single-shot: a concurrent job run cannot credit twice.
            if ($credited = WalletService::completeDeposit($tx, $kpayStatus)) {
                KPayPollPendingTransactions::notifyWalletCredited($credited->user, (float) $credited->amount, (string) $credited->currency);
            }
        } elseif (in_array($kpayStatus, ['FAILED', 'CANCELLED'])) {
            WalletService::failTransaction($tx, $kpayStatus, $result['failureReason'] ?? null);
        }

        return response()->json([
            'success' => true,
            'status' => $tx->fresh()->status,
        ]);
    }

    // =========================================================================
    // WITHDRAWAL (driver payout to mobile money)
    // =========================================================================

    /**
     * Initiate a withdrawal (driver withdraws earnings to mobile money).
     */
    public function initiateWithdrawal(Request $request)
    {
        $request->validate([
            'amount' => $this->amountRules(),
            'phone' => 'required|string|max:30',
            'provider' => 'required|string|in:MTN_MOMO_CMR,ORANGE_CMR',
        ]);

        $user = auth()->user();

        if (! $user->hasRole('driver') || ! $user->driver) {
            return $this->respondFailed('only_drivers_can_withdraw');
        }

        if (! $this->kpay->isConfigured()) {
            return $this->respondFailed('kpay_not_configured');
        }

        $driver = $user->driver;
        $currency = $user->countryDetail?->currency_code ?? 'XAF';
        $amount = (int) $request->amount;
        $externalId = $this->newExternalId('WDR');

        // Lock the wallet, re-check balance + pending payout, debit, and record the pending
        // payout row — all atomically and BEFORE any money leaves via KPay. Concurrent requests
        // serialize on the wallet row lock, so only one can pass.
        $tx = DB::transaction(function () use ($driver, $user, $amount, $currency, $externalId, $request) {
            $wallet = WalletService::lockWallet(DriverWallet::class, $driver->id, false);

            if (! $wallet || $wallet->amount_balance < $amount) {
                return 'insufficient_wallet_balance';
            }

            $hasPending = PawaPayTransaction::where('driver_id', $driver->id)
                ->where('type', 'payout')
                ->where('status', 'pending')
                ->exists();

            if ($hasPending) {
                return 'payout_already_pending';
            }

            $wallet->amount_balance -= $amount;
            $wallet->save();

            DriverWalletHistory::create([
                'user_id' => $driver->id,
                'amount' => $amount,
                'transaction_id' => $externalId,
                'remarks' => WalletRemarks::WITHDRAWN_FROM_WALLET,
                'is_credit' => false,
            ]);

            $row = [
                'transaction_id' => $externalId,
                'type' => 'payout',
                'user_id' => $user->id,
                'driver_id' => $driver->id,
                'amount' => $amount,
                'currency' => $currency,
                'phone' => $request->phone,
                'provider' => $request->provider,
                'status' => 'pending',
                'pawapay_status' => 'INITIATING',
            ];
            if (WalletService::hasExternalIdColumn()) {
                $row['external_id'] = $externalId;
            }

            return PawaPayTransaction::create($row);
        });

        if (is_string($tx)) {
            return $this->respondFailed($tx);
        }

        try {
            $result = $this->kpay->initiateWithdrawal($externalId, $request->phone, $request->provider, $amount, 'Retrait de fonds Merci E');
        } catch (\Throwable $e) {
            // Outcome unknown (timeout / network): the payout MAY have been created. Keep the
            // money debited and the row pending; the polling job (or an admin) resolves it.
            Log::error("KPay withdrawal init error {$externalId}: {$e->getMessage()}");

            return response()->json([
                'success' => false,
                'message' => 'withdrawal_status_unknown',
                'withdrawal_id' => $tx->transaction_id,
            ], 502);
        }

        $kpayStatus = $result['status'] ?? 'UNKNOWN';
        $kpayId = isset($result['id']) ? (string) $result['id'] : null;

        if ($this->isDefinitelyRejected($result)) {
            // Refund exactly once.
            WalletService::failTransaction($tx, $kpayStatus, $result['failureReason'] ?? $result['message'] ?? null);

            return response()->json([
                'success' => false,
                'message' => 'withdrawal_rejected',
                'reason' => $result['failureReason'] ?? $result['message'] ?? null,
            ], 422);
        }

        $this->attachKPayId($tx, $kpayId, $kpayStatus);

        // Dispatch background polling job
        KPayPollPendingTransactions::dispatch()->delay(now()->addSeconds(10));

        return response()->json([
            'success' => true,
            'message' => 'withdrawal_initiated',
            'withdrawal_id' => $tx->transaction_id,
            'status' => $kpayStatus,
        ]);
    }

    /**
     * Check withdrawal status (polling from the app).
     */
    public function withdrawalStatus(string $withdrawalId)
    {
        $tx = PawaPayTransaction::where('transaction_id', $withdrawalId)
            ->where('user_id', auth()->id())
            ->where('type', 'payout')
            ->firstOrFail();

        if (in_array($tx->status, ['completed', 'failed'])) {
            return response()->json([
                'success' => true,
                'status' => $tx->status,
            ]);
        }

        $result = $this->kpay->checkWithdrawalStatus($withdrawalId);
        $kpayStatus = $result['status'] ?? null;

        if ($kpayStatus === 'COMPLETED') {
            WalletService::completePayout($tx, $kpayStatus);
        } elseif (in_array($kpayStatus, ['FAILED', 'CANCELLED'])) {
            // Single-shot: refunds only if still pending (job may have refunded already).
            WalletService::failTransaction($tx, $kpayStatus, $result['failureReason'] ?? null);
        }

        return response()->json([
            'success' => true,
            'status' => $tx->fresh()->status,
        ]);
    }
}
