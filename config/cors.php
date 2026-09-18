<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // Comma-separated list of browser origins allowed to call the API cross-origin, e.g.
    // CORS_ALLOWED_ORIGINS="https://mercie.iues-insambot.com,https://www.example.com".
    // Defaults to the origin of APP_URL. The admin panel / web booking portal call the API
    // from the same origin, so they are not affected. The mobile apps do not use CORS.
    // Set CORS_ALLOWED_ORIGINS="*" only for local development.
    'allowed_origins' => array_values(array_filter(array_map(function ($origin) {
        $origin = trim($origin);

        if ($origin === '*' || $origin === '') {
            return $origin;
        }

        $parts = parse_url($origin);

        if (empty($parts['scheme']) || empty($parts['host'])) {
            return null;
        }

        return $parts['scheme'].'://'.$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '');
    }, explode(',', (string) env('CORS_ALLOWED_ORIGINS', env('APP_URL', '')))))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
