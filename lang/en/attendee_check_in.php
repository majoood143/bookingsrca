<?php

return [
    'navigation' => [
        'label' => 'Check-In Scan',
    ],

    'title' => 'Attendee Check-In',

    'summary' => ':checked_in of :total checked in',

    'event_progress' => [
        'label' => 'Event Check-In Progress',
        'summary' => ':checked_in of :total attendees checked in',
        'complete' => 'All attendees checked in',
    ],

    'search' => [
        'label' => 'Scan or Enter Code',
        'placeholder' => 'Booking reference, ticket number, or phone number',
    ],

    'disambiguation' => [
        'prompt' => 'Multiple bookings match this phone number. Select one:',
    ],

    'actions' => [
        'fullscreen_on' => 'Full Screen',
        'fullscreen_off' => 'Exit Full Screen',
        'check_in_all' => 'Check In All (:count)',
        'check_in_all_confirm' => 'Check in all :count remaining attendee(s)?',
        'scan_another' => 'Scan Another',
    ],

    'notifications' => [
        'not_found_title' => 'No match found',
        'not_found_body' => 'No booking, ticket, or attendee matched that code.',
        'checked_in_title' => 'Checked in',
        'undo_title' => 'Check-in undone',
        'check_in_all_title' => 'All attendees checked in',
        'check_in_all_body' => ':count attendee(s) checked in',
        'refunded_title' => 'Booking refunded — do not admit',
        'refunded_body' => 'Booking :reference has been refunded and cannot be checked in.',
        'date_restricted_title' => 'Check-in blocked — wrong event date',
        'date_restricted_warning_title' => 'Event date mismatch',
        'date_restricted_body_future' => 'This booking is for :date, a future date. Check-in for this event is not open yet.',
        'date_restricted_body_past' => 'This booking was for :date, a past date. Check-in for this event has closed.',
        'date_restricted_override_note' => 'You may still check in this attendee — please confirm this is intentional.',
    ],
];
