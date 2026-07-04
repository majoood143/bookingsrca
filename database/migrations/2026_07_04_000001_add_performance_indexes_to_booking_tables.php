<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->index('status');
            $table->index('event_date');
            $table->index('payment_status');
            $table->index('created_at');
            $table->index(['event_id', 'status']);
            $table->index(['event_id', 'event_date']);
        });

        Schema::table('booking_attendees', function (Blueprint $table) {
            $table->index('checked_in');
            $table->index('email_sent');
        });

        Schema::table('ticket_types', function (Blueprint $table) {
            $table->index('is_active');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->index('status');
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['event_date']);
            $table->dropIndex(['payment_status']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['event_id', 'status']);
            $table->dropIndex(['event_id', 'event_date']);
        });

        Schema::table('booking_attendees', function (Blueprint $table) {
            $table->dropIndex(['checked_in']);
            $table->dropIndex(['email_sent']);
        });

        Schema::table('ticket_types', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['start_date', 'end_date']);
        });
    }
};
