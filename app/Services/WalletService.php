<?php

namespace App\Services;

use App\Base\Constants\Masters\WalletRemarks;
use App\Models\Payment\DriverWallet;
use App\Models\Payment\DriverWalletHistory;
use App\Models\Payment\PawaPayTransaction;
use App\Models\Payment\UserWallet;
use App\Models\Payment\UserWalletHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Concurrency-safe wallet helpers.
 *
 * Every balance mutation goes through a row lock (SELECT ... FOR UPDATE) inside a
 * DB transaction, and every mobile-money transaction status transition
 * (pending -> completed / pending -> failed) is single-shot: the transaction row is
 * locked and re-checked, so the credit / refund happens exactly once even when
 * the app polling endpoint and the background polling job race each other.
 *
 * NOTE: GFSolutionsController still has its own crediting code; it can adopt
 * completeDeposit() / failTransaction() later.
 */
class WalletService
{
    /** Providers handled by KPay (rows in pawapay_transactions). */
    public const KPAY_PROVIDERS = ['MTN_MOMO_CMR', 'ORANGE_CMR'];

    private static ?bool $hasExternalIdColumn = null;

    /**
     * Lock a wallet row (driver_wallet / user_wallet / owner_wallets) for the given owner id,
     * creating it when missing. MUST be called inside DB::transaction().
     *
     * @param  class-string<Model>  $walletClass
     */
    public static function lockWallet(string $walletClass, int|string $ownerId, bool $create = true): ?Model
    {
        $wallet = $walletClass::where('user_id', $ownerId)->orderBy('created_at')->lockForUpdate()->first();

        if (! $wallet && $create) {
            $wallet = $walletClass::create([
                'user_id' => $ownerId,
                'amount_added' => 0,
                'amount_balance' => 0,
                'amount_spent' => 0,
            ]);
        }

        return $wallet;
    }

    /**
     * Lock a wallet row by its primary key. MUST be called inside DB::transaction().
     */
    public static function lockWalletByKey(Model $wallet): ?Model
    {
        return $wallet->newQuery()->whereKey($wallet->getKey())->lockForUpdate()->first();
    }

    /**
     * Whether pawapay_transactions has the external_id column (migration may not be run yet).
     */
    public static function hasExternalIdColumn(): bool
    {
        if (self::$hasExternalIdColumn === null) {
            try {
                self::$hasExternalIdColumn = Schema::hasColumn('pawapay_transactions', 'external_id');
            } catch (\Throwable $e) {
                self::$hasExternalIdColumn = false;
            }
        }

        return self::$hasExternalIdColumn;
    }

    /**
     * pending -> completed for a DEPOSIT, crediting the owner's wallet exactly once.
     *
     * @return PawaPayTransaction|null the transaction when THIS call credited the wallet, null otherwise
     *                                 (already completed/failed, not a deposit, no user)
     */
    public static function completeDeposit(PawaPayTransaction|int $tx, string $rawStatus = 'COMPLETED'): ?PawaPayTransaction
    {
        $id = $tx instanceof PawaPayTransaction ? $tx->getKey() : $tx;

        return DB::transaction(function () use ($id, $rawStatus) {
            $tx = PawaPayTransaction::whereKey($id)->lockForUpdate()->first();

            if (! $tx || $tx->type !== 'deposit' || $tx->status !== 'pending') {
                return null;
            }

            $user = $tx->user;
            if (! $user) {
                return null;
            }

            $amount = (float) $tx->amount;

            if ($user->hasRole('driver') && $user->driver) {
                $wallet = self::lockWallet(DriverWallet::class, $user->driver->id);
                $historyClass = DriverWalletHistory::class;
                $historyOwnerId = $user->driver->id;
            } else {
                $wallet = self::lockWallet(UserWallet::class, $user->id);
                $historyClass = UserWalletHistory::class;
                $historyOwnerId = $user->id;
            }

            $wallet->amount_added += $amount;
            $wallet->amount_balance += $amount;
            $wallet->save();

            $historyClass::create([
                'user_id' => $historyOwnerId,
                'amount' => $amount,
                'transaction_id' => $tx->transaction_id,
                'remarks' => WalletRemarks::MONEY_DEPOSITED_TO_E_WALLET,
                'is_credit' => true,
            ]);

            $tx->status = 'completed';
            $tx->pawapay_status = $rawStatus;
            $tx->save();

            return $tx;
        });
    }

    /**
     * pending -> completed for a PAYOUT (money already debited at initiation).
     *
     * @return bool true when this call performed the transition
     */
    public static function completePayout(PawaPayTransaction|int $tx, string $rawStatus = 'COMPLETED'): bool
    {
        $id = $tx instanceof PawaPayTransaction ? $tx->getKey() : $tx;

        $affected = PawaPayTransaction::whereKey($id)
            ->where('type', 'payout')
            ->where('status', 'pending')
            ->update(['status' => 'completed', 'pawapay_status' => $rawStatus, 'updated_at' => now()]);

        return $affected > 0;
    }

    /**
     * pending -> failed. For a PAYOUT the debited amount is refunded to the driver wallet,
     * exactly once (the row lock + status re-check makes a second call a no-op).
     *
     * @return bool true when this call performed the transition (and refund, for payouts)
     */
    public static function failTransaction(PawaPayTransaction|int $tx, ?string $rawStatus, mixed $failureReason = null): bool
    {
        $id = $tx instanceof PawaPayTransaction ? $tx->getKey() : $tx;

        return DB::transaction(function () use ($id, $rawStatus, $failureReason) {
            $tx = PawaPayTransaction::whereKey($id)->lockForUpdate()->first();

            if (! $tx || $tx->status !== 'pending') {
                return false;
            }

            $tx->status = 'failed';
            $tx->pawapay_status = $rawStatus;
            $tx->failure_reason = is_string($failureReason) ? $failureReason : json_encode($failureReason);
            $tx->save();

            if ($tx->type === 'payout' && $tx->driver_id) {
                $amount = (float) $tx->amount;
                $wallet = self::lockWallet(DriverWallet::class, $tx->driver_id);
                $wallet->amount_balance += $amount;
                $wallet->save();

                DriverWalletHistory::create([
                    'user_id' => $tx->driver_id,
                    'amount' => $amount,
                    'transaction_id' => $tx->transaction_id.'-refund',
                    'remarks' => WalletRemarks::MONEY_DEPOSITED_TO_E_WALLET,
                    'is_credit' => true,
                ]);
            }

            return true;
        });
    }
}
