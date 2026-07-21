<?php

return [

    'navigation' => [
        'group' => 'إدارة الحجوزات',
        'label' => 'فئة مصروف',
        'plural' => 'فئات المصروفات',
    ],

    'sections' => [
        'information' => 'معلومات الفئة',
    ],

    'fields' => [
        'name_en' => 'الاسم بالإنجليزية',
        'name_ar' => 'الاسم بالعربية',
        'description_en' => 'الوصف بالإنجليزية',
        'description_ar' => 'الوصف بالعربية',
        'color' => 'اللون',
        'icon' => 'الأيقونة',
        'order' => 'ترتيب العرض',
        'is_active' => 'نشط',
    ],

    'columns' => [
        'name' => 'الاسم',
        'color' => 'اللون',
        'expenses_count' => 'المصروفات',
        'is_active' => 'نشط',
        'order' => 'الترتيب',
    ],

    'filters' => [
        'is_active' => 'نشط',
    ],

    'actions' => [
        'create_first' => 'إنشاء أول فئة',
    ],

    'notifications' => [
        'cannot_delete' => 'لا يمكن الحذف',
        'has_expenses' => 'هذه الفئة مرتبطة بمصروفات.',
    ],

    'empty_state' => [
        'heading' => 'لا توجد فئات مصروفات بعد',
        'description' => 'أنشئ فئات لتصنيف مصروفاتك (خدمات، تسويق، صيانة، إلخ)',
    ],
];
