<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('booking_settings')->insert([
            'key' => 'module_pos_enabled',
            'value' => 'true',
            'type' => 'boolean',
            'description' => 'Enable the Point of Sale module (counter screen, terminals, products, discounts, shifts, transactions) across the system',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('booking_settings')->where('key', 'module_pos_enabled')->delete();
    }
};
