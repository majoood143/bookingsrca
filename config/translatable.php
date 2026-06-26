<?php

return [
    'locales' => [
        'en',
        'ar',
    ],
    'locale_separator' => '-',
    'default_locale' => 'en',
    'fallback_locale' => 'en',
    'locale_key' => 'locale',
    'always_load_translations' => false,
    'load_translations_when_missing' => false,
    'to_array_always_loads_translations' => true,
];

// config/filament.php (Add to existing config)
/*
'default_filesystem_disk' => env('FILAMENT_FILESYSTEM_DISK', 'public'),
'path' => env('FILAMENT_PATH', 'admin'),
'domain' => env('FILAMENT_DOMAIN'),
*/

// ===== LANGUAGE FILES =====

// resources/lang/en/validation.php (Add custom messages)
/*
'custom' => [
    'quantity' => [
        'min' => 'You must select at least 1 ticket.',
        'max' => 'Maximum 10 tickets allowed per booking.',
    ],
],
*/

// resources/lang/ar/validation.php
/*
'required' => 'حقل :attribute مطلوب.',
'email' => 'يجب أن يكون :attribute عنوان بريد إلكتروني صحيح.',
'max' => [
    'string' => 'يجب ألا يزيد :attribute عن :max حرف.',
],
'custom' => [
    'quantity' => [
        'min' => 'يجب اختيار تذكرة واحدة على الأقل.',
        'max' => 'الحد الأقصى 10 تذاكر لكل حجز.',
    ],
],
*/