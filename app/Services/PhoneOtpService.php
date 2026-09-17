<?php

namespace App\Services;

use App\Services\Sms\SmsChannelInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class PhoneOtpService
{
    protected const EXPIRES_IN_MINUTES = 5;

    protected const MAX_ATTEMPTS = 5;

    protected const RESEND_SECONDS = 60;

    public function __construct(protected SmsChannelInterface $sms)
    {
    }

    /**
     * Generate a fresh OTP for the given phone number, store it, and send it via SMS.
     *
     * @throws ValidationException if a code was already sent too recently
     */
    public function send(string $phone): void
    {
        $rateLimitKey = 'otp-send:'.$phone;

        if (RateLimiter::tooManyAttempts($rateLimitKey, 1)) {
            throw ValidationException::withMessages([
                'phone' => __('Please wait :seconds seconds before requesting another code.', [
                    'seconds' => RateLimiter::availableIn($rateLimitKey),
                ]),
            ]);
        }

        $otp = (string) random_int(100000, 999999);

        DB::table('phone_otps')->updateOrInsert(
            ['phone' => $phone],
            [
                'otp' => Hash::make($otp),
                'attempts' => 0,
                'expires_at' => now()->addMinutes(self::EXPIRES_IN_MINUTES),
                'created_at' => now(),
            ]
        );

        RateLimiter::hit($rateLimitKey, self::RESEND_SECONDS);

        $this->sms->send($phone, "Your Uujyalo Stream verification code is {$otp}. It expires in ".self::EXPIRES_IN_MINUTES.' minutes.');
    }

    /**
     * Verify a submitted OTP for the given phone number. Consumes the code on success.
     */
    public function verify(string $phone, string $otp): bool
    {
        $record = DB::table('phone_otps')->where('phone', $phone)->first();

        if (! $record || now()->greaterThan($record->expires_at) || $record->attempts >= self::MAX_ATTEMPTS) {
            return false;
        }

        if (! Hash::check($otp, $record->otp)) {
            DB::table('phone_otps')->where('phone', $phone)->increment('attempts');

            return false;
        }

        DB::table('phone_otps')->where('phone', $phone)->delete();

        return true;
    }
}
