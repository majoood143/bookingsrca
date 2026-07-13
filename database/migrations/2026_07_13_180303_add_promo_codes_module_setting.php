<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('booking_settings')->insert([
            'key' => 'module_promo_codes_enabled',
            'value' => 'true',
            'type' => 'boolean',
            'description' => 'Enable promo codes that customers can apply for a discount at checkout',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('booking_settings')->where('key', 'module_promo_codes_enabled')->delete();
    }
};
