<?php

/*
|--------------------------------------------------------------------------
| Integration partners (B2B delivery accounts)
|--------------------------------------------------------------------------
|
| A partner is a regular Merci E *user* account (role "user") used by an
| external system (e.g. the EstuaireAchats marketplace) to create delivery
| requests through the normal API with a Sanctum token.
|
| For partner accounts:
|   - creating a request does NOT auto-cancel the account's previous request
|     that is still waiting for a driver (several concurrent deliveries);
|   - every state change of their requests is POSTed, signed, to the
|     partner's webhook URL (see App\Jobs\SendPartnerWebhook).
|
| .env:
|   MERCI_E_PARTNER_USER_IDS=123            (comma-separated users.id)
|   MERCI_E_PARTNER_123_WEBHOOK_URL=https://api.example.com/api/v1/delivery/webhook/merci-e
|   MERCI_E_PARTNER_123_WEBHOOK_SECRET=<long random string, shared with the partner>
|
| Run `php artisan config:clear` (or config:cache) after changing these.
*/

$ids = array_values(array_filter(array_map(
    'trim',
    explode(',', (string) env('MERCI_E_PARTNER_USER_IDS', ''))
), fn ($id) => $id !== '' && ctype_digit($id)));

$partners = [];
foreach ($ids as $id) {
    $partners[(int) $id] = [
        'webhook_url' => env("MERCI_E_PARTNER_{$id}_WEBHOOK_URL"),
        'webhook_secret' => env("MERCI_E_PARTNER_{$id}_WEBHOOK_SECRET"),
    ];
}

return [
    'partners' => $partners,

    // Outbound webhook HTTP timeout (seconds). Must stay well below queue retry_after (90s).
    'webhook_timeout' => (int) env('MERCI_E_PARTNER_WEBHOOK_TIMEOUT', 15),
];
