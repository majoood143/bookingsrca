<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('organizer_phone')->nullable()->after('organizer');
            $table->string('location_link')->nullable()->after('location');
            $table->json('timeline')->nullable()->after('description'); // Translatable
            $table->json('faq')->nullable()->after('timeline');
            $table->json('terms_and_conditions')->nullable()->after('faq'); // Translatable
            $table->string('promotional_video_url')->nullable()->after('terms_and_conditions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'organizer_phone',
                'location_link',
                'timeline',
                'faq',
                'terms_and_conditions',
                'promotional_video_url',
            ]);
        });
    }
};
