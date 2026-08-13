<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('booking_settings')->insert([
            [
                'key' => 'checkin_date_restriction_enabled',
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'When enabled, the Check-In Scan page validates a booking\'s event date before allowing check-in',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'checkin_date_restriction_mode',
                'value' => 'block',
                'type' => 'text',
                'description' => 'How the Check-In Scan page reacts to a wrong-date booking: "block" refuses check-in, "warn" shows a popup but still allows it',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'checkin_date_grace_days',
                'value' => '0',
                'type' => 'number',
                'description' => 'Number of days before/after the scheduled event date during which check-in is still allowed without triggering the date restriction',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('booking_settings')->whereIn('key', [
            'checkin_date_restriction_enabled',
            'checkin_date_restriction_mode',
            'checkin_date_grace_days',
        ])->delete();
    }
};
