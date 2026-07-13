<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('booking_settings')->insert([
            [
                'key' => 'pending_booking_expiry_minutes',
                'value' => '15',
                'type' => 'number',
                'description' => 'Minutes a pending (unpaid) booking is held before it is automatically cancelled',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'timezone',
                'value' => 'Asia/Muscat',
                'type' => 'text',
                'description' => 'Timezone used for displaying and scheduling dates across the system',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'currency_code',
                'value' => 'OMR',
                'type' => 'text',
                'description' => 'ISO currency code used for pricing and financial reports',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'currency_symbol',
                'value' => 'OMR',
                'type' => 'text',
                'description' => 'Currency symbol/label shown next to monetary amounts',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'app_logo',
                'value' => null,
                'type' => 'file',
                'description' => 'Logo shown in the control panel sidebar. Falls back to the default logo if not set',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'favicon',
                'value' => null,
                'type' => 'file',
                'description' => 'Favicon shown in the browser tab for the public site and control panel',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'panel_primary_color',
                'value' => '#16a34a',
                'type' => 'color',
                'description' => 'Primary accent color used throughout the control panel (buttons, links, highlights)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'module_kiosk_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Enable the self-service kiosk check-in module across the system',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'module_extra_services_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Enable extra/add-on services that can be attached to bookings',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'module_private_events_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Allow events to be created with private, password-protected access',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('booking_settings')->whereIn('key', [
            'pending_booking_expiry_minutes',
            'timezone',
            'currency_code',
            'currency_symbol',
            'app_logo',
            'favicon',
            'panel_primary_color',
            'module_kiosk_enabled',
            'module_extra_services_enabled',
            'module_private_events_enabled',
        ])->delete();
    }
};
