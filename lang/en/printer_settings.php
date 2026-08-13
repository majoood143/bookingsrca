<?php

return [

    'title' => 'Printer Settings',
    'save' => 'Save Settings',

    'sections' => [
        'thermal_printer' => 'Thermal Printer',
        'thermal_printer_desc' => 'The app is hosted online and cannot reach a printer on your on-site network directly. Attendee tickets are rendered here and queued; an on-site print agent (see print-agent/README.md) polls this app and delivers them to the printer over your local network.',
        'print_agent' => 'On-Site Print Agent',
        'print_agent_desc' => 'This token authenticates the on-site agent script when it polls this app for print jobs. Copy it into the agent\'s config.ini.',
    ],

    'fields' => [
        'enabled' => 'Enable Thermal Printing',
        'enabled_helper' => 'When disabled, printing attendee tickets will show an error instead of queuing a job.',
        'paper_width' => 'Paper Width',
        'paper_width_384' => '58mm (384 dots)',
        'paper_width_576' => '80mm (576 dots)',
        'graphics_mode' => 'Use Raster Graphics Mode',
        'graphics_mode_helper' => 'Enable if your printer supports GS ( L raster graphics for higher quality output. Leave off to use the more widely compatible bit-image method.',
        'chrome_path' => 'Chrome/Chromium Executable Path (optional)',
        'chrome_path_helper' => 'Leave blank to let Puppeteer auto-detect a browser. Set this if ticket/receipt rendering fails with a "Could not find chrome-headless-shell" error — point it at an already-installed Chrome, e.g. "C:\Program Files\Google\Chrome\Application\chrome.exe" on Windows or "/usr/bin/google-chrome" on Linux.',
        'agent_token' => 'Agent Token',
        'agent_last_seen' => 'Agent Last Seen',
        'pending_jobs' => 'Pending / Failed Jobs',
    ],

    'messages' => [
        'agent_never_seen' => 'Never — the on-site agent has not polled this app yet.',
        'job_counts' => ':pending pending, :failed failed',
    ],

    'actions' => [
        'regenerate_token' => 'Regenerate Agent Token',
        'regenerate_token_confirm' => 'The current token will stop working immediately — update the on-site agent\'s config.ini with the new value right after.',
        'send_test_print' => 'Send Test Print',
    ],

    'notifications' => [
        'token_regenerated' => 'Agent token regenerated.',
        'test_print_queued' => 'Test print queued — it will print once the on-site agent picks it up.',
        'test_print_failed' => 'Test print failed.',
        'saved' => 'Printer settings saved.',
    ],

];
