<?php

namespace App\Http\Controllers\Api\V1\Payment;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\V1\BaseController;
use App\Services\GFSolutionsService;
use App\Services\WalletService;
use App\Models\Payment\PawaPayTransaction;
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

        $signature  = (string) $request->header('x-gfs-signature', '');
        $paymentRef = (string) $request->input('paymentRef', '');
        $amount     = $request->input('amount', 0);
        $orderId    = (string) $request->input('orderId', '');
        $status     = (string) $request->input('status', '');

        // Signature is mandatory: a missing header is treated exactly like a bad one.
        if ($signature === '' || ! $this->gfs->verifySignature($signature, $paymentRef, $amount, $orderId)) {
            Log::warning('GFSolutions: missing or invalid signature', [
                'order_id'      => $orderId,
                'has_signature' => $signature !== '',
            ]);

            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $tx = PawaPayTransaction::where('transaction_id', $orderId)
            ->where('type', 'deposit')
            ->where('provider', 'GFSOLUTIONS')
            ->first();

        // Unknown order, or already settled (retry / duplicate callback): acknowledge, do nothing.
        if (! $tx || $tx->status !== 'pending') {
            return response()->json(['received' => true]);
        }

        // WalletService performs the pending -> completed/failed transition under a row lock
        // and re-checks status=pending, so retried or concurrent callbacks credit at most once.
        if ($status !== 'COMPLETED') {
            WalletService::failTransaction($tx, $status, $request->all());

            return response()->json(['received' => true]);
        }

        // The stored amount is the one we asked GFSolutions to collect; never credit anything else.
        if (! $this->amountMatches($tx->amount, $amount)) {
            Log::warning('GFSolutions: callback amount does not match stored transaction', [
                'order_id' => $orderId,
                'expected' => $tx->amount,
                'received' => $amount,
            ]);

            WalletService::failTransaction($tx, 'AMOUNT_MISMATCH', [
                'reason'   => 'amount_mismatch',
                'expected' => $tx->amount,
                'callback' => $request->all(),
            ]);

            return response()->json(['received' => true]);
        }

        $credited = WalletService::completeDeposit($tx, 'COMPLETED');

        if ($credited) {
            // Outside the DB transaction: a push failure must never roll back the credit.
            $this->notifyWalletCredited($credited->user, (float) $credited->amount, (string) $credited->currency);
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

    /**
     * Compare the stored (requested) amount with the amount reported by the callback.
     * Amounts are whole FCFA; compared in cents to avoid float noise ("5000" vs "5000.00").
     */
    private function amountMatches($expected, $received): bool
    {
        if (! is_numeric($received)) {
            return false;
        }

        return (int) round(((float) $expected) * 100) === (int) round(((float) $received) * 100);
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
