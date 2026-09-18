<?php

use App\Models\User;

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option controls the default authentication "guard" and password
    | reset options for your application. You may change these defaults
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | here which uses session storage and the Eloquent user provider.
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | Supported: "session"
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | If you have multiple user tables or models you may configure multiple
    | sources which represent each model / table. These sources may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => User::class,
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | You may specify multiple password reset configurations if you have more
    | than one user table or model in the application and you want to have
    | separate password reset settings based on the specific user types.
    |
    | The expiry time is the number of minutes that each reset token will be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
    |
    | The throttle setting is the number of seconds a user must wait before
    | generating more password reset tokens. This prevents the user from
    | quickly generating a very large amount of password reset tokens.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Here you may define the amount of seconds before a password confirmation
    | times out and the user is prompted to re-enter their password via the
    | confirmation screen. By default, the timeout lasts for three hours.
    |
    */

    'password_timeout' => 10800,

    /*
    |--------------------------------------------------------------------------
    | Legacy mobile OTP login (temporary)
    |--------------------------------------------------------------------------
    |
    | Mobile OTP login requires the `otp` field on /api/v1/{user,driver}/login.
    | App builds released before that change send only `mobile` after calling
    | /api/v1/validate-otp. Set this to true ONLY as a short migration window:
    | a login without `otp` is then accepted if an unexpired, already verified
    | code exists for that mobile (it is consumed on use).
    |
    */

    'legacy_mobile_login_without_otp' => (bool) env('AUTH_LEGACY_MOBILE_LOGIN_WITHOUT_OTP', false),

    /*
    |--------------------------------------------------------------------------
    | Firebase phone-auth OTP
    |--------------------------------------------------------------------------
    |
    | Mobile login and password reset accept `firebase_id_token` (a Firebase ID
    | token obtained by the app after Firebase phone verification) instead of a
    | server SMS `otp`. See App\Base\Services\OTP\FirebasePhoneVerifier.
    |
    | replay_cache_store must be a store shared by all PHP workers and that
    | survives between requests (file, redis, database...). Never "array".
    |
    */

    'firebase_phone_auth' => [
        'project_id' => env('FIREBASE_PHONE_AUTH_PROJECT_ID', 'mercie-app'),
        'max_auth_age_minutes' => 10,
        'replay_ttl_minutes' => 15,
        'replay_cache_store' => env('FIREBASE_PHONE_AUTH_REPLAY_STORE', 'file'),
    ],

];
