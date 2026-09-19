<?php

return [
    'navigation' => [
        'label' => 'Translation',
        'plural' => 'Translations',
    ],
    'sections' => [
        'key' => 'Translation key',
        'text' => 'Text',
    ],
    'fields' => [
        'catalog_key' => 'Pick an existing translation',
        'catalog_key_helper' => 'Search by key or by text. Choosing one fills in the current text below. Or leave empty and type a key yourself.',
        'group' => 'Group',
        'group_helper' => 'The lang file name, e.g. home. Use * for plain-sentence translations.',
        'key' => 'Key',
        'text_en' => 'English',
        'text_ar' => 'Arabic',
        'default' => 'Default: :text',
    ],
    'columns' => [
        'group' => 'Group',
        'key' => 'Key',
        'text_en' => 'English',
        'text_ar' => 'Arabic',
        'updated_at' => 'Updated',
    ],
    'actions' => [
        'reset' => 'Reset to default',
        'reset_heading' => 'Reset to default?',
        'reset_description' => 'The saved text is removed and the built-in translation is used again.',
        'reset_done' => 'Translation reset to default',
    ],
    'empty_state' => [
        'heading' => 'No custom translations yet',
        'description' => 'Everything currently uses the built-in text. Create one to override any key.',
    ],
];
