<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('booking_settings')->insert([
            'key'         => 'show_slot_end_time',
            'value'       => 'true',
            'type'        => 'boolean',
            'description' => 'Show the time slot end time on the booking and kiosk pages',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('booking_settings')->where('key', 'show_slot_end_time')->delete();
    }
};
