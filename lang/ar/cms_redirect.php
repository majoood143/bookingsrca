<?php

return [
    'navigation' => [
        'label' => 'إعادة توجيه',
        'plural' => 'إعادات التوجيه',
    ],
    'sections' => [
        'information' => 'المعلومات',
    ],
    'fields' => [
        'from_path' => 'من المسار',
        'from_path_helper' => 'المسار القديم، بدون شرطة مائلة في البداية، مثل old-page',
        'to_path' => 'إلى المسار',
        'status_code' => 'رمز الحالة',
    ],
    'status_codes' => [
        '301' => '301 — دائم',
        '302' => '302 — مؤقت',
    ],
    'columns' => [
        'from_path' => 'من',
        'to_path' => 'إلى',
        'status_code' => 'الحالة',
        'hits' => 'الزيارات',
    ],
    'actions' => [
        'create_first' => 'إنشاء أول إعادة توجيه',
    ],
    'empty_state' => [
        'heading' => 'لا توجد إعادات توجيه بعد',
        'description' => 'أضف إعادة توجيه عند تغيير رابط صفحة أو مقالة لتجنب الروابط المعطلة.',
    ],
];
