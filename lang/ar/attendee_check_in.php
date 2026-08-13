<?php

return [
    'navigation' => [
        'label' => 'مسح الحضور',
    ],

    'title' => 'تسجيل حضور المشاركين',

    'summary' => ':checked_in من :total تم تسجيل حضورهم',

    'event_progress' => [
        'label' => 'تقدم تسجيل حضور الفعالية',
        'summary' => ':checked_in من :total تم تسجيل حضورهم',
        'complete' => 'تم تسجيل حضور جميع المشاركين',
    ],

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
        'refunded_title' => 'الحجز مسترد — لا يسمح بالدخول',
        'refunded_body' => 'تم استرداد الحجز :reference ولا يمكن تسجيل دخوله.',
        'date_restricted_title' => 'تم منع تسجيل الحضور — تاريخ الفعالية غير مطابق',
        'date_restricted_warning_title' => 'عدم تطابق تاريخ الفعالية',
        'date_restricted_body_future' => 'هذا الحجز مخصص لتاريخ :date، وهو تاريخ مستقبلي. تسجيل الحضور لهذه الفعالية لم يفتح بعد.',
        'date_restricted_body_past' => 'كان هذا الحجز لتاريخ :date، وهو تاريخ سابق. تسجيل الحضور لهذه الفعالية قد أُغلق.',
        'date_restricted_override_note' => 'لا يزال بإمكانك تسجيل حضور هذا المشارك — يُرجى التأكد من أن ذلك مقصود.',
    ],
];
