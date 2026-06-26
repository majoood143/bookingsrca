<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('booking_settings')->insert([
            [
                'key'         => 'terms_en',
                'value'       => '',
                'type'        => 'richtext',
                'description' => 'Terms and conditions in English displayed on the booking form',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'key'         => 'terms_ar',
                'value'       => '',
                'type'        => 'richtext',
                'description' => 'Terms and conditions in Arabic displayed on the booking form',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('booking_settings')->whereIn('key', ['terms_en', 'terms_ar'])->delete();
    }
};
