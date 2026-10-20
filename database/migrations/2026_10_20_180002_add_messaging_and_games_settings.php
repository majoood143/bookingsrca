<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Settings for the Games module and customer messaging (General Settings → Modules / Messaging).
 * Typed rows, so booleans and numbers read back as such through BookingSetting::get().
 */
return new class extends Migration
{
    private const SETTINGS = [
        ['module_games_enabled', 'true', 'boolean', 'Enable games (e.g. the penalty challenge) that collect players and award vouchers'],
        ['whatsapp_enabled', 'false', 'boolean', 'Send customer messages (one-time codes, winner vouchers) by WhatsApp'],
        ['whatsapp_instance_id', '', 'text', 'The WhatsApp instance that sends customer messages (empty = first connected)'],
        ['otp_sms_fallback_enabled', 'false', 'boolean', 'Send one-time codes by SMS when WhatsApp cannot send them'],
        ['sms_driver', '', 'text', 'SMS gateway used for the SMS fallback'],
        ['sms_tamimah_username', '', 'text', 'Tamimah SMS username'],
        ['sms_tamimah_password', '', 'text', 'Tamimah SMS password (encrypted)'],
        ['sms_tamimah_sender', '', 'text', 'Tamimah SMS sender name'],
        ['sms_tamimah_app_id', '', 'text', 'Tamimah SMS application id'],
        ['sms_tamimah_endpoint', '', 'text', 'Tamimah SMS endpoint (empty = default)'],
        ['sms_tamimah_success_codes', '', 'text', 'Tamimah status codes that mean sent (comma separated, empty = any)'],
        ['sms_tamimah_priority', '0', 'number', 'Tamimah SMS priority'],
    ];

    public function up(): void
    {
        foreach (self::SETTINGS as [$key, $value, $type, $description]) {
            if (DB::table('booking_settings')->where('key', $key)->exists()) {
                continue;
            }

            DB::table('booking_settings')->insert([
                'key' => $key,
                'value' => $value,
                'type' => $type,
                'description' => $description,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('booking_settings')->whereIn('key', array_column(self::SETTINGS, 0))->delete();
    }
};
