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
        Schema::table('event_signage_settings', function (Blueprint $table) {
            $table->unsignedInteger('language_switch_seconds')->default(10)->after('upcoming_trips_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_signage_settings', function (Blueprint $table) {
            $table->dropColumn('language_switch_seconds');
        });
    }
};
