<?php

return [
    'title' => 'إعدادات المحتوى',
    'save' => 'حفظ',
    'tabs' => [
        'general' => 'عام',
        'seo' => 'تحسين محركات البحث',
    ],
    'sections' => [
        'defaults' => 'الإعدادات الافتراضية للمحتوى',
        'defaults_desc' => 'تُستخدم كخيار احتياطي عندما لا تحدد الصفحة أو المقالة وصف الميتا أو الصورة الخاصة بها.',
        'seo' => 'تحسين محركات البحث والخلاصات',
    ],
    'fields' => [
        'default_meta_description' => 'وصف الميتا الافتراضي',
        'default_meta_description_helper' => 'يُستخدم عندما لا يكون للصفحة أو المقالة وصف ميتا خاص بها.',
        'default_og_image' => 'صورة Open Graph الافتراضية',
        'default_og_image_helper' => 'تُستخدم عندما لا يكون للصفحة أو المقالة صورة Open Graph خاصة بها.',
        'posts_per_page' => 'عدد المقالات في الصفحة',
        'sitemap_enabled' => 'تفعيل sitemap.xml',
        'sitemap_enabled_helper' => 'نشر خريطة موقع يتم إنشاؤها تلقائيًا تغطي الصفحات والمقالات المنشورة.',
        'rss_enabled' => 'تفعيل خلاصة RSS',
        'rss_enabled_helper' => 'نشر /blog/feed يحتوي على أحدث المقالات المنشورة.',
        'robots_block_all' => 'حظر جميع الزواحف (وضع التجربة)',
        'robots_block_all_helper' => 'فعّل هذا الخيار في بيئات التجربة لإبقاء محركات البحث بعيدة عبر robots.txt.',
    ],
    'notifications' => [
        'updated' => 'تم تحديث الإعدادات',
    ],
];
