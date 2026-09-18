<?php

namespace App\Base\Services\OTP;

use DateTimeInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;
use Throwable;

/**
 * Verifies a Firebase phone-auth ID token as proof that the caller controls a mobile number.
 *
 * The app performs Firebase phone verification (verifyPhoneNumber / signInWithCredential)
 * and sends the resulting Firebase ID token as `firebase_id_token`. The token is accepted
 * only if ALL of the following hold:
 *  - signature, expiry, issuer and audience are valid for our Firebase project (kreait);
 *  - it was issued by the "phone" sign-in provider and carries a phone_number claim;
 *  - phone_number equals the account's number (dial code + mobile, digits only);
 *  - the phone sign-in (auth_time) happened less than MAX_AUTH_AGE_MINUTES ago;
 *  - that sign-in has not been used before (single use, cached for REPLAY_TTL_MINUTES).
 *
 * The token itself is never logged.
 */
class FirebasePhoneVerifier
{
    /**
     * Verify the token against the expected number and consume it (single use).
     *
     * @param  string|null  $idToken  Firebase ID token sent by the app
     * @param  string|null  $mobile  the account's stored mobile (with or without dial code)
     * @param  string|null  $dialCode  the account's country dial code, e.g. "+237"
     */
    public function verify(?string $idToken, ?string $mobile, ?string $dialCode): bool
    {
        if (blank($idToken) || blank($mobile)) {
            return $this->fail('missing_token_or_mobile', $mobile);
        }

        try {
            $token = $this->auth()->verifyIdToken($idToken);
        } catch (Throwable $e) {
            return $this->fail('invalid_token', $mobile, [
                'exception' => get_class($e),
                'error' => $this->redact($e->getMessage()),
            ]);
        }

        $claims = $token->claims();

        // Defence in depth: kreait already checks aud/iss against the service-account project.
        $projectId = (string) config('auth.firebase_phone_auth.project_id');
        $audience = (array) $claims->get('aud', []);
        if ($projectId === '' || ! in_array($projectId, $audience, true)
            || $claims->get('iss') !== 'https://securetoken.google.com/'.$projectId) {
            return $this->fail('wrong_project', $mobile);
        }

        $firebase = $claims->get('firebase');
        $provider = is_array($firebase) ? ($firebase['sign_in_provider'] ?? null) : null;
        if ($provider !== 'phone') {
            return $this->fail('not_phone_provider', $mobile, ['provider' => $provider]);
        }

        $phoneNumber = $claims->get('phone_number');
        if (! is_string($phoneNumber) || $phoneNumber === '') {
            return $this->fail('missing_phone_number', $mobile);
        }

        if (! $this->phoneMatches($phoneNumber, $mobile, $dialCode)) {
            return $this->fail('phone_mismatch', $mobile, ['token_phone' => $this->mask($phoneNumber)]);
        }

        $authTime = $this->timestamp($claims->get('auth_time'));
        $maxAge = (int) config('auth.firebase_phone_auth.max_auth_age_minutes', 10) * 60;
        $now = now()->getTimestamp();
        // 60s tolerance for clock skew in the future direction only.
        if ($authTime === null || $authTime < $now - $maxAge || $authTime > $now + 60) {
            return $this->fail('stale_auth_time', $mobile);
        }

        // Single use: one phone sign-in (uid + auth_time) can be exchanged once. Refreshed ID
        // tokens keep the same auth_time, so they cannot be replayed either.
        $subject = (string) $claims->get('sub', '');
        $key = 'firebase_phone_auth:used:'.hash('sha256', $subject.'|'.$authTime);
        $ttl = (int) config('auth.firebase_phone_auth.replay_ttl_minutes', 15) * 60;

        if (! $this->replayStore()->add($key, 1, $ttl)) {
            return $this->fail('token_reused', $mobile);
        }

        return true;
    }

    /**
     * Compare an E.164 number from the token with the account's number (digits only).
     */
    public function phoneMatches(string $tokenPhone, string $mobile, ?string $dialCode): bool
    {
        $tokenDigits = $this->digits($tokenPhone);
        $mobileDigits = $this->digits($mobile);
        $dialDigits = $this->digits((string) $dialCode);

        if ($tokenDigits === '' || $mobileDigits === '') {
            return false;
        }

        $candidates = [];
        if ($dialDigits !== '') {
            $candidates[] = $dialDigits.$mobileDigits;
        }
        // Mobile already stored in international form (with or without "+"/"00").
        if ($dialDigits === '' || str_starts_with($mobileDigits, $dialDigits)) {
            $candidates[] = $mobileDigits;
        }
        if (str_starts_with($mobileDigits, '00')) {
            $candidates[] = substr($mobileDigits, 2);
        }

        foreach ($candidates as $candidate) {
            if (hash_equals($candidate, $tokenDigits)) {
                return true;
            }
        }

        return false;
    }

    protected function auth(): FirebaseAuth
    {
        return app(FirebaseAuth::class);
    }

    protected function replayStore()
    {
        return Cache::store(config('auth.firebase_phone_auth.replay_cache_store') ?: null);
    }

    protected function digits(string $value): string
    {
        return preg_replace('/\D/', '', $value) ?? '';
    }

    protected function timestamp($value): ?int
    {
        if ($value instanceof DateTimeInterface) {
            return $value->getTimestamp();
        }

        return is_numeric($value) ? (int) $value : null;
    }

    protected function mask(?string $phone): string
    {
        $digits = $this->digits((string) $phone);

        return $digits === '' ? '' : str_repeat('*', max(0, strlen($digits) - 4)).substr($digits, -4);
    }

    /**
     * Remove anything that looks like a JWT from an error message.
     */
    protected function redact(string $message): string
    {
        return (string) preg_replace('/eyJ[A-Za-z0-9_\-]*\.[A-Za-z0-9_\-]*\.?[A-Za-z0-9_\-]*/', '[token]', $message);
    }

    protected function fail(string $reason, ?string $mobile, array $context = []): bool
    {
        Log::warning('Firebase phone verification failed', array_merge([
            'reason' => $reason,
            'mobile' => $this->mask($mobile),
        ], $context));

        return false;
    }
}
