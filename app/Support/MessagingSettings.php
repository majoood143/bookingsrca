<?php

namespace App\Support;

use App\Models\BookingSetting;
use Illuminate\Support\Facades\Crypt;

/**
 * Customer messaging, as set in General Settings → Messaging: WhatsApp on/off and its sending
 * instance, and the SMS fallback (on/off, gateway, credentials). The Evolution API URL and key
 * are not here: the WhatsApp plugin reads them from .env (EVOLUTION_*).
 */
final class MessagingSettings
{
    public static function whatsappEnabled(): bool
    {
        return (bool) BookingSetting::get('whatsapp_enabled', false);
    }

    /** The plugin's instance id (a UUID), or null to use the first connected instance. */
    public static function whatsappInstanceId(): ?string
    {
        $id = trim((string) BookingSetting::get('whatsapp_instance_id', ''));

        return $id === '' ? null : $id;
    }

    public static function smsFallbackEnabled(): bool
    {
        return (bool) BookingSetting::get('otp_sms_fallback_enabled', false);
    }

    public static function smsDriver(): string
    {
        return (string) BookingSetting::get('sms_driver', '');
    }

    public static function get(string $key, mixed $default = ''): mixed
    {
        return BookingSetting::get($key, $default);
    }

    /** A stored secret (encrypted at rest); empty when unset or unreadable after an APP_KEY change. */
    public static function secret(string $key): string
    {
        $stored = (string) BookingSetting::get($key, '');

        if ($stored === '') {
            return '';
        }

        return (string) rescue(fn () => Crypt::decryptString($stored), '', false);
    }

    public static function encryptSecret(string $plain): string
    {
        return $plain === '' ? '' : Crypt::encryptString($plain);
    }
}
