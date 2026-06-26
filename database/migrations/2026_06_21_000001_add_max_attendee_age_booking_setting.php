<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('booking_settings')->insert([
            'key'         => 'max_attendee_age_years',
            'value'       => '75',
            'type'        => 'number',
            'description' => 'Maximum allowed age (in years) for an attendee based on date of birth',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('booking_settings')->where('key', 'max_attendee_age_years')->delete();
    }
};
