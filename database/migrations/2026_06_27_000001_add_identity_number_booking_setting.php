<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('booking_settings')->insert([
            'key'         => 'show_identity_number',
            'value'       => 'true',
            'type'        => 'boolean',
            'description' => 'Show identity number field in booking form',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('booking_settings')->where('key', 'show_identity_number')->delete();
    }
};
