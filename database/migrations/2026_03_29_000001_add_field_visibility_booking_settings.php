<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('booking_settings')->insert([
            [
                'key'         => 'show_email',
                'value'       => 'true',
                'type'        => 'boolean',
                'description' => 'Show email field in booking form',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'key'         => 'show_phone',
                'value'       => 'true',
                'type'        => 'boolean',
                'description' => 'Show mobile number field in booking form',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'key'         => 'show_date_of_birth',
                'value'       => 'true',
                'type'        => 'boolean',
                'description' => 'Show date of birth field in booking form',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'key'         => 'show_gender',
                'value'       => 'true',
                'type'        => 'boolean',
                'description' => 'Show gender field in booking form',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'key'         => 'show_nationality',
                'value'       => 'true',
                'type'        => 'boolean',
                'description' => 'Show nationality field in booking form',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('booking_settings')->whereIn('key', [
            'show_email',
            'show_phone',
            'show_date_of_birth',
            'show_gender',
            'show_nationality',
        ])->delete();
    }
};
