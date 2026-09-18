<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KPayService
{
    private string $baseUrl = 'https://admin.kpay.site';
    private string $apiKey;
    private string $secretKey;

    public function __construct()
    {
        $env = get_payment_settings('kpay_environment') ?? 'test';

        if ($env === 'live') {
            $this->apiKey    = get_payment_settings('kpay_live_api_key') ?? env('KPAY_API_KEY', '');
            $this->secretKey = get_payment_settings('kpay_live_secret_key') ?? env('KPAY_SECRET_KEY', '');
        } else {
            $this->apiKey    = get_payment_settings('kpay_test_api_key') ?? env('KPAY_API_KEY', '');
            $this->secretKey = get_payment_settings('kpay_test_secret_key') ?? env('KPAY_SECRET_KEY', '');
        }
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && !empty($this->secretKey);
    }

    private function headers(): array
    {
        return [
            'X-API-Key'    => $this->apiKey,
            'X-Secret-Key' => $this->secretKey,
            'Content-Type'  => 'application/json',
        ];
    }

    // =========================================================================
    // DEPOSITS (recharge wallet)
    // =========================================================================

    /**
     * Initiate a deposit via USSD (MTN MoMo / Orange Money).
     */
    public function initiateDeposit(
        string $externalId,
        string $phone,
        string $provider,
        int    $amount,
        string $description = 'Paiement Merci E'
    ): array {
        $payload = [
            'amount'      => $amount,
            'provider'    => $provider,
            'phoneNumber' => $phone,
            'externalId'  => $externalId,
            'description' => $description,
        ];

        $response = Http::withHeaders($this->headers())
            ->post("{$this->baseUrl}/api/v1/payments/init", $payload);

        Log::info('KPay deposit initiated', [
            'payload'  => $payload,
            'status'   => $response->status(),
            'response' => $response->json(),
        ]);

        return $this->withHttpStatus($response);
    }

    /**
     * Check deposit status.
     */
    public function checkDepositStatus(string $paymentId): array
    {
        $response = Http::withHeaders($this->headers())
            ->get("{$this->baseUrl}/api/v1/payments/{$paymentId}");

        return $response->json() ?? [];
    }

    // =========================================================================
    // WITHDRAWALS (driver payout to mobile money)
    // =========================================================================

    /**
     * Initiate a withdrawal via USSD.
     */
    public function initiateWithdrawal(
        string $externalId,
        string $phone,
        string $provider,
        int    $amount,
        string $description = 'Retrait de fonds Merci E'
    ): array {
        $payload = [
            'amount'      => $amount,
            'provider'    => $provider,
            'phoneNumber' => $phone,
            'externalId'  => $externalId,
            'description' => $description,
        ];

        $response = Http::withHeaders($this->headers())
            ->post("{$this->baseUrl}/api/v1/payments/withdraw", $payload);

        Log::info('KPay withdrawal initiated', [
            'payload'  => $payload,
            'status'   => $response->status(),
            'response' => $response->json(),
        ]);

        return $this->withHttpStatus($response);
    }

    /**
     * Check withdrawal status.
     */
    public function checkWithdrawalStatus(string $withdrawalId): array
    {
        $response = Http::withHeaders($this->headers())
            ->get("{$this->baseUrl}/api/v1/payments/withdraw/{$withdrawalId}");

        return $response->json() ?? [];
    }

    /**
     * Decoded JSON body plus `_http_status` (lets callers distinguish a definite
     * 4xx rejection from an ambiguous 5xx / unparseable response).
     */
    private function withHttpStatus(\Illuminate\Http\Client\Response $response): array
    {
        $json = $response->json();
        $json = is_array($json) ? $json : [];
        $json['_http_status'] = $response->status();

        return $json;
    }
}
