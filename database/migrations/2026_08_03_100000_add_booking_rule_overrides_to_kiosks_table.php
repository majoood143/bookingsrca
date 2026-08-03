<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kiosks', function (Blueprint $table) {
            $table->boolean('override_booking_rules')->default(false)->after('event_id');
            $table->unsignedInteger('min_tickets_per_booking')->nullable()->after('override_booking_rules');
            $table->unsignedInteger('max_tickets_per_booking')->nullable()->after('min_tickets_per_booking');
            $table->unsignedInteger('max_attendee_age_years')->nullable()->after('max_tickets_per_booking');
            $table->unsignedInteger('pending_booking_expiry_minutes')->nullable()->after('max_attendee_age_years');
            $table->boolean('show_slot_end_time')->nullable()->after('pending_booking_expiry_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('kiosks', function (Blueprint $table) {
            $table->dropColumn([
                'override_booking_rules',
                'min_tickets_per_booking',
                'max_tickets_per_booking',
                'max_attendee_age_years',
                'pending_booking_expiry_minutes',
                'show_slot_end_time',
            ]);
        });
    }
};
