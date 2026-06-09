<?php

namespace App\Http\Controllers\Api\V1\Payment;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\V1\BaseController;
use App\Services\GFSolutionsService;
use App\Models\Payment\PawaPayTransaction;
use App\Models\Payment\UserWallet;
use App\Models\Payment\DriverWallet;
use App\Models\Payment\UserWalletHistory;
use App\Models\Payment\DriverWalletHistory;
use App\Base\Constants\Masters\WalletRemarks;
use App\Jobs\Notifications\SendPushNotification;

/**
 * @group GFSolutions
 * @authenticated
 * Wallet recharge via GFSolutions payment gateway
 */
class GFSolutionsController extends BaseController
{
    public function __construct(private GFSolutionsService $gfs)
    {
    }

    /**
     * Initiate a GFSolutions deposit (wallet recharge).
     *
     * Returns a paymentUrl that the mobile app should open in a WebView.
     *
     * @bodyParam amount numeric required Amount to deposit (e.g. 5000)
     */
    public function initiateDeposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100',
        ]);

        if (!$this->gfs->isConfigured()) {
            return $this->respondFailed('gfsolutions_not_configured');
        }

        $user     = auth()->user();
        $orderId  = 'GFS-' . Str::uuid();
        $amount   = (int) $request->amount;
        $currency = $user->countryDetail?->currency_code ?? 'XAF';

        $appUrl      = config('app.url');
        $callbackUrl = rtrim($appUrl, '/') . '/api/v1/payment/gfsolutions/callback';
        $returnUrl   = rtrim($appUrl, '/') . '/api/v1/payment/gfsolutions/return?order_id=' . $orderId;

        $result = $this->gfs->createPayment(
            $amount,
            $orderId,
            'Merci E recharge portefeuille',
            $callbackUrl,
            $returnUrl
        );

        $paymentUrl = $result['paymentUrl'] ?? null;

        if (!$paymentUrl) {
            Log::error('GFSolutions: no paymentUrl returned', ['result' => $result]);
            return $this->respondFailed('payment_creation_failed');
        }

        // Store pending transaction (reuse pawapay_transactions table)
        PawaPayTransaction::create([
            'transaction_id' => $orderId,
            'type'           => 'deposit',
            'user_id'        => $user->id,
            'amount'         => $amount,
            'currency'       => $currency,
            'phone'          => '',
            'provider'       => 'GFSOLUTIONS',
            'status'         => 'pending',
            'pawapay_status' => $result['paymentRef'] ?? 'INITIATED',
        ]);

        return response()->json([
            'success'     => true,
            'message'     => 'payment_created',
            'order_id'    => $orderId,
            'payment_url' => $paymentUrl,
        ]);
    }

    /**
     * Check deposit status (polling from the app).
     *
     * @urlParam order_id string required The orderId returned at initiation.
     */
    public function depositStatus(string $orderId)
    {
        $tx = PawaPayTransaction::where('transaction_id', $orderId)
            ->where('user_id', auth()->id())
            ->where('type', 'deposit')
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'status'  => $tx->status,
        ]);
    }

    /**
     * GFSolutions webhook callback (POST, called by GFSolutions servers).
     * Public route — no auth middleware.
     */
    public function callback(Request $request)
    {
        Log::info('GFSolutions callback received', $request->all());

        $signature  = $request->header('x-gfs-signature', '');
        $paymentRef = $request->input('paymentRef', '');
        $amount     = $request->input('amount', 0);
        $orderId    = $request->input('orderId', '');
        $status     = $request->input('status', '');

        // Verify signature
        if ($signature && !$this->gfs->verifySignature($signature, $paymentRef, $amount, $orderId)) {
            Log::warning('GFSolutions: invalid signature', [
                'expected_order' => $orderId,
                'signature'      => $signature,
            ]);
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $tx = PawaPayTransaction::where('transaction_id', $orderId)
            ->where('type', 'deposit')
            ->where('status', 'pending')
            ->first();

        if (!$tx) {
            return response()->json(['received' => true]);
        }

        if ($status === 'COMPLETED') {
            $this->creditWallet($tx);
        } else {
            $tx->update([
                'status'         => 'failed',
                'pawapay_status' => $status,
                'failure_reason' => json_encode($request->all()),
            ]);
        }

        return response()->json(['received' => true]);
    }

    /**
     * Return URL — user is redirected here after payment on GFSolutions page.
     * Shows a simple HTML page that the app's WebView can detect.
     */
    public function returnPage(Request $request)
    {
        $orderId = $request->input('order_id', '');
        $tx = PawaPayTransaction::where('transaction_id', $orderId)->first();
        $status = $tx?->status ?? 'unknown';

        // Return a simple page the WebView can parse
        return response()->view('gfsolutions_return', [
            'status'  => $status,
            'orderId' => $orderId,
        ]);
    }

    // =========================================================================
    // Private helpers
    // =========================================================================

    private function creditWallet(PawaPayTransaction $tx): void
    {
        if ($tx->status === 'completed') {
            return;
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

        $this->notifyWalletCredited($user, $tx->amount, $tx->currency);
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
            Log::error('GFSolutions wallet notification error: ' . $e->getMessage());
        }
    }
}
