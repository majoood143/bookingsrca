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

    // ── Step 1 — Select Date ──────────────────────────────────────────────────
    'step1' => [
        'heading'       => 'Select a Date',
        'subheading'    => 'Choose your preferred event date',
        'no_dates'      => 'No available dates',
        'no_dates_body' => 'There are no upcoming dates for this event.',
    ],

    // ── Step 2 — Choose Time Slot ─────────────────────────────────────────────
    'step2' => [
        'heading'          => 'Choose a Time Slot',
        'subheading'       => 'Available slots for',
        'full'             => 'Full',
        'almost_full'      => 'Almost Full',
        'available'        => 'Available',
        'spots_remaining'  => 'spots remaining',
    ],

    // ── Step 3 — Tickets ──────────────────────────────────────────────────────
    'step3' => [
        'heading'          => 'Pick Your Tickets',
        'subheading'       => 'Select ticket types and how many of each you need',
        'n_selected'       => ':n selected',
        'sold_out'         => 'Sold Out',
        'tickets_available' => 'tickets available',
        'per_ticket'       => 'per ticket',
        'subtotal'         => 'subtotal',
        'running_total'    => 'Running Total',
        'n_tickets'        => '(:n ticket(s))',
        'min_tickets'      => 'Select at least :n ticket(s) to continue',
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
        'date_of_birth'        => 'Date of Birth',
        'date_of_birth_max_age' => 'Attendee age cannot exceed :age years.',
        'date_of_birth_future' => 'Date of birth cannot be in the future.',
        'gender'               => 'Gender',
        'select_gender'        => 'Select gender',
        'male'                 => 'Male',
        'female'               => 'Female',
        'nationality'          => 'Nationality',
        'nationality_no_results' => 'No countries found',
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
        'confirm_booking'        => 'Confirm Booking',
        'processing'             => 'Processing...',
        'order_summary'          => 'Order Summary',
        'secured_by_thawani'     => 'Secured by Thawani',
        'secured_by_nbo'         => 'Secured by NBO',
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

];
