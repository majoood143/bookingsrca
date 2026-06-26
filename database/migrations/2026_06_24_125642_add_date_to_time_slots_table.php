<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // time_slots is becoming date-specific; existing rows pool bookings across
        // every date an event runs on, so there is no sound way to backfill a date
        // per row. This data is confirmed disposable test/seed data.
        DB::table('booking_extra_services')->delete();
        DB::table('booking_attendees')->delete();
        DB::table('booking_payments')->delete();
        DB::table('payment_gateway_logs')->delete();
        DB::table('bookings')->delete();
        DB::table('time_slots')->delete();

        Schema::table('time_slots', function (Blueprint $table) {
            $table->date('date')->after('event_id');
            $table->unique(['event_id', 'date', 'start_time', 'end_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('time_slots', function (Blueprint $table) {
            $table->dropUnique(['event_id', 'date', 'start_time', 'end_time']);
            $table->dropColumn('date');
        });
    }
};
