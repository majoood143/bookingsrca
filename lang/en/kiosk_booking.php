<?php

return [

    'inactive' => [
        'heading' => 'This kiosk is currently unavailable',
        'body'    => 'Please see a member of staff at the counter for assistance.',
    ],

    // The welcome screen's two buttons are a fixed bilingual pair — each one
    // is always shown in its own language regardless of the kiosk's current
    // locale, so both en/ar files intentionally carry identical values here.
    'welcome' => [
        'en_cta'    => 'Click to Start Booking',
        'en_sub'    => 'Touch the screen to begin',
        'ar_cta'    => 'اضغط هنا لبدء الحجز',
        'ar_sub'    => 'المس الشاشة للبدء',
        'kiosk_tag' => 'Self-Service Kiosk',
    ],

    'choose_event' => [
        'heading'    => 'Welcome',
        'subheading' => 'Please choose what you would like to book',
        'no_events'  => 'No events are currently available for booking.',
    ],

    'today' => [
        'badge'            => "Today, :date",
        'no_slots_heading' => 'No time slots left for today',
        'no_slots_body'    => "All of today's time slots have passed or are fully booked. Please see a member of staff at the counter for help.",
        'back_to_events'   => 'Choose a Different Event',
    ],

    'progress' => [
        'time'    => 'Time',
        'tickets' => 'Tickets',
        'extras'  => 'Extras',
        'details' => 'Details',
        'payment' => 'Payment',
        'done'    => 'Done',
    ],

    'idle' => [
        'resuming_in'   => 'Restarting in :seconds...',
        'still_there'   => 'Still there?',
        'still_there_body' => "We'll restart this screen soon to make way for the next guest.",
        'continue'      => 'Continue',
    ],

    'step6' => [
        'heading'    => 'How would you like to pay?',
        'subheading' => 'Choose an option below to complete your booking.',

        'wallet_title'    => 'Tap Card',
        'wallet_subtitle' => 'Pay instantly with your prepaid card',
        'wallet_waiting'  => 'Please tap your card on the reader now',
        'wallet_cancel'   => 'Cancel',
        'wallet_unavailable' => 'Card reader is currently unavailable',

        'counter_title'    => 'Pay at the Counter to Confirm',
        'counter_subtitle' => 'Get a reference and pay a member of staff',
        'counter_button'   => 'Get My Reference',

        'processing' => 'Processing...',
    ],

    'confirmation' => [
        'paid_heading'    => 'Booking Confirmed!',
        'paid_body'       => 'Your tickets have been sent to your email.',
        'pending_heading' => 'Almost Done!',
        'pending_body'    => 'Please bring this reference to the counter to complete your payment.',
        'reference'       => 'Booking Reference',
        'amount_due'      => 'Amount Due',
        'new_booking'     => 'Start New Booking',
        'redirect_message' => 'Thank you! Please proceed to the reception counter.',
        'redirect_now'    => 'Start New Booking Now',
    ],

    'wallet' => [
        'card_not_found'       => 'Card not recognised. Please try again or pay at the counter.',
        'card_blocked'         => 'This card is blocked. Please see a member of staff.',
        'insufficient_balance' => 'Insufficient balance on this card. Please top up or pay at the counter.',
    ],
];
