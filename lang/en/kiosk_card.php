<?php

return [

    'navigation' => [
        'group'  => 'Kiosk Management',
        'label'  => 'Wallet Card',
        'plural' => 'Wallet Cards',
    ],

    'sections' => [
        'information'      => 'Card Information',
        'information_desc' => 'The card UID is read once by tapping it on the reader, or entered manually.',
        'balance'           => 'Balance',
        'balance_desc'      => 'Starting balance for a newly issued card. Use the Top Up action to add funds later.',
    ],

    'fields' => [
        'uid'          => 'Card UID',
        'uid_helper'   => 'The unique ID printed by the ACR122U when the card is tapped.',
        'holder_name'  => 'Holder Name',
        'phone'        => 'Phone',
        'balance'      => 'Balance',
        'status'       => 'Status',
        'notes'        => 'Notes',
    ],

    'statuses' => [
        'active'  => 'Active',
        'blocked' => 'Blocked',
    ],

    'columns' => [
        'uid'                => 'UID',
        'holder_name'        => 'Holder',
        'phone'              => 'Phone',
        'balance'            => 'Balance',
        'status'             => 'Status',
        'transactions_count' => 'Transactions',
        'created_at'         => 'Issued',
    ],

    'actions' => [
        'new'    => 'New Card',
        'top_up' => 'Top Up',
        'block'  => 'Block',
        'unblock' => 'Unblock',
    ],

    'top_up_modal' => [
        'heading'     => 'Top Up Card',
        'description' => 'Adds funds to this card\'s balance and logs the transaction.',
        'amount'      => 'Amount to add',
        'reference'   => 'Reference',
        'notes'       => 'Notes',
    ],

    'notifications' => [
        'created'        => 'Card registered.',
        'updated'        => 'Card updated.',
        'topped_up'      => 'Card topped up.',
        'status_updated' => 'Card status updated.',
        'card_blocked'   => 'Card blocked.',
        'card_unblocked' => 'Card unblocked.',
        'insufficient_for_block' => 'Cannot top up a blocked card. Unblock it first.',
    ],

    'transactions' => [
        'title' => 'Transaction Ledger',
        'fields' => [
            'date'          => 'Date',
            'type'          => 'Type',
            'amount'        => 'Amount',
            'balance_after' => 'Balance After',
            'kiosk'         => 'Kiosk',
            'booking'       => 'Booking',
            'recorded_by'   => 'Recorded By',
            'reference'     => 'Reference',
            'notes'         => 'Notes',
        ],
        'types' => [
            'topup'      => 'Top Up',
            'payment'    => 'Payment',
            'refund'     => 'Refund',
            'adjustment' => 'Adjustment',
        ],
        'self_service' => 'Self-service (kiosk)',
        'total' => 'Net Total',
    ],

    'empty_state' => [
        'heading'     => 'No wallet cards yet',
        'description' => 'Register a card to start topping up balances for customers.',
    ],
];
