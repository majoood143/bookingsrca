<?php

return [

    'navigation' => [
        'group' => 'Booking Management',
        'label' => 'Attendee',
        'plural' => 'Attendees',
    ],

    'sections' => [
        'attendee_info'   => 'Attendee Information',
        'booking_info'    => 'Booking Information',
        'ticket_info'     => 'Ticket Information',
        'extra_services'  => 'Extra Services',
    ],

    'fields' => [
        'full_name'         => 'Full Name',
        'first_name'        => 'First Name',
        'last_name'         => 'Last Name',
        'email'             => 'Email',
        'phone'             => 'Phone',
        'date_of_birth'     => 'Date of Birth',
        'gender'            => 'Gender',
        'nationality'       => 'Nationality',
        'identity_number'   => 'Identity Number',
        'passport_number'   => 'Passport Number',
        'identity_card_upload' => 'Identity Card Uploaded',
        'passport_upload'   => 'Passport Uploaded',
        'ticket_number'     => 'Ticket Number',
        'ticket_type'       => 'Ticket Type',
        'ticket_price'      => 'Ticket Price',
        'email_sent'        => 'Email Sent',
        'email_sent_at'     => 'Email Sent At',
        'checked_in'        => 'Checked In',
        'checked_in_at'     => 'Checked In At',
        'booking_reference' => 'Booking Reference',
        'event'             => 'Event',
        'event_date'        => 'Event Date',
        'time_slot'         => 'Time Slot',
        'booking_status'    => 'Booking Status',
        'service_name'      => 'Service',
        'service_quantity'  => 'Qty',
        'service_price'     => 'Price',
        'qr_code'           => 'QR Code',
        'created_at'        => 'Registered At',
    ],

    'columns' => [
        'name'              => 'Name',
        'email'             => 'Email',
        'ticket_number'     => 'Ticket #',
        'event'             => 'Event',
        'event_date'        => 'Date',
        'time'              => 'Time',
        'ticket_type'       => 'Ticket Type',
        'email_sent'        => 'Email',
        'checked_in'        => 'Checked In',
        'booking_reference' => 'Booking Ref',
        'booking_status'    => 'Booking Status',
        'created_at'        => 'Registered At',
        'phone'             => 'Phone',
    ],

    'actions' => [
        'resend_ticket'     => 'Resend Ticket',
        'download_ticket'   => 'Download Ticket',
        'print_ticket'      => 'Print Ticket',
        'download_identity_card' => 'Download Identity Card',
        'download_passport'      => 'Download Passport',
        'check_in'          => 'Check In',
        'undo_check_in'     => 'Undo Check-In',
        'view_booking'      => 'View Booking',
    ],

    'tabs' => [
        'confirmed'     => 'Confirmed',
        'cancelled'     => 'Cancelled',
        'all'           => 'All Attendees',
        'checked_in'    => 'Checked In',
        'not_checked_in' => 'Not Checked In',
        'email_sent'    => 'Email Sent',
        'email_pending' => 'Email Pending',
    ],

    'filters' => [
        'event'          => 'Event',
        'booking_status' => 'Booking Status',
        'checked_in'     => 'Check-In Status',
        'email_sent'     => 'Email Status',
        'event_date'     => 'Event Date',
        'time_slot'      => 'Time Slot',
        'ticket_type'    => 'Ticket Type',
        'date_from'      => 'Date From',
        'date_until'     => 'Date Until',
    ],

    'notifications' => [
        'ticket_resent'        => 'Ticket Resent',
        'ticket_resent_body'   => 'The ticket has been resent to :email',
        'ticket_resend_failed' => 'Failed to Resend',
        'ticket_resend_failed_body' => 'Could not resend the ticket. Please check the email address.',
        'checked_in'           => 'Attendee Checked In',
        'checked_in_body'      => ':name has been successfully checked in.',
        'check_in_undone'      => 'Check-In Undone',
        'check_in_undone_body' => ':name check-in has been reversed.',
    ],

    'modals' => [
        'resend_heading'     => 'Resend Ticket',
        'resend_description' => 'This will resend the ticket email to :email. Continue?',
        'resend_submit'      => 'Yes, Resend',
        'check_in_heading'   => 'Check In Attendee',
        'check_in_description' => 'Mark :name as checked in?',
        'check_in_submit'    => 'Check In',
    ],

    'tooltips' => [
        'resend_ticket'   => 'Resend ticket email to this attendee',
        'download_ticket' => 'Download the PDF ticket',
        'download_identity_card' => 'Download the uploaded identity card scan',
        'download_passport'      => 'Download the uploaded passport scan',
        'check_in'        => 'Mark attendee as checked in',
    ],

    'no_extra_services'   => 'No extra services were booked.',
    'no_qr_code'          => 'QR code not yet generated.',
    'no_pdf'              => 'PDF ticket not yet generated.',

];
