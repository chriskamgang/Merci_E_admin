<?php

namespace App\Base\Services\OTP;

use App\Models\MailOtp;
use App\Models\MobileOtp;
use Illuminate\Support\Facades\DB;

/**
 * Verifies and consumes the one-time codes issued by
 * POST /api/v1/mobile-otp (mobile_otp_verifications) and
 * POST /api/v1/send-mail-otp (mail_otp_verifications).
 *
 * A code is accepted only if it matches the latest code issued for that exact
 * mobile/email, was issued less than EXPIRY_MINUTES ago, and has not been used.
 * Successful verification deletes the code, so it can be used once only.
 */
class OtpVerifier
{
    public const EXPIRY_MINUTES = 10;

    /**
     * Verify and consume a mobile OTP.
     *
     * @param  string|null  $otp  the code the client received by SMS. When null,
     *                            a code already verified through /validate-otp is accepted
     *                            (only used for the legacy login fallback).
     */
    public static function consumeMobileOtp(?string $mobile, ?string $otp): bool
    {
        if (blank($mobile)) {
            return false;
        }

        if ($otp !== null && self::isDemoCode($otp)) {
            MobileOtp::where('mobile', $mobile)->delete();

            return true;
        }

        return self::consume(MobileOtp::class, 'mobile', $mobile, $otp);
    }

    /**
     * Verify and consume an email OTP.
     */
    public static function consumeEmailOtp(?string $email, ?string $otp): bool
    {
        if (blank($email) || blank($otp)) {
            return false;
        }

        if (self::isDemoCode($otp)) {
            MailOtp::where('email', $email)->delete();

            return true;
        }

        return self::consume(MailOtp::class, 'email', $email, $otp);
    }

    /**
     * Same behaviour as the existing demo shortcut in LoginController::validateSmsOtp.
     */
    protected static function isDemoCode(string $otp): bool
    {
        return env('APP_FOR') == 'demo' && $otp === '123456';
    }

    protected static function consume(string $model, string $column, string $value, ?string $otp): bool
    {
        return DB::transaction(function () use ($model, $column, $value, $otp) {
            $record = $model::where($column, $value)
                ->orderByDesc('updated_at')
                ->lockForUpdate()
                ->first();

            if (! $record || blank($record->otp) || ! $record->updated_at) {
                return false;
            }

            if ($record->updated_at->lt(now()->subMinutes(self::EXPIRY_MINUTES))) {
                return false;
            }

            if ($otp !== null) {
                if (blank($otp) || ! hash_equals((string) $record->otp, (string) $otp)) {
                    return false;
                }
            } elseif (! $record->verified) {
                return false;
            }

            // Single use: remove every code issued for this identifier.
            $model::where($column, $value)->delete();

            return true;
        });
    }
}
