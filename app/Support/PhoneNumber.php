<?php

namespace App\Support;

/**
 * One spelling for a phone number, so the same customer is recognised however they typed it
 * (game sign-up, booking attendee phone, voucher checks): digits only, with the country code.
 * A bare 8-digit number is an Omani one.
 */
class PhoneNumber
{
    public const DEFAULT_COUNTRY_CODE = '968';

    public static function normalize(?string $raw): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) self::toAscii($raw));

        if ($digits === '' || $digits === null) {
            return null;
        }

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (strlen($digits) === 8) {
            $digits = self::DEFAULT_COUNTRY_CODE . $digits;
        }

        return strlen($digits) >= 10 && strlen($digits) <= 15 ? $digits : null;
    }

    /** An Omani mobile: +968 then 8 digits starting with 7 or 9. */
    public static function isOmaniMobile(?string $normalized): bool
    {
        return $normalized !== null && (bool) preg_match('/^968[79]\d{7}$/', $normalized);
    }

    /** The number without its country code, as people say it (95512928). */
    public static function local(string $normalized): string
    {
        return str_starts_with($normalized, self::DEFAULT_COUNTRY_CODE) && strlen($normalized) === 11
            ? substr($normalized, 3)
            : $normalized;
    }

    /**
     * For public screens (leaderboards on stadium displays): the local number with its middle
     * hidden, 95512928 → 955**928. Unique enough to recognise yourself, useless to anyone else.
     */
    public static function mask(?string $normalized): string
    {
        if ($normalized === null || $normalized === '') {
            return '';
        }

        $local = self::local($normalized);
        $keep = strlen($local) >= 8 ? 3 : max(1, intdiv(strlen($local), 3));

        return substr($local, 0, $keep) . str_repeat('*', max(2, strlen($local) - 2 * $keep)) . substr($local, -$keep);
    }

    /**
     * The number as typed, but in plain ASCII: Arabic-Indic and Persian digits (an Arabic keyboard)
     * become 0-9, the invisible direction marks a pasted number from WhatsApp or the contacts app
     * carries are dropped, and non-breaking spaces become spaces. Nothing else changes.
     */
    public static function toAscii(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }

        $raw = strtr($raw, [
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            "\u{00A0}" => ' ', "\u{202F}" => ' ',
        ]);

        return trim(preg_replace('/[\x{200B}-\x{200F}\x{202A}-\x{202E}\x{2066}-\x{2069}\x{FEFF}]/u', '', $raw));
    }
}
