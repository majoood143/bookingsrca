<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $existing = DB::table('booking_settings')->where('key', 'module_cms_enabled')->first();

        if ($existing) {
            DB::table('booking_settings')
                ->where('key', 'module_cms_enabled')
                ->update([
                    'type' => 'boolean',
                    'description' => $existing->description ?: 'Enable the Content (CMS) module (pages, posts, categories, tags, menus) across the system',
                    'updated_at' => now(),
                ]);

            return;
        }

        DB::table('booking_settings')->insert([
            'key' => 'module_cms_enabled',
            'value' => 'true',
            'type' => 'boolean',
            'description' => 'Enable the Content (CMS) module (pages, posts, categories, tags, menus) across the system',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // No-op: fixing the `type` column is not meaningfully reversible.
    }
};
