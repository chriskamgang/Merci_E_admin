<?php

namespace App\Services\Partners;

use App\Models\Request\Request;

/**
 * Integration partner accounts (see config/partners.php).
 */
class PartnerRegistry
{
    public static function isPartner($userId): bool
    {
        if ($userId === null || $userId === '') {
            return false;
        }

        return array_key_exists((int) $userId, config('partners.partners', []));
    }

    /**
     * @return array{webhook_url: ?string, webhook_secret: ?string}|null
     */
    public static function config($userId): ?array
    {
        if (! self::isPartner($userId)) {
            return null;
        }

        return config('partners.partners')[(int) $userId];
    }

    /**
     * The partner's still-active request (not cancelled, not completed) for an external reference.
     * Cancelled/completed requests never block a new attempt with the same reference (automatic
     * re-request after "no driver found").
     */
    public static function activeRequestFor($userId, string $reference): ?Request
    {
        return Request::where('user_id', $userId)
            ->where('partner_reference', $reference)
            ->where('is_cancelled', false)
            ->where('is_completed', false)
            ->latest()
            ->first();
    }

    /**
     * Signature sent in X-MerciE-Signature: HMAC-SHA256 over "{timestamp}.{raw body}".
     */
    public static function sign(string $secret, int $timestamp, string $body): string
    {
        return 'sha256='.hash_hmac('sha256', $timestamp.'.'.$body, $secret);
    }
}
