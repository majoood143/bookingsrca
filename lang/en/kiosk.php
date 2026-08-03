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
        'booking_rules_desc' => 'Override the general settings booking rules for bookings made on this kiosk only.',
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
        'override_booking_rules'  => 'Override Booking Rules',
        'override_booking_rules_helper' => 'When enabled, this kiosk uses the rules below instead of the general settings Booking Rules.',
        'min_tickets_per_booking' => 'Minimum Tickets per Booking',
        'max_tickets_per_booking' => 'Maximum Tickets per Booking',
        'max_attendee_age_years'  => 'Maximum Attendee Age (Years)',
        'pending_booking_expiry_minutes' => 'Pending Booking Expiry (Minutes)',
        'pending_booking_expiry_minutes_helper' => 'Unpaid bookings made on this kiosk are auto-cancelled after this many minutes.',
        'show_slot_end_time'      => 'Show Slot End Time',
        'show_slot_end_time_helper' => 'Display the end time alongside the start time when this kiosk lists time slots.',
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
