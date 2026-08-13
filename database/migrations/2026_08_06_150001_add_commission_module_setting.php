<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('booking_settings')->insert([
            'key' => 'module_commission_enabled',
            'value' => 'true',
            'type' => 'boolean',
            'description' => 'Enable commission settings, the effective-commission column on Events, and the Commission Report',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('booking_settings')->where('key', 'module_commission_enabled')->delete();
    }
};
