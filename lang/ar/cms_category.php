<?php

return [
    'navigation' => [
        'label' => 'تصنيف',
        'plural' => 'التصنيفات',
    ],
    'sections' => [
        'information' => 'المعلومات',
    ],
    'fields' => [
        'name_en' => 'الاسم (إنجليزي)',
        'name_ar' => 'الاسم (عربي)',
        'slug' => 'الرابط المختصر',
        'description_en' => 'الوصف (إنجليزي)',
        'description_ar' => 'الوصف (عربي)',
        'parent' => 'التصنيف الأب',
        'order' => 'الترتيب',
    ],
    'columns' => [
        'name' => 'الاسم',
        'parent' => 'الأب',
        'posts_count' => 'المقالات',
        'order' => 'الترتيب',
    ],
    'actions' => [
        'create_first' => 'إنشاء أول تصنيف',
    ],
    'empty_state' => [
        'heading' => 'لا توجد تصنيفات بعد',
        'description' => 'أنشئ تصنيفًا لتبدأ في تنظيم المقالات.',
    ],
    'notifications' => [
        'cannot_delete' => 'لا يمكن حذف التصنيف',
        'has_posts' => 'لا يزال هذا التصنيف يحتوي على مقالات أو تصنيفات فرعية مرتبطة به.',
    ],
];
