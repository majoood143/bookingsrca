<?php

return [

    'navigation' => [
        'group'  => 'Kiosk Management',
        'label'  => 'Kiosk',
        'plural' => 'Kiosks',
    ],

    'sections' => [
        'information'      => 'Kiosk Information',
        'information_desc' => 'Identify this device and which event it serves.',
        'payment'           => 'Payment & Behaviour',
        'payment_desc'      => 'Which payment methods this kiosk offers, and how quickly it resets an idle session.',
        'booking_rules'      => 'Booking Rules',
        'booking_rules_desc' => 'Enable any rule below to override it for this kiosk only. Rules left off keep using the general settings Booking Rules.',
        'hardware'          => 'Hardware Status',
        'hardware_desc'     => 'Reported automatically by the kiosk app via its heartbeat — read only.',
        'receipt'           => 'Receipt',
        'receipt_desc'      => 'Footer text printed on the customer receipt.',
        'slideshow'         => 'Welcome Screen Slideshow',
        'slideshow_desc'    => 'Background images that fade through on the "tap to start" screen while the kiosk sits idle.',
    ],

    'fields' => [
        'name'                    => 'Kiosk Name',
        'code'                    => 'Kiosk Code',
        'code_helper'             => 'Unique identifier used in the kiosk URL and device provisioning. Cannot be changed after creation.',
        'event'                   => 'Event',
        'event_helper'            => 'Leave empty to let the customer pick from all bookable events on this kiosk.',
        'is_active'               => 'Active',
        'is_active_helper'        => 'Inactive kiosks show a maintenance screen instead of the booking flow.',
        'idle_timeout_seconds'    => 'Idle Timeout (seconds)',
        'idle_timeout_helper'     => 'Resets the booking flow back to the start after this many seconds of inactivity.',
        'enabled_payment_methods' => 'Enabled Payment Methods',

        'min_tickets_per_booking' => 'Minimum Tickets per Booking',
        'override_min_tickets_per_booking' => 'Override Minimum Tickets',
        'override_min_tickets_per_booking_helper' => 'When enabled, this kiosk uses its own minimum instead of the general settings value.',

        'max_tickets_per_booking' => 'Maximum Tickets per Booking',
        'override_max_tickets_per_booking' => 'Override Maximum Tickets',
        'override_max_tickets_per_booking_helper' => 'When enabled, this kiosk uses its own maximum instead of the general settings value.',

        'max_attendee_age_years'  => 'Maximum Attendee Age (Years)',
        'override_max_attendee_age_years' => 'Override Maximum Attendee Age',
        'override_max_attendee_age_years_helper' => 'When enabled, this kiosk uses its own maximum attendee age instead of the general settings value.',

        'pending_booking_expiry_minutes' => 'Pending Booking Expiry (Minutes)',
        'pending_booking_expiry_minutes_helper' => 'Unpaid bookings made on this kiosk are auto-cancelled after this many minutes.',
        'override_pending_booking_expiry_minutes' => 'Override Pending Booking Expiry',
        'override_pending_booking_expiry_minutes_helper' => 'When enabled, this kiosk uses its own expiry window instead of the general settings value.',

        'show_slot_end_time'      => 'Show Slot End Time',
        'show_slot_end_time_helper' => 'Display the end time alongside the start time when this kiosk lists time slots.',
        'override_show_slot_end_time' => 'Override Show Slot End Time',
        'override_show_slot_end_time_helper' => 'When enabled, this kiosk uses its own setting instead of the general settings value.',

        'override_booking_cutoff' => 'Override Booking Cutoff',
        'override_booking_cutoff_helper' => 'When enabled, this kiosk uses its own booking-cutoff window instead of the event\'s.',
        'booking_cutoff_enabled' => 'Close Booking Before Slot Starts',
        'booking_cutoff_enabled_helper' => 'When enabled, this kiosk stops offering a time slot once it falls within this window of starting.',
        'booking_cutoff_value' => 'Close Booking',
        'booking_cutoff_unit'  => 'Unit',
        'booking_cutoff_units' => [
            'hours'   => 'Hours',
            'minutes' => 'Minutes',
        ],

        'override_capacity_enforcement' => 'Override Capacity Enforcement',
        'override_capacity_enforcement_helper' => 'When enabled, this kiosk uses the setting below instead of always accepting walk-ins regardless of remaining stock.',
        'enforce_capacity' => 'Enforce Remaining Capacity',
        'enforce_capacity_helper' => 'When on, this kiosk blocks bookings once a time slot, ticket type, or extra service sells out — the same as the public booking page. When off (the default), walk-ins are always accepted and staff resolve any overbooking afterwards.',
        'receipt_footer_en'       => 'Receipt Footer (English)',
        'receipt_footer_ar'       => 'Receipt Footer (Arabic)',
        'slideshow_images'        => 'Slideshow Images',
        'slideshow_images_helper' => 'Up to 5 images, shown in this order. Landscape (16:9) photos work best. Leave empty to use the plain background instead.',
    ],

    'payment_methods' => [
        'wallet'         => 'Tap Card (Prepaid Wallet)',
        'pay_at_counter' => 'Pay at Counter',
    ],

    'columns' => [
        'name'            => 'Name',
        'code'            => 'Code',
        'event'           => 'Event',
        'all_events'      => 'All Events',
        'status'          => 'Status',
        'payment_methods' => 'Payment Methods',
        'reader'          => 'Reader',
        'printer'         => 'Printer',
        'last_seen'       => 'Last Seen',
        'app_version'     => 'App Version',
        'bookings'        => 'Bookings',
        'never'           => 'Never',
        'connected'       => 'Connected',
        'disconnected'    => 'Disconnected',
    ],

    'actions' => [
        'new'         => 'New Kiosk',
        'activate'    => 'Activate',
        'deactivate'  => 'Deactivate',
    ],

    'notifications' => [
        'created'          => 'Kiosk created.',
        'updated'          => 'Kiosk updated.',
        'status_updated'   => 'Kiosk status updated.',
        'kiosk_activated'  => 'Kiosk activated.',
        'kiosk_deactivated' => 'Kiosk deactivated.',
    ],

    'empty_state' => [
        'heading'     => 'No kiosks yet',
        'description' => 'Register a device to get its kiosk URL for provisioning.',
    ],
];
