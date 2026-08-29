<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('kiosks', function (Blueprint $table) {
            $table->boolean('override_min_tickets_per_booking')->default(false)->after('override_booking_rules');
            $table->boolean('override_max_tickets_per_booking')->default(false)->after('override_min_tickets_per_booking');
            $table->boolean('override_max_attendee_age_years')->default(false)->after('override_max_tickets_per_booking');
            $table->boolean('override_pending_booking_expiry_minutes')->default(false)->after('override_max_attendee_age_years');
            $table->boolean('override_show_slot_end_time')->default(false)->after('override_pending_booking_expiry_minutes');

            $table->boolean('override_booking_cutoff')->default(false)->after('show_slot_end_time');
            $table->boolean('booking_cutoff_enabled')->nullable()->after('override_booking_cutoff');
            $table->unsignedInteger('booking_cutoff_value')->nullable()->after('booking_cutoff_enabled');
            $table->enum('booking_cutoff_unit', ['hours', 'minutes'])->nullable()->after('booking_cutoff_value');

            $table->boolean('override_capacity_enforcement')->default(false)->after('booking_cutoff_unit');
            $table->boolean('enforce_capacity')->nullable()->after('override_capacity_enforcement');
        });

        // Preserve current behaviour for already-configured kiosks: the old
        // all-or-nothing toggle covered exactly these five fields.
        DB::table('kiosks')->where('override_booking_rules', true)->update([
            'override_min_tickets_per_booking' => true,
            'override_max_tickets_per_booking' => true,
            'override_max_attendee_age_years' => true,
            'override_pending_booking_expiry_minutes' => true,
            'override_show_slot_end_time' => true,
        ]);

        Schema::table('kiosks', function (Blueprint $table) {
            $table->dropColumn('override_booking_rules');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kiosks', function (Blueprint $table) {
            $table->boolean('override_booking_rules')->default(false)->after('event_id');
        });

        DB::table('kiosks')
            ->where('override_min_tickets_per_booking', true)
            ->orWhere('override_max_tickets_per_booking', true)
            ->orWhere('override_max_attendee_age_years', true)
            ->orWhere('override_pending_booking_expiry_minutes', true)
            ->orWhere('override_show_slot_end_time', true)
            ->update(['override_booking_rules' => true]);

        Schema::table('kiosks', function (Blueprint $table) {
            $table->dropColumn([
                'override_min_tickets_per_booking',
                'override_max_tickets_per_booking',
                'override_max_attendee_age_years',
                'override_pending_booking_expiry_minutes',
                'override_show_slot_end_time',
                'override_booking_cutoff',
                'booking_cutoff_enabled',
                'booking_cutoff_value',
                'booking_cutoff_unit',
                'override_capacity_enforcement',
                'enforce_capacity',
            ]);
        });
    }
};
