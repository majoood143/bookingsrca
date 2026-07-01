<?php

return [

    'navigation' => [
        'group'  => 'إدارة الفعاليات',
        'label'  => 'نوع التذكرة',
        'plural' => 'أنواع التذاكر',
    ],

    'sections' => [
        'information'         => 'معلومات نوع التذكرة',
        'information_desc'    => 'حدّد أنواع التذاكر للفعاليات (عادي، VIP، طالب، إلخ)',
        'pricing'             => 'التسعير والسعة',
        'pricing_desc'        => 'حدد السعر والكمية المتاحة',
        'sale_period'         => 'فترة البيع',
        'sale_period_desc'    => 'اختياري: تحديد متى يمكن شراء هذا النوع من التذاكر',
        'status'              => 'الحالة والإعدادات',
        'dependency'          => 'الاعتمادية على تذكرة أخرى',
        'dependency_desc'     => 'اختياري: يلزم اختيار نوع تذكرة آخر قبل إمكانية حجز هذه التذكرة',
        'sales_overview'      => 'نظرة عامة على المبيعات',
        'sales_overview_desc' => 'عرض إحصائيات المبيعات والتوفر الحالية',
    ],

    'fields' => [
        'event'                     => 'الفعالية',
        'event_helper'              => 'اختر الفعالية لنوع التذكرة هذا',
        'name_en'                   => 'الاسم (إنجليزي)',
        'name_en_helper'            => 'أدخل اسم نوع التذكرة بالإنجليزية',
        'name_ar'                   => 'الاسم (عربي)',
        'name_ar_helper'            => 'أدخل اسم نوع التذكرة بالعربية',
        'description_en'            => 'الوصف (إنجليزي)',
        'description_ar'            => 'الوصف (عربي)',
        'price'                     => 'السعر',
        'price_helper'              => 'حدد سعر التذكرة بالريال العماني ',
        'quantity_available'        => 'الكمية المتاحة',
        'quantity_available_helper' => 'إجمالي التذاكر المتاحة',
        'quantity_sold'             => 'الكمية المباعة',
        'quantity_sold_helper'      => 'يُحدَّث تلقائيًا عند الحجز',
        'sale_start_date'           => 'تاريخ بدء البيع',
        'sale_start_helper'         => 'اتركه فارغًا لعدم التقييد',
        'sale_end_date'             => 'تاريخ انتهاء البيع',
        'sale_end_helper'           => 'اتركه فارغًا لعدم التقييد',
        'is_active'                 => 'نشط',
        'is_active_helper'          => 'أنواع التذاكر النشطة فقط هي المتاحة للشراء',
        'depends_on'                => 'تعتمد على',
        'depends_on_helper'         => 'يجب على المستخدم اختيار نوع التذكرة الأصل أولاً قبل إضافة هذه التذكرة',
        'depends_on_placeholder'    => 'بلا اعتمادية (تذكرة مستقلة)',
    ],

    'placeholders' => [
        'name_en'        => 'e.g., Standard, VIP, Student',
        'name_ar'        => 'مثال: عادي، في آي بي، طالب',
        'description_en' => 'Describe what this ticket includes...',
        'description_ar' => 'اشرح ما تتضمنه هذه التذكرة...',
    ],

    'create_event_form' => [
        'title_en'  => 'العنوان (إنجليزي)',
        'title_ar'  => 'العنوان (عربي)',
        'draft'     => 'مسودة',
        'published' => 'منشور',
    ],

    'sale_period_status' => [
        'always_available' => '✓ متاح للشراء دائمًا',
        'not_yet'          => '⚠ غير متاح بعد (يبدأ :date)',
        'ended'            => '✗ انتهت فترة البيع (:date)',
        'currently'        => '✓ متاح للشراء حاليًا',
        'pending'          => 'ستُطبَّق إعدادات فترة البيع بعد إنشاء نوع التذكرة.',
    ],

    'sales_info' => [
        'pending'           => 'ستتوفر معلومات المبيعات بعد إنشاء نوع التذكرة.',
        'sold'              => 'مباع',
        'available'         => 'متاح',
        'total'             => 'الإجمالي',
        'sales_progress'    => 'تقدم المبيعات',
        'revenue_generated' => 'الإيراد المحقق',
        'potential_revenue' => 'الإيراد المحتمل',
    ],

    'columns' => [
        'event'       => 'الفعالية',
        'name'        => 'نوع التذكرة',
        'price'       => 'السعر',
        'total'       => 'الإجمالي',
        'sold'        => 'المباع',
        'available'   => 'المتاح',
        'sold_pct'    => '% المباع',
        'revenue'     => 'الإيراد',
        'status'      => 'الحالة',
        'sale_period' => 'فترة البيع',
        'depends_on'  => 'تعتمد على',
        'bookings'    => 'الحجوزات',
        'created_at'  => 'تاريخ الإنشاء',
        'updated_at'  => 'تاريخ التعديل',
    ],

    'sale_period_column' => [
        'always' => 'دائمًا',
        'from'   => 'من :date',
        'until'  => 'حتى :date',
    ],

    'filters' => [
        'by_event'          => 'تصفية حسب الفعالية',
        'status'            => 'الحالة',
        'status_all'        => 'جميع أنواع التذاكر',
        'status_active'     => 'النشطة فقط',
        'status_inactive'   => 'غير النشطة فقط',
        'availability'      => 'التوفر',
        'availability_show' => 'عرض',
        'avail_available'   => 'التذاكر المتاحة',
        'avail_sold_out'    => 'نفدت',
        'avail_almost'      => 'شبه نافدة (>80%)',
        'avail_low'         => 'مخزون منخفض (<50 متبقي)',
        'ind_available'     => 'التذاكر المتاحة فقط',
        'ind_sold_out'      => 'التذاكر النافدة فقط',
        'ind_almost'        => 'شبه نافدة (>80%)',
        'ind_low'           => 'مخزون منخفض (<50 متبقي)',
        'price_range'       => 'نطاق السعر',
        'price_from'        => 'من',
        'price_to'          => 'إلى',
        'ind_price_from'    => 'السعر من: $:amount',
        'ind_price_to'      => 'السعر إلى: $:amount',
    ],

    'actions' => [
        'new'                 => 'نوع تذكرة جديد',
        'deactivate'          => 'تعطيل',
        'activate'            => 'تفعيل',
        'activate_selected'   => 'تفعيل المحدد',
        'deactivate_selected' => 'تعطيل المحدد',
        'create_first'        => 'أنشئ أول نوع تذكرة',
    ],

    'modals' => [
        'deactivate_heading'     => 'تعطيل نوع التذكرة',
        'activate_heading'       => 'تفعيل نوع التذكرة',
        'deactivate_description' => 'لن يكون نوع التذكرة هذا متاحًا للشراء.',
        'activate_description'   => 'سيصبح نوع التذكرة هذا متاحًا للشراء.',
    ],

    'empty_state' => [
        'heading'     => 'لا توجد أنواع تذاكر بعد',
        'description' => 'أنشئ أنواع تذاكر لفعالياتك لتفعيل الحجوزات.',
    ],

    'notifications' => [
        'status_updated'    => 'تم تحديث الحالة',
        'type_activated'    => 'تم تفعيل نوع التذكرة',
        'type_deactivated'  => 'تم تعطيل نوع التذكرة',
        'types_activated'   => 'تم تفعيل أنواع التذاكر',
        'types_deactivated' => 'تم تعطيل أنواع التذاكر',
        'cannot_delete'     => 'لا يمكن الحذف',
        'has_bookings'      => 'نوع التذكرة هذا لديه حجوزات موجودة.',
        'bulk_has_bookings' => ':count نوع/أنواع تذاكر لديها حجوزات موجودة.',
        'created'           => 'تم إنشاء نوع التذكرة بنجاح',
        'updated'           => 'تم تحديث نوع التذكرة بنجاح',
    ],

    'suffix' => [
        'tickets' => 'تذكرة',
        'sold'    => 'مباع',
    ],

];
