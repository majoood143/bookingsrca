<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_attendees', function (Blueprint $table) {
            $table->string('passport_number', 50)->nullable()->after('identity_number');
            $table->string('identity_card_path')->nullable()->after('passport_number');
            $table->string('passport_path')->nullable()->after('identity_card_path');
        });
    }

    public function down(): void
    {
        Schema::table('booking_attendees', function (Blueprint $table) {
            $table->dropColumn(['passport_number', 'identity_card_path', 'passport_path']);
        });
    }
};
