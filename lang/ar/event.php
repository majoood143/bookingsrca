<?php

return [

    'navigation' => [
        'group'  => 'إدارة الفعاليات',
        'label'  => 'فعالية',
        'plural' => 'الفعاليات',
    ],

    'sections' => [
        'information'       => 'معلومات الفعالية',
        'information_desc'  => 'أدخل التفاصيل الأساسية للفعالية بكلا اللغتين',
        'location_schedule' => 'الموقع والجدول الزمني',
        'location_desc'     => 'حدد موقع الفعالية والتواريخ',
        'recurring'         => 'إعدادات الفعالية المتكررة',
        'recurring_desc'    => 'تكوين ما إذا كانت الفعالية تتكرر في أيام محددة',
        'media_status'      => 'الوسائط والحالة',
        'media_desc'        => 'رفع صورة الفعالية وتحديد الحالة',
    ],

    'fields' => [
        'title_en'             => 'العنوان (إنجليزي)',
        'title_ar'             => 'العنوان (عربي)',
        'slug'                 => 'الرابط المختصر',
        'slug_helper'          => 'يُولَّد تلقائيًا من العنوان الإنجليزي، ويمكنك تخصيصه',
        'description_en'       => 'الوصف (إنجليزي)',
        'description_ar'       => 'الوصف (عربي)',
        'organizer_en'         => 'المنظِّم (إنجليزي)',
        'organizer_ar'         => 'المنظِّم (عربي)',
        'location_en'          => 'الموقع (إنجليزي)',
        'location_ar'          => 'الموقع (عربي)',
        'start_date'           => 'تاريخ البداية',
        'end_date'             => 'تاريخ النهاية',
        'max_attendees'        => 'الحد الأقصى للحضور',
        'max_attendees_helper' => 'الحد الأقصى لعدد الزوار في هذه الفعالية',
        'is_recurring'         => 'هل الفعالية متكررة؟',
        'is_recurring_helper'  => 'فعّل هذا الخيار إذا كانت الفعالية تتكرر في أيام محددة من الأسبوع',
        'recurring_days'       => 'اختر أيام التكرار',
        'recurring_days_helper'=> 'ستُقام الفعالية فقط في الأيام المحددة بين تاريخَي البداية والنهاية',
        'image'                => 'صورة الفعالية',
        'image_helper'         => 'ارفع صورة (الحد الأقصى 2 ميغابايت). الحجم الموصى به: 1200×675 بكسل',
        'status'               => 'حالة الفعالية',
        'status_helper'        => 'الفعاليات المنشورة فقط هي التي تظهر للمستخدمين',
    ],

    'placeholders' => [
        'organizer_en' => 'Organization or person organizing the event',
        'organizer_ar' => 'المنظمة أو الشخص المسؤول عن الفعالية',
        'location_en' => 'e.g., Dubai World Trade Centre',
        'location_ar' => 'مثال: مركز دبي التجاري العالمي',
    ],

    'options' => [
        'status' => [
            'draft'     => 'مسودة',
            'published' => 'منشور',
            'cancelled' => 'ملغي',
        ],
        'days' => [
            'monday'    => 'الاثنين',
            'tuesday'   => 'الثلاثاء',
            'wednesday' => 'الأربعاء',
            'thursday'  => 'الخميس',
            'friday'    => 'الجمعة',
            'saturday'  => 'السبت',
            'sunday'    => 'الأحد',
        ],
    ],

    'columns' => [
        'image'        => 'الصورة',
        'title'        => 'العنوان',
        'organizer'    => 'المنظِّم',
        'location'     => 'الموقع',
        'start_date'   => 'تاريخ البداية',
        'end_date'     => 'تاريخ النهاية',
        'is_recurring' => 'متكررة',
        'status'       => 'الحالة',
        'bookings'     => 'الحجوزات',
        'capacity'     => 'الطاقة الاستيعابية',
        'created_at'   => 'تاريخ الإنشاء',
        'updated_at'   => 'تاريخ التعديل',
    ],

    'filters' => [
        'status'          => 'الحالة',
        'recurring'       => 'الفعاليات المتكررة',
        'recurring_all'   => 'جميع الفعاليات',
        'recurring_true'  => 'المتكررة فقط',
        'recurring_false' => 'غير المتكررة فقط',
        'start_date'      => 'تاريخ البداية',
        'start_from'      => 'تاريخ البداية من',
        'start_until'     => 'تاريخ البداية حتى',
        'indicator_from'  => 'من: :date',
        'indicator_until' => 'حتى: :date',
    ],

    'actions' => [
        'new_event'        => 'فعالية جديدة',
        'view_on_site'     => 'عرض في الموقع',
        'duplicate'        => 'نسخ',
        'publish_selected' => 'نشر المحدد',
        'move_to_draft'    => 'نقل إلى المسودة',
        'create_first'     => 'أنشئ أول فعالية',
    ],

    'empty_state' => [
        'heading'     => 'لا توجد فعاليات بعد',
        'description' => 'أنشئ أول فعالية للبدء في استقبال الحجوزات.',
    ],

    'notifications' => [
        'duplicated'      => 'تم نسخ الفعالية',
        'duplicated_body' => 'تم نسخ الفعالية بنجاح.',
        'published'       => 'تم نشر الفعاليات',
        'moved_to_draft'  => 'تم نقل الفعاليات إلى المسودة',
        'created'         => 'تم إنشاء الفعالية بنجاح',
        'updated'         => 'تم تحديث الفعالية بنجاح',
    ],

    'suffix' => [
        'people' => 'شخص',
    ],

];
