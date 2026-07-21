<?php

return [

    // ── Language switcher ─────────────────────────────────────────────────────
    'switch_to_arabic' => 'العربية',
    'switch_to_english' => 'English',

    // ── Progress stepper ──────────────────────────────────────────────────────
    'steps' => [
        'date'    => 'Date',
        'time'    => 'Time',
        'tickets' => 'Tickets',
        'extras'  => 'Extras',
        'details' => 'Details',
        'payment' => 'Payment',
    ],

    // ── Common actions ────────────────────────────────────────────────────────
    'back'     => 'Back',
    'continue' => 'Continue',

    // ── Unavailable / maintenance mode ────────────────────────────────────────
    'unavailable' => [
        'heading'           => 'Under Maintenance',
        'draft_message'     => 'This event is being prepared and is not open for booking yet. Please check back soon.',
        'cancelled_message' => 'This event has been cancelled and is no longer available for booking.',
        'back_home'         => 'Back to Home',
    ],

    // ── Private / password-protected event ──────────────────────────────────
    'private' => [
        'heading'          => 'This Event is Private',
        'message'          => 'Enter the access password to view this event.',
        'password_label'   => 'Password',
        'password_placeholder' => 'Enter password',
        'submit'           => 'View Event',
        'incorrect'        => 'Incorrect password. Please try again.',
    ],

    // ── Event details panel (timeline / FAQ / promo video) ───────────────────
    'details' => [
        'timeline'    => 'Event Timeline',
        'faq'         => 'Frequently Asked Questions',
        'promo_video' => 'Promotional Video',
    ],

    // ── Step 1 — Select Date ──────────────────────────────────────────────────
    'step1' => [
        'heading'         => 'Select a Date',
        'subheading'      => 'Choose your preferred event date',
        'no_dates'        => 'No available dates',
        'no_dates_body'   => 'There are no upcoming dates for this event.',
        'sold_out'        => 'Tickets Sold Out',
        'sold_out_error'  => 'All tickets for that date are sold out. Please choose another date.',
    ],

    // ── Step 2 — Choose Time Slot ─────────────────────────────────────────────
    'step2' => [
        'heading'          => 'Choose a Time Slot',
        'subheading'       => 'Available slots for',
        'full'             => 'Full',
        'almost_full'      => 'Almost Full',
        'available'        => 'Available',
        'spots_remaining'  => 'spots remaining',
        'booking_closed'   => 'Sorry, bookings for this event closed :time.',
    ],

    // ── Step 3 — Tickets ──────────────────────────────────────────────────────
    'step3' => [
        'heading'          => 'Pick Your Tickets',
        'subheading'       => 'Select ticket types and how many of each you need',
        'n_selected'       => ':n selected',
        'sold_out'         => 'Sold Out',
        'tickets_available' => 'tickets available',
        'per_ticket'       => 'per ticket',
        'free'             => 'Free',
        'subtotal'         => 'subtotal',
        'running_total'    => 'Running Total',
        'n_tickets'        => '(:n ticket(s))',
        'min_tickets'         => 'Select at least :n ticket(s) to continue',
        'dependency_required' => ':child requires :parent — please add a :parent ticket first.',
        'requires_parent'     => 'Requires :parent',
        'add_parent_first'    => 'Add :parent first',
        'slot_limit_reached'  => 'This time slot has no more remaining capacity for additional tickets.',
    ],

    // ── Step 4 — Extra Services ───────────────────────────────────────────────
    'step4' => [
        'heading'    => 'Add Extra Services',
        'subheading' => 'Enhance your experience with optional add-ons',
        'no_extras'  => 'No extra services available for this event.',
        'available'  => 'available',
        'per_ticket' => 'per ticket',
    ],

    // ── Step 5 — Attendee Details ─────────────────────────────────────────────
    'step5' => [
        'heading'              => 'Attendee Details',
        'subheading'           => 'Fill in the details for each ticket holder',
        'copy_contact'         => "Apply first attendee's contact details to all others",
        'copy_contact_hint'    => 'will be copied from Attendee 1',
        'attendee_n'           => 'Attendee :n',
        'primary'              => 'primary',
        'first_name'           => 'First Name',
        'first_name_placeholder' => 'John',
        'last_name'            => 'Last Name',
        'last_name_placeholder' => 'Doe',
        'email_address'        => 'Email Address',
        'email_hint'           => 'Confirmation ticket will be sent here',
        'phone_number'         => 'Phone Number',
        'phone_invalid'        => 'Phone number must be 7 to 15 digits, optionally starting with +.',
        'date_of_birth'        => 'Date of Birth',
        'date_of_birth_invalid' => 'Please enter a valid date of birth.',
        'date_of_birth_max_age' => 'Attendee age cannot exceed :age years.',
        'date_of_birth_future' => 'Date of birth cannot be in the future.',
        'gender'               => 'Gender',
        'select_gender'        => 'Select gender',
        'male'                 => 'Male',
        'female'               => 'Female',
        'nationality'          => 'Nationality',
        'nationality_no_results' => 'No countries found',
        'identity_number'      => 'Identity Number',
        'passport_number'      => 'Passport Number',
        'identity_card_upload' => 'Identity Card Upload',
        'passport_upload'      => 'Passport Upload',
        'uploading'            => 'Uploading...',
        'file_selected'        => 'Selected: :name',
        'terms_heading'        => 'Terms & Conditions',
        'terms_agree'          => 'I have read and agree to the',
        'terms_required'       => 'You must agree to the terms and conditions to proceed.',
        'continue_to_payment'  => 'Continue to Payment',
        'validating'           => 'Validating...',
        'booking_summary'      => 'Booking Summary',
        'email_label'          => 'Email',
        'phone_label'          => 'phone',
    ],

    // ── Step 6 — Payment ──────────────────────────────────────────────────────
    'step6' => [
        'heading'                => 'Payment',
        'subheading'             => 'Review your order and complete payment',
        'thawani_title'          => 'Thawani',
        'thawani_subtitle'       => 'Secure online payment powered by Thawani',
        'thawani_redirect_note'  => 'You will be redirected to the Thawani secure payment page to complete your purchase.',
        'pay_at_door_title'      => 'Pay at Door',
        'pay_at_door_subtitle'   => 'Pay with cash when you arrive at the event',
        'free_title'             => 'Free Entry',
        'free_subtitle'          => 'No payment required for this event',
        'pay_thawani_btn'        => 'Pay with Thawani',
        'redirecting_thawani'    => 'Redirecting to Thawani...',
        'nbo_title'              => 'NBO Unified Checkout',
        'nbo_subtitle'           => 'Secure online payment powered by National Bank of Oman',
        'nbo_redirect_note'      => 'You will be redirected to the NBO secure payment page to complete your purchase.',
        'pay_nbo_btn'            => 'Pay Now',
        'redirecting_nbo'        => 'Redirecting to NBO...',
        'ccavenue_title'         => 'CCAvenue (Bank Muscat)',
        'ccavenue_subtitle'      => 'Secure online payment powered by Bank Muscat / CCAvenue',
        'ccavenue_redirect_note' => 'You will be redirected to the CCAvenue secure payment page to complete your purchase.',
        'pay_ccavenue_btn'       => 'Pay Now',
        'redirecting_ccavenue'   => 'Redirecting to CCAvenue...',
        'confirm_booking'        => 'Confirm Booking',
        'processing'             => 'Processing...',
        'order_summary'          => 'Order Summary',
        'secured_by_thawani'     => 'Secured by Thawani',
        'secured_by_nbo'         => 'Secured by NBO',
        'secured_by_ccavenue'    => 'Secured by CCAvenue',
    ],

    // ── Shared summary sidebar ────────────────────────────────────────────────
    'summary' => [
        'event'          => 'Event',
        'date'           => 'Date',
        'time'           => 'Time',
        'tickets'        => 'Tickets',
        'extra_services' => 'Extra Services',
        'attendees'      => 'Attendees',
        'total'          => 'Total',
    ],

    // ── Success page ──────────────────────────────────────────────────────────
    'success' => [
        'title'                 => 'Booking Confirmed',
        'heading'               => 'Booking Confirmed!',
        'subheading'            => 'Your spot is reserved',
        'booking_reference'     => 'Booking Reference',
        'event'                 => 'Event',
        'date'                  => 'Date',
        'time'                  => 'Time',
        'ticket_type'           => 'Ticket Type',
        'quantity'              => 'Quantity',
        'ticket_unit'           => 'ticket(s)',
        'total_paid'            => 'Total Paid',
        'confirmation_email'    => 'A confirmation email has been sent to',
        'present_qr'            => 'Present this QR code at the entrance',
        'print_ticket'          => 'Print Ticket',
        'book_again'            => 'Book Again',
        'back_to_events'        => 'Back to Events',
    ],

    // ── Booking confirmation email ────────────────────────────────────────────
    'email' => [
        'subject'         => 'Booking Confirmation - :reference',
        'confirmed'       => 'Booking Confirmed!',
        'reference'       => 'Reference: :reference',
        'dear'            => 'Dear :name,',
        'valued_customer' => 'Valued Customer',
        'details_below'   => 'Your booking has been confirmed. Please find the details below:',
        'event_details'   => 'Event Details',
        'event'           => 'Event',
        'location'        => 'Location',
        'date'            => 'Date',
        'time'            => 'Time',
        'ticket_type'     => 'Ticket Type',
        'quantity'        => 'Quantity',
        'extra_services'  => 'Extra Services',
        'total_amount'    => 'Total Amount',
        'qr_heading'      => 'Your Ticket QR Code',
        'qr_notice'       => 'Please present this QR code at the event entrance.',
        'support'         => 'If you have any questions, please contact our support team.',
        'thank_you'       => 'Thank you for booking with us!',

        // Individual ticket email
        'ticket_subject'       => 'Your Event Ticket - :ticket_number',
        'ticket_heading'       => 'Your Event Ticket',
        'ticket_hello'         => 'Hello :name!',
        'ticket_intro'         => 'Thank you for your booking! Your ticket is ready. Please find your ticket details below:',
        'ticket_number'        => 'Ticket Number',
        'ticket_attendee'      => 'Attendee',
        'ticket_extra_services' => 'Extra Services Included',
        'ticket_qr_heading'    => 'Your QR Code',
        'ticket_qr_important'  => 'Important',
        'ticket_qr_notice'     => 'Please present this QR code at the event entrance.',
        'ticket_attachments'   => 'Attachments',
        'ticket_pdf_label'     => 'PDF Ticket (ticket-:number.pdf)',
        'ticket_qr_label'      => 'QR Code (qr-code-:number.png)',
        'ticket_see_you'       => 'We look forward to seeing you at the event!',
        'ticket_reference'     => 'Booking Reference',
        'ticket_automated'     => 'This is an automated email. Please do not reply to this message.',
        'ticket_support'       => 'If you have any questions, please contact event support.',
        'ticket_all_rights'    => 'All rights reserved.',

        // Combined tickets email (all attendees, sent to the first attendee)
        'tickets_subject'      => 'Your Event Tickets - :reference',
        'tickets_heading'      => 'Your Event Tickets',
        'tickets_intro'        => 'Thank you for your booking! All :count ticket(s) for your booking are ready. Please find the details and attachments below:',
        'tickets_list_heading' => 'Tickets',
    ],

    // ── Individual ticket PDF ─────────────────────────────────────────────────
    'ticket' => [
        'event_ticket'      => 'Event Ticket',
        'scan_to_verify'    => 'Scan to verify',
        'attendee'          => 'Attendee',
        'name'              => 'Name',
        'email'             => 'Email',
        'phone'             => 'Phone',
        'ticket_number'     => 'Ticket Number',
        'ticket_type'       => 'Ticket Type',
        'event_details'     => 'Event Details',
        'date'              => 'Date',
        'time'              => 'Time',
        'location'          => 'Location',
        'organizer'         => 'Organizer',
        'extra_services'    => 'Extra Services',
        'quantity'          => 'Quantity',
        'entry_pass'        => 'Entry Pass',
        'present_qr'        => 'Present this QR code at the event entrance for check-in.',
        'present_barcode'   => 'Present this barcode at the event entrance for check-in.',
        'booking_reference' => 'Booking Reference',
        'booked_on'         => 'Booked on',
        'support_note'      => 'For questions, please contact event support.',
        'all_rights'        => 'All rights reserved.',
        'refunded'          => 'Refunded',
    ],

];
