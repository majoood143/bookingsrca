<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('event_field_visibility_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->unique()->constrained()->cascadeOnDelete();

            // Event booking page (public web flow) — used only when override is enabled,
            // otherwise the global booking settings apply.
            $table->boolean('event_booking_override_enabled')->default(false);
            $table->boolean('event_booking_show_email')->default(true);
            $table->boolean('event_booking_require_email')->default(true);
            $table->boolean('event_booking_show_phone')->default(true);
            $table->boolean('event_booking_require_phone')->default(true);
            $table->boolean('event_booking_show_date_of_birth')->default(true);
            $table->boolean('event_booking_require_date_of_birth')->default(false);
            $table->boolean('event_booking_show_gender')->default(true);
            $table->boolean('event_booking_require_gender')->default(true);
            $table->boolean('event_booking_show_nationality')->default(true);
            $table->boolean('event_booking_require_nationality')->default(false);
            $table->boolean('event_booking_show_identity_number')->default(true);
            $table->boolean('event_booking_require_identity_number')->default(true);

            // Kiosk booking flow — independent override from the event booking page above.
            $table->boolean('kiosk_override_enabled')->default(false);
            $table->boolean('kiosk_show_email')->default(true);
            $table->boolean('kiosk_require_email')->default(true);
            $table->boolean('kiosk_show_phone')->default(true);
            $table->boolean('kiosk_require_phone')->default(true);
            $table->boolean('kiosk_show_date_of_birth')->default(true);
            $table->boolean('kiosk_require_date_of_birth')->default(false);
            $table->boolean('kiosk_show_gender')->default(true);
            $table->boolean('kiosk_require_gender')->default(true);
            $table->boolean('kiosk_show_nationality')->default(true);
            $table->boolean('kiosk_require_nationality')->default(false);
            $table->boolean('kiosk_show_identity_number')->default(true);
            $table->boolean('kiosk_require_identity_number')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_field_visibility_settings');
    }
};
