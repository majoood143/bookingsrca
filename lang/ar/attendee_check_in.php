<?php

return [
    'navigation' => [
        'label' => 'مسح الحضور',
    ],

    'title' => 'تسجيل حضور المشاركين',

    'summary' => ':checked_in من :total تم تسجيل حضورهم',

    'search' => [
        'label' => 'امسح الرمز أو أدخله',
        'placeholder' => 'رقم الحجز، رقم التذكرة، أو رقم الهاتف',
    ],

    'disambiguation' => [
        'prompt' => 'يوجد أكثر من حجز مطابق لهذا الرقم. اختر أحدها:',
    ],

    'actions' => [
        'fullscreen_on' => 'ملء الشاشة',
        'fullscreen_off' => 'إنهاء ملء الشاشة',
        'check_in_all' => 'تسجيل حضور الجميع (:count)',
        'check_in_all_confirm' => 'هل تريد تسجيل حضور جميع الحاضرين المتبقين وعددهم :count؟',
        'scan_another' => 'مسح رمز آخر',
    ],

    'notifications' => [
        'not_found_title' => 'لم يتم العثور على تطابق',
        'not_found_body' => 'لا يوجد حجز أو تذكرة أو مشارك مطابق لهذا الرمز.',
        'checked_in_title' => 'تم تسجيل الحضور',
        'undo_title' => 'تم التراجع عن تسجيل الحضور',
        'check_in_all_title' => 'تم تسجيل حضور الجميع',
        'check_in_all_body' => 'تم تسجيل حضور :count مشارك',
    ],
];
