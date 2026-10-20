<?php

namespace App\Services\Messaging;

use App\Models\BookingSetting;
use App\Models\PhoneOtp;
use App\Support\PhoneNumber;

/**
 * One-time codes that prove a customer holds a phone (game sign-up). A code goes by WhatsApp,
 * or by SMS when WhatsApp cannot send it and the fallback is on (CustomerMessenger).
 *
 * A code is four digits, lives five minutes, allows five wrong tries and works once. Only a keyed
 * hash of it is stored. Asking for codes is limited per phone (one a minute, five an hour) and per
 * address (two hundred an hour: a venue's Wi-Fi or a mobile carrier puts many phones behind one
 * address), counted from the database so it holds whatever the cache driver is.
 */
class OtpService
{
    public const DIGITS = 4;

    public const TTL_MINUTES = 5;

    public const MAX_ATTEMPTS = 5;

    public const RESEND_SECONDS = 60;

    public const MAX_PER_PHONE_PER_HOUR = 5;

    public const MAX_PER_IP_PER_HOUR = 200;

    public const VERIFIED = 'verified';

    public const WRONG = 'wrong';

    public const EXPIRED = 'expired';

    public function __construct(private readonly CustomerMessenger $messenger) {}

    public function request(?string $rawPhone, ?string $ip, string $purpose = 'game'): OtpRequestResult
    {
        $phone = PhoneNumber::normalize($rawPhone);

        if ($phone === null) {
            return new OtpRequestResult(OtpRequestResult::INVALID_PHONE);
        }

        if (! $this->messenger->canSend()) {
            return new OtpRequestResult(OtpRequestResult::UNAVAILABLE);
        }

        if (($wait = $this->throttleSeconds($phone, $ip)) > 0) {
            return new OtpRequestResult(OtpRequestResult::THROTTLED, $wait);
        }

        $code = str_pad((string) random_int(0, 10 ** self::DIGITS - 1), self::DIGITS, '0', STR_PAD_LEFT);

        // a newer code replaces any earlier one still waiting
        PhoneOtp::query()->where('phone', $phone)->whereNull('consumed_at')->update(['consumed_at' => now()]);

        $otp = PhoneOtp::create([
            'phone' => $phone,
            'purpose' => $purpose,
            'code_hash' => $this->hash($phone, $code),
            'expires_at' => now()->addMinutes(self::TTL_MINUTES),
            'ip' => $ip,
        ]);

        $result = $this->messenger->send($phone, __('games.messages.otp', [
            'code' => $code,
            'minutes' => self::TTL_MINUTES,
            'site' => BookingSetting::get('site_name_' . app()->getLocale(), config('app.name')),
        ]), 'otp', sensitive: true);

        if (! $result->ok) {
            // the customer cannot use a code they never got
            $otp->update(['consumed_at' => now()]);

            return new OtpRequestResult(OtpRequestResult::UNAVAILABLE);
        }

        $otp->update(['channel' => $result->channel]);

        return new OtpRequestResult(OtpRequestResult::SENT, self::RESEND_SECONDS, $result->demo ? $code : null, $result->channel);
    }

    /**
     * Check a code and use it up when it is right.
     *
     * @return string VERIFIED|WRONG|EXPIRED
     */
    public function check(?string $rawPhone, ?string $rawCode): string
    {
        $phone = PhoneNumber::normalize($rawPhone);
        $code = preg_replace('/\D+/', '', (string) PhoneNumber::toAscii($rawCode));

        if ($phone === null || $code === '') {
            return self::WRONG;
        }

        $otp = PhoneOtp::query()
            ->where('phone', $phone)
            ->whereNull('consumed_at')
            ->latest('id')
            ->first();

        if (! $otp || $otp->expires_at->isPast()) {
            return self::EXPIRED;
        }

        // Count the try before looking at it, so a guess is spent even if the request dies. One
        // conditional UPDATE, so guesses sent all at once cannot get past the limit together.
        $counted = PhoneOtp::query()
            ->whereKey($otp->getKey())
            ->whereNull('consumed_at')
            ->where('attempts', '<', self::MAX_ATTEMPTS)
            ->increment('attempts');

        if ($counted === 0) {
            return self::EXPIRED;
        }

        if (! hash_equals($otp->code_hash, $this->hash($phone, $code))) {
            return self::WRONG;
        }

        // and a right code works once, even when sent twice at the same moment
        $used = PhoneOtp::query()->whereKey($otp->getKey())->whereNull('consumed_at')->update(['consumed_at' => now()]);

        return $used === 1 ? self::VERIFIED : self::EXPIRED;
    }

    /** Seconds until another code may be asked for, 0 when one may be asked for now. */
    private function throttleSeconds(string $phone, ?string $ip): int
    {
        $last = PhoneOtp::query()->where('phone', $phone)->latest('id')->first();

        if ($last && $last->created_at->gt(now()->subSeconds(self::RESEND_SECONDS))) {
            return max(1, self::RESEND_SECONDS - (int) $last->created_at->diffInSeconds(now(), true));
        }

        $hour = now()->subHour();
        $perPhone = PhoneOtp::query()->where('phone', $phone)->where('created_at', '>=', $hour)->count();
        $perIp = $ip ? PhoneOtp::query()->where('ip', $ip)->where('created_at', '>=', $hour)->count() : 0;

        if ($perPhone >= self::MAX_PER_PHONE_PER_HOUR || $perIp >= self::MAX_PER_IP_PER_HOUR) {
            $oldest = PhoneOtp::query()
                ->where($perPhone >= self::MAX_PER_PHONE_PER_HOUR ? 'phone' : 'ip', $perPhone >= self::MAX_PER_PHONE_PER_HOUR ? $phone : $ip)
                ->where('created_at', '>=', $hour)
                ->oldest('id')
                ->first();

            return $oldest ? max(60, 3600 - (int) $oldest->created_at->diffInSeconds(now(), true)) : 3600;
        }

        return 0;
    }

    private function hash(string $phone, string $code): string
    {
        return hash_hmac('sha256', $code, config('app.key') . '|' . $phone);
    }
}
