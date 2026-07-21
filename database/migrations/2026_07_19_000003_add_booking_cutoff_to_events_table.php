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
            $table->boolean('booking_cutoff_enabled')->default(false)->after('max_attendees');
            $table->unsignedInteger('booking_cutoff_value')->nullable()->after('booking_cutoff_enabled');
            $table->enum('booking_cutoff_unit', ['hours', 'minutes'])->nullable()->after('booking_cutoff_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'booking_cutoff_enabled',
                'booking_cutoff_value',
                'booking_cutoff_unit',
            ]);
        });
    }
};
