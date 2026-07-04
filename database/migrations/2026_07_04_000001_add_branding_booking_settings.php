<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('booking_settings')->insert([
            [
                'key' => 'site_name_en',
                'value' => 'Bookings',
                'type' => 'text',
                'description' => 'Site name shown in the header and browser title (English)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'site_name_ar',
                'value' => 'الحجوزات',
                'type' => 'text',
                'description' => 'Site name shown in the header and browser title (Arabic)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'site_logo',
                'value' => null,
                'type' => 'file',
                'description' => 'Logo shown in the site header. Falls back to the default logo if not set',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'primary_color',
                'value' => '#05602b',
                'type' => 'color',
                'description' => 'Primary brand color used across buttons and highlights on the public site',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'secondary_color',
                'value' => '#0da74c',
                'type' => 'color',
                'description' => 'Secondary brand color used for hover states and gradients on the public site',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('booking_settings')->whereIn('key', [
            'site_name_en',
            'site_name_ar',
            'site_logo',
            'primary_color',
            'secondary_color',
        ])->delete();
    }
};
