<?php

return [

    'navigation' => [
        'group'  => 'Booking Management',
        'label'  => 'Payment Gateway Log',
        'plural' => 'Payment Gateway Logs',
    ],

    'columns' => [
        'created_at'       => 'Date',
        'booking'          => 'Booking',
        'gateway'          => 'Gateway',
        'event'            => 'Event',
        'result'           => 'Result',
        'status_code'      => 'HTTP Status',
        'response_preview' => 'Response Preview',
    ],

    'filters' => [
        'gateway'                    => 'Gateway',
        'event'                      => 'Event',
        'result'                     => 'Result',
        'status_code'                => 'HTTP Status',
        'date'                       => 'Date',
        'from'                       => 'From',
        'to'                         => 'To',
        'payload_search'             => 'Search in payloads',
        'payload_search_placeholder' => 'e.g. order id, tracking id, error message...',
    ],

    'events' => [
        'create_session'   => 'Create Session',
        'get_session'       => 'Get Session',
        'initiate_payment' => 'Initiate Payment',
        'callback'          => 'Callback',
        'webhook'           => 'Webhook',
    ],

    'outcomes' => [
        'success' => 'Success',
        'failed'  => 'Failed',
        'pending' => 'Pending',
        'error'   => 'Error',
        'unknown' => 'Unknown',
    ],

    'status_ranges' => [
        '2xx' => 'Success (2xx)',
        '4xx' => 'Client Error (4xx)',
        '5xx' => 'Server Error (5xx)',
        'none' => 'No HTTP Status',
    ],

    'tabs' => [
        'all'     => 'All',
        'success' => 'Success',
        'failed'  => 'Failed',
        'error'   => 'Error',
    ],

    'sections' => [
        'overview' => 'Overview',
        'payloads' => 'Request & Response Payloads',
    ],

    'gateway_logs' => [
        'request'  => 'Request',
        'response' => 'Response',
        'empty'    => 'No payment gateway transactions recorded.',
    ],

    'widgets' => [
        'total_last_7_days' => 'Transactions (7 days)',
        'total_desc'        => 'Payment gateway calls logged in the last 7 days',
        'success_rate'      => 'Success Rate',
        'success_desc'      => ':count successful',
        'failed'            => 'Failed',
        'failed_desc'       => 'Attempts that did not result in payment',
        'errors'            => 'Errors',
        'errors_desc'       => ':count today',
    ],

    'empty_state' => [
        'heading'     => 'No payment gateway logs yet',
        'description' => 'Transactions with Thawani, NBO, and CCAvenue will appear here as they occur.',
    ],

];
