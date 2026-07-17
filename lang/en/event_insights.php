<?php

return [

    'sections' => [
        'summary'   => 'Summary',
        'trend'     => 'Bookings & Revenue Trend',
        'by_ticket' => 'Breakdown by Ticket Type',
    ],

    'stats' => [
        'total_bookings'  => 'Total Bookings',
        'confirmed'       => 'Confirmed',
        'pending'         => 'Pending',
        'cancelled'       => 'Cancelled',
        'checked_in'      => 'Checked In',
        'total_attendees' => 'Total Attendees',
        'total_revenue'   => 'Total Revenue',
        'total_discount'  => 'Total Discounts',
        'capacity'        => 'Capacity Filled',
        'unlimited'       => 'Unlimited',
    ],

    'no_data' => 'No bookings recorded for this period.',

    'actions' => [
        'download_pdf'     => 'Download Insights PDF',
        'email_settings'   => 'Email Report Settings',
        'send_now'         => 'Send Now (Last 7 Days)',
        'send_now_confirm' => 'This will immediately email the last 7 days of performance to the configured recipients.',
    ],

    'form' => [
        'recipients'             => 'Recipients',
        'recipients_placeholder' => 'Enter an email and press Enter',
        'invalid_email'          => ':email is not a valid email address.',
        'is_enabled'             => 'Send weekly on the schedule below',
        'send_day'               => 'Day of Week',
        'send_time'              => 'Send Time',
    ],

    'days' => [
        'sunday'    => 'Sunday',
        'monday'    => 'Monday',
        'tuesday'   => 'Tuesday',
        'wednesday' => 'Wednesday',
        'thursday'  => 'Thursday',
        'friday'    => 'Friday',
        'saturday'  => 'Saturday',
    ],

    'notifications' => [
        'settings_saved' => 'Email report settings saved.',
        'send_queued'    => 'The report has been queued for sending.',
    ],

    'export' => [
        'filename' => 'event-insights',
    ],

    'email' => [
        'subject'         => 'Performance Report: :event',
        'heading'         => 'Event Performance Report',
        'intro'           => 'Here is a summary of this event\'s performance for the period below. The full report is attached as a PDF.',
        'attachment_note' => 'See the attached PDF for the full breakdown by ticket type and daily trend.',
    ],

];
