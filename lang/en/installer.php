<?php

return [

    'title' => 'Setup Wizard',
    'subtitle' => 'Let\'s get your booking system up and running.',

    'steps' => [
        'Requirements',
        'Database',
        'App Settings',
        'Install',
        'General Settings',
        'Admin Account',
        'Finish',
    ],

    'actions' => [
        'continue' => 'Continue',
        'test_connection' => 'Test Connection',
        'testing' => 'Testing…',
        'run' => 'Run Migrations',
        'rerun' => 'Run Again',
        'create_admin' => 'Create Admin Account',
        'finish' => 'Go to Dashboard',
    ],

    'requirements' => [
        'title' => 'Server Requirements',
        'php_version' => 'PHP :version or higher',
        'extension' => 'PHP extension: :name',
        'writable' => 'Writable: :path',
        'env_writable' => 'Writable: .env',
        'failed_hint' => 'Some requirements are not met. Fix the items marked with ✗ on your server, then continue.',
    ],

    'database' => [
        'title' => 'Database Connection',
        'fields' => [
            'host' => 'Host',
            'port' => 'Port',
            'database' => 'Database Name',
            'username' => 'Username',
            'password' => 'Password',
        ],
        'invalid_name' => 'Database name may only contain letters, numbers, and underscores.',
        'access_denied' => 'Access denied. Please check your username and password.',
        'unknown_host' => 'Could not reach the database host. Please check the host and port.',
        'connection_failed' => 'Connection failed: :error',
        'connected' => 'Connection successful.',
        'created' => 'Connection successful — the database was created.',
    ],

    'app_settings' => [
        'title' => 'Application Settings',
        'fields' => [
            'app_name' => 'Application Name',
            'app_url' => 'Application URL',
        ],
    ],

    'migrate' => [
        'title' => 'Install Database Tables',
        'description' => 'This will create the database tables and permissions required to run the application.',
        'running' => 'Running…',
        'success' => 'Database tables installed successfully.',
        'failed' => 'Migration failed: :error',
        'already_done' => 'Database tables are already installed.',
    ],

    'settings' => [
        'title' => 'General Settings',
        'description' => 'Set the essentials for this environment — branding, currency, and identity. Everything here can be changed later from Settings → General.',
        'fields' => [
            'site_name_en' => 'Site Name (English)',
            'site_name_ar' => 'Site Name (Arabic)',
            'currency_code' => 'Currency',
            'currency_symbol' => 'Currency Symbol',
            'primary_color' => 'Brand Color',
            'primary_color_helper' => 'Used across the public booking pages.',
            'panel_primary_color' => 'Admin Panel Color',
            'panel_primary_color_helper' => 'Used for the admin dashboard theme.',
            'site_logo' => 'Site Logo',
            'site_logo_helper' => 'Shown on the public booking pages and in emails. Optional.',
            'app_logo' => 'Admin Panel Logo',
            'app_logo_helper' => 'Shown in the admin dashboard sidebar. Optional.',
        ],
    ],

    'admin' => [
        'title' => 'Create Admin Account',
        'already_exists' => 'An admin account already exists (:email).',
        'fields' => [
            'name' => 'Name',
            'email' => 'Email',
            'password' => 'Password',
            'password_confirmation' => 'Confirm Password',
        ],
    ],

    'finish' => [
        'title' => 'Setup Complete',
        'description' => 'Your booking system is ready. Click below to sign in to the admin dashboard.',
        'seed_demo_data' => 'Seed demo data',
        'seed_demo_data_helper' => 'Adds one example event with dates, ticket types, and extra services so you can explore the system before going live. Safe to skip for a production launch.',
        'finishing' => 'Finishing…',
        'cache_note' => 'If you run "php artisan optimize" or "route:cache" as part of your deployment, run it after finishing setup — running it earlier can cache settings from before the wizard wrote your final configuration.',
    ],

];
