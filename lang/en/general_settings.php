<?php

return [

    'title' => 'General Settings',
    'save' => 'Save Settings',

    'tabs' => [
        'general' => 'General',
        'branding' => 'Branding & Appearance',
        'booking_rules' => 'Booking Rules',
        'attendee_fields' => 'Attendee Fields',
        'terms' => 'Terms & Conditions',
        'modules' => 'Modules',
    ],

    'sections' => [
        'site_identity' => 'Site Identity',
        'site_identity_desc' => 'The name shown in the header, browser title, and communications.',
        'localization' => 'Localization',
        'localization_desc' => 'Timezone and currency used across the system.',
        'logos' => 'Logos & Icons',
        'logos_desc' => 'Images used to brand the public booking site and the control panel.',
        'colors' => 'Theme Colors',
        'colors_desc' => 'Colors used on the public booking site and in the control panel.',
        'booking_rules' => 'Booking Rules',
        'attendee_fields' => 'Attendee Fields',
        'attendee_fields_desc' => 'Choose which fields are shown on the booking form.',
        'modules' => 'Modules',
        'modules_desc' => 'Enable or disable optional features across the system.',
    ],

    'fields' => [
        'site_name_en' => 'Site Name (English)',
        'site_name_ar' => 'Site Name (Arabic)',
        'timezone' => 'Timezone',
        'currency_code' => 'Currency',
        'currency_symbol' => 'Currency Symbol',
        'currency_icon' => 'Currency Icon (SVG)',
        'currency_icon_helper' => 'Optional SVG icon used instead of the currency symbol text on the booking page.',

        'site_logo' => 'Public Site Logo',
        'site_logo_helper' => 'Shown on the public booking site header.',
        'app_logo' => 'Control Panel Logo',
        'app_logo_helper' => 'Shown in the admin control panel sidebar.',
        'favicon' => 'Favicon',
        'favicon_helper' => 'Browser tab icon.',
        'primary_color' => 'Public Site Primary Color',
        'primary_color_helper' => 'Buttons and highlights on the public site.',
        'secondary_color' => 'Public Site Secondary Color',
        'secondary_color_helper' => 'Hover states and gradients on the public site.',
        'panel_primary_color' => 'Control Panel Theme Color',
        'panel_primary_color_helper' => 'Primary accent color used throughout the admin control panel.',

        'min_tickets_per_booking' => 'Minimum Tickets per Booking',
        'max_tickets_per_booking' => 'Maximum Tickets per Booking',
        'max_attendee_age_years' => 'Maximum Attendee Age (Years)',
        'pending_booking_expiry_minutes' => 'Pending Booking Expiry (Minutes)',
        'pending_booking_expiry_minutes_helper' => 'Unpaid bookings are automatically cancelled after this many minutes.',
        'show_slot_end_time' => 'Show Time Slot End Time',
        'show_slot_end_time_helper' => 'When off, only the start time is shown for time slots on the booking and kiosk pages (e.g. "09:00" instead of "09:00 - 10:00").',

        'show_email' => 'Show Email Field',
        'show_phone' => 'Show Mobile Number Field',
        'show_date_of_birth' => 'Show Date of Birth Field',
        'show_gender' => 'Show Gender Field',
        'show_nationality' => 'Show Nationality Field',
        'show_identity_number' => 'Show Identity Number Field',

        'terms_en' => 'Terms & Conditions (English)',
        'terms_ar' => 'Terms & Conditions (Arabic)',

        'module_kiosk_enabled' => 'Kiosk Check-in Module',
        'module_kiosk_enabled_helper' => 'Enables the self-service kiosk check-in module and its navigation.',
        'module_extra_services_enabled' => 'Extra Services Module',
        'module_extra_services_enabled_helper' => 'Enables add-on services that can be attached to bookings.',
        'module_private_events_enabled' => 'Private Events Module',
        'module_private_events_enabled_helper' => 'Allows events to be created with private, password-protected access.',
        'module_promo_codes_enabled' => 'Promo Codes Module',
        'module_promo_codes_enabled_helper' => 'Allows customers to apply promo codes for a discount at checkout.',
    ],

    'notifications' => [
        'updated' => 'Settings updated successfully.',
    ],

];
