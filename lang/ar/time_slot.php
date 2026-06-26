<?php

return [

    'navigation' => [
        'group'  => 'إدارة الفعاليات',
        'label'  => 'فترة زمنية',
        'plural' => 'الفترات الزمنية',
    ],

    'sections' => [
        'information'      => 'معلومات الفترة الزمنية',
        'information_desc' => 'تهيئة الفترة الزمنية لفعالية',
        'capacity'         => 'نظرة عامة على السعة',
        'capacity_desc'    => 'عرض السعة الحالية والتوفر',
    ],

    'fields' => [
        'event'                   => 'الفعالية',
        'event_helper'            => 'اختر الفعالية لهذه الفترة الزمنية',
        'date'                    => 'التاريخ',
        'date_helper'             => 'التاريخ المحدد لهذه الفترة. تُتابَع السعة بشكل مستقل لكل تاريخ.',
        'date_not_recurring_day'  => 'هذا التاريخ لا يقع في أحد أيام التكرار المحددة للفعالية.',
        'date_unique'             => 'توجد فترة زمنية بهذا التاريخ والنطاق الزمني لهذه الفعالية مسبقًا.',
        'start_time'              => 'وقت البداية',
        'start_time_helper'       => 'اختر وقت بداية هذه الفترة',
        'end_time'                => 'وقت النهاية',
        'end_time_helper'         => 'اختر وقت نهاية هذه الفترة',
        'max_attendees'           => 'الحد الأقصى للحضور',
        'max_attendees_helper'    => 'الحد الأقصى لعدد الحجوزات في هذه الفترة الزمنية',
        'current_bookings'        => 'الحجوزات الحالية',
        'current_bookings_helper' => 'يُحدَّث تلقائيًا عند إجراء الحجوزات',
        'is_active'               => 'نشط',
        'is_active_helper'        => 'الفترات الزمنية النشطة فقط هي المتاحة للحجز',
    ],

    'create_event_form' => [
        'title_en'  => 'العنوان (إنجليزي)',
        'title_ar'  => 'العنوان (عربي)',
        'draft'     => 'مسودة',
        'published' => 'منشور',
    ],

    'capacity_info' => [
        'pending'    => 'ستتوفر معلومات السعة بعد إنشاء الفترة الزمنية.',
        'booked'     => 'محجوز:',
        'available'  => 'متاح:',
        'total'      => 'الإجمالي:',
        'filled_pct' => ':percent% من السعة ممتلئة',
    ],

    'columns' => [
        'event'          => 'الفعالية',
        'date'           => 'التاريخ',
        'start_time'     => 'وقت البداية',
        'end_time'       => 'وقت النهاية',
        'time_range'     => 'النطاق الزمني',
        'capacity'       => 'الطاقة الاستيعابية',
        'booked'         => 'المحجوز',
        'available'      => 'المتاح',
        'filled'         => 'الممتلئ',
        'status'         => 'الحالة',
        'total_bookings' => 'إجمالي الحجوزات',
        'created_at'     => 'تاريخ الإنشاء',
        'updated_at'     => 'تاريخ التعديل',
    ],

    'filters' => [
        'by_event'          => 'تصفية حسب الفعالية',
        'status'            => 'الحالة',
        'status_all'        => 'جميع الفترات',
        'status_active'     => 'النشطة فقط',
        'status_inactive'   => 'غير النشطة فقط',
        'availability'      => 'التوفر',
        'availability_show' => 'عرض',
        'avail_available'   => 'الفترات المتاحة',
        'avail_full'        => 'الفترات الممتلئة',
        'avail_almost'      => 'شبه ممتلئة (>80%)',
        'ind_available'     => 'الفترات المتاحة فقط',
        'ind_full'          => 'الفترات الممتلئة فقط',
        'ind_almost'        => 'الفترات شبه الممتلئة (>80%)',
        'time_range'        => 'النطاق الزمني',
        'from'              => 'من',
        'to'                => 'إلى',
        'date'              => 'التاريخ',
    ],

    'actions' => [
        'new'                 => 'فترة زمنية جديدة',
        'deactivate'          => 'تعطيل',
        'activate'            => 'تفعيل',
        'activate_selected'   => 'تفعيل المحدد',
        'deactivate_selected' => 'تعطيل المحدد',
        'create_first'        => 'أنشئ أول فترة زمنية',
        'generate_slots'      => 'إنشاء فترات زمنية',
    ],

    'modals' => [
        'deactivate_heading'         => 'تعطيل الفترة الزمنية',
        'activate_heading'           => 'تفعيل الفترة الزمنية',
        'deactivate_description'     => 'لن تكون هذه الفترة الزمنية متاحة للحجوزات الجديدة.',
        'activate_description'      => 'ستصبح هذه الفترة الزمنية متاحة للحجوزات.',
        'generate_slots_description' => 'ينشئ فترة زمنية واحدة لكل تاريخ متاح للفعالية المحددة (مع مراعاة أيام التكرار إن وجدت). الفترات الموجودة مسبقًا لنفس التاريخ والوقت لا تتأثر.',
    ],

    'empty_state' => [
        'heading'     => 'لا توجد فترات زمنية بعد',
        'description' => 'أنشئ فترات زمنية لفعالياتك لتفعيل الحجوزات.',
    ],

    'notifications' => [
        'status_updated'    => 'تم تحديث الحالة',
        'slot_activated'    => 'تم تفعيل الفترة الزمنية',
        'slot_deactivated'  => 'تم تعطيل الفترة الزمنية',
        'slots_activated'   => 'تم تفعيل الفترات الزمنية',
        'slots_deactivated' => 'تم تعطيل الفترات الزمنية',
        'cannot_delete'     => 'لا يمكن الحذف',
        'has_bookings'      => 'هذه الفترة الزمنية لديها حجوزات موجودة.',
        'bulk_has_bookings' => ':count فترة/فترات زمنية لديها حجوزات موجودة.',
        'created'           => 'تم إنشاء الفترة الزمنية بنجاح',
        'updated'           => 'تم تحديث الفترة الزمنية بنجاح',
        'slots_generated'      => 'تم إنشاء الفترات الزمنية',
        'slots_generated_body' => 'تم إنشاء :created، وتم تجاوز :skipped (موجودة مسبقًا).',
    ],

    'suffix' => [
        'people' => 'شخص',
        'booked' => 'محجوز',
    ],

];
