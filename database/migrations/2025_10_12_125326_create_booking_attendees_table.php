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
        Schema::create('booking_attendees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->string('nationality')->nullable();
            $table->string('ticket_number')->unique(); // Individual ticket number
            $table->string('qr_code')->nullable(); // Individual QR code
            $table->string('pdf_path')->nullable(); // Individual PDF ticket
            $table->boolean('email_sent')->default(false);
            $table->timestamp('email_sent_at')->nullable();
            $table->boolean('checked_in')->default(false);
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamps();
        });

        // Update bookings table - remove attendee_id, keep quantity for reference
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'attendee_id')) {
                $table->dropForeign(['attendee_id']);
                $table->dropColumn('attendee_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_attendees');
    }
};
