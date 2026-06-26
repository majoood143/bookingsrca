<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('booking_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('text'); // text, number, boolean
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default values
        DB::table('booking_settings')->insert([
            [
                'key' => 'max_tickets_per_booking',
                'value' => '10',
                'type' => 'number',
                'description' => 'Maximum number of tickets allowed per booking',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'min_tickets_per_booking',
                'value' => '1',
                'type' => 'number',
                'description' => 'Minimum number of tickets required per booking',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_settings');
    }

    
};
