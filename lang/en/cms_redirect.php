<?php

return [
    'navigation' => [
        'label' => 'Redirect',
        'plural' => 'Redirects',
    ],
    'sections' => [
        'information' => 'Information',
    ],
    'fields' => [
        'from_path' => 'From path',
        'from_path_helper' => 'The old URL path, without a leading slash, e.g. old-page',
        'to_path' => 'To path',
        'status_code' => 'Status code',
    ],
    'status_codes' => [
        '301' => '301 — Permanent',
        '302' => '302 — Temporary',
    ],
    'columns' => [
        'from_path' => 'From',
        'to_path' => 'To',
        'status_code' => 'Status',
        'hits' => 'Hits',
    ],
    'actions' => [
        'create_first' => 'Create first redirect',
    ],
    'empty_state' => [
        'heading' => 'No redirects yet',
        'description' => 'Add a redirect when a page or post URL changes to avoid broken links.',
    ],
];
