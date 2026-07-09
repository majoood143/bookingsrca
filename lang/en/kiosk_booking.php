<?php

return [

    'inactive' => [
        'heading' => 'This kiosk is currently unavailable',
        'body'    => 'Please see a member of staff at the counter for assistance.',
    ],

    'choose_event' => [
        'heading'    => 'Welcome',
        'subheading' => 'Please choose what you would like to book',
        'no_events'  => 'No events are currently available for booking.',
    ],

    'idle' => [
        'resuming_in'   => 'Restarting in :seconds...',
        'still_there'   => 'Still there?',
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

        'counter_title'    => 'Pay at Counter',
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
    ],

    'wallet' => [
        'card_not_found'       => 'Card not recognised. Please try again or pay at the counter.',
        'card_blocked'         => 'This card is blocked. Please see a member of staff.',
        'insufficient_balance' => 'Insufficient balance on this card. Please top up or pay at the counter.',
    ],
];
