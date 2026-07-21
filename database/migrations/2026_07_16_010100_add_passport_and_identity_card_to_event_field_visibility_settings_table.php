<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_field_visibility_settings', function (Blueprint $table) {
            $table->boolean('event_booking_show_passport_number')->default(true)->after('event_booking_require_identity_number');
            $table->boolean('event_booking_require_passport_number')->default(false)->after('event_booking_show_passport_number');
            $table->boolean('event_booking_show_identity_card_upload')->default(true)->after('event_booking_require_passport_number');
            $table->boolean('event_booking_require_identity_card_upload')->default(false)->after('event_booking_show_identity_card_upload');
            $table->boolean('event_booking_show_passport_upload')->default(true)->after('event_booking_require_identity_card_upload');
            $table->boolean('event_booking_require_passport_upload')->default(false)->after('event_booking_show_passport_upload');

            $table->boolean('kiosk_show_passport_number')->default(true)->after('kiosk_require_identity_number');
            $table->boolean('kiosk_require_passport_number')->default(false)->after('kiosk_show_passport_number');
            $table->boolean('kiosk_show_identity_card_upload')->default(true)->after('kiosk_require_passport_number');
            $table->boolean('kiosk_require_identity_card_upload')->default(false)->after('kiosk_show_identity_card_upload');
            $table->boolean('kiosk_show_passport_upload')->default(true)->after('kiosk_require_identity_card_upload');
            $table->boolean('kiosk_require_passport_upload')->default(false)->after('kiosk_show_passport_upload');
        });
    }

    public function down(): void
    {
        Schema::table('event_field_visibility_settings', function (Blueprint $table) {
            $table->dropColumn([
                'event_booking_show_passport_number',
                'event_booking_require_passport_number',
                'event_booking_show_identity_card_upload',
                'event_booking_require_identity_card_upload',
                'event_booking_show_passport_upload',
                'event_booking_require_passport_upload',
                'kiosk_show_passport_number',
                'kiosk_require_passport_number',
                'kiosk_show_identity_card_upload',
                'kiosk_require_identity_card_upload',
                'kiosk_show_passport_upload',
                'kiosk_require_passport_upload',
            ]);
        });
    }
};
