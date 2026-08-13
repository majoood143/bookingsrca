<?php

return [

    'title' => 'Payment Gateways',
    'save' => 'Save Settings',

    'sections' => [
        'active_gateway' => 'Active Gateway',
        'active_gateway_desc' => 'Choose which payment gateway is used during checkout.',
        'thawani' => 'Thawani Settings',
        'thawani_desc' => 'Credentials for the Thawani payment gateway.',
        'nbo' => 'NBO Settings',
        'nbo_desc' => 'Credentials for the NBO Unified Checkout payment gateway.',
        'ccavenue' => 'CCAvenue Settings',
        'ccavenue_desc' => 'Credentials for the CCAvenue (Bank Muscat MCPG) payment gateway.',
    ],

    'options' => [
        'active_gateway' => 'Active Payment Gateway',
        'free' => 'Free (no payment required)',
        'cash' => 'Cash / Pay at Door',
        'thawani' => 'Thawani',
        'nbo' => 'NBO Unified Checkout',
        'ccavenue' => 'CCAvenue (Bank Muscat)',
    ],

    'fields' => [
        'test_mode' => 'Test Mode',
        'thawani_test_mode_helper' => 'When enabled, requests go to uatcheckout.thawani.om instead of the live endpoint.',
        'secret_key' => 'Secret Key',
        'publishable_key' => 'Publishable Key',
        'base_url_override' => 'Base URL Override',
        'url_override_helper' => 'Leave blank to use the default URL based on test-mode setting.',
        'webhook_secret' => 'Webhook Secret',
        'webhook_secret_helper' => 'Optional HMAC-SHA256 secret used to verify incoming Thawani webhook events.',

        'nbo_test_mode_helper' => 'When enabled, requests go to the NBO sandbox endpoint instead of the live endpoint.',
        'tranportal_id' => 'Tranportal ID',
        'tranportal_password' => 'Tranportal Password',
        'resource_key' => 'Resource Key',
        'resource_key_helper' => 'AES encryption key provided by NBO. Must be exactly 16 characters.',
        'endpoint_url_override' => 'Endpoint URL Override',

        'ccavenue_test_mode_helper' => 'When enabled, requests go to the CCAvenue sandbox endpoint instead of the live endpoint.',
        'merchant_id' => 'Merchant ID',
        'access_code' => 'Access Code',
        'working_key' => 'Working Key',
        'working_key_helper' => 'AES-256 encryption key provided by Bank Muscat/CCAvenue.',
        'ccavenue_endpoint_url_helper' => 'Leave blank to use the placeholder default; must be overridden with the real Bank Muscat/CCAvenue transaction URL before going live.',
    ],

    'notifications' => [
        'saved' => 'Payment gateway settings saved.',
    ],

];
