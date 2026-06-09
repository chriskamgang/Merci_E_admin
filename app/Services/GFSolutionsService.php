<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GFSolutionsService
{
    private string $baseUrl;
    private string $apiKey;
    private string $apiSecret;

    public function __construct()
    {
        $this->apiKey    = get_payment_settings('gfsolutions_api_key') ?: env('GFSOLUTIONS_API_KEY', '');
        $this->apiSecret = get_payment_settings('gfsolutions_api_secret') ?: env('GFSOLUTIONS_API_SECRET', '');
        $this->baseUrl   = get_payment_settings('gfsolutions_base_url') ?: env('GFSOLUTIONS_BASE_URL', 'https://backend.gfinancials.com/api/v1');
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Create a payment link.
     *
     * @return array{paymentUrl: string, paymentRef: string}|array
     */
    public function createPayment(
        float  $amount,
        string $orderId,
        string $description,
        string $callbackUrl,
        string $returnUrl
    ): array {
        $payload = [
            'amount'      => (int) $amount,
            'orderId'     => $orderId,
            'description' => $description,
            'callbackUrl' => $callbackUrl,
            'returnUrl'   => $returnUrl,
        ];

        $response = Http::withHeaders([
            'X-API-Key'    => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/gateway/payments", $payload);

        Log::info('GFSolutions payment created', [
            'payload'  => $payload,
            'status'   => $response->status(),
            'response' => $response->json(),
        ]);

        return $response->json() ?? [];
    }

    /**
     * Verify webhook signature.
     */
    public function verifySignature(string $signature, string $paymentRef, $amount, string $orderId): bool
    {
        $expected = 'sha256=' . hash_hmac('sha256', "{$paymentRef}:{$amount}:{$orderId}", $this->apiKey);
        return hash_equals($expected, $signature);
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }
}
