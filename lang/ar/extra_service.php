<?php

return [

    'navigation' => [
        'group'  => 'إدارة الفعاليات',
        'label'  => 'خدمة إضافية',
        'plural' => 'الخدمات الإضافية',
    ],

    'sections' => [
        'information'      => 'معلومات الخدمة',
        'information_desc' => 'حدّد الخدمات الإضافية للفعاليات (وجبات، مواصلات، بضائع، إلخ)',
        'pricing'          => 'التسعير والتوفر',
        'pricing_desc'     => 'حدد السعر وإعدادات الكمية',
        'status'           => 'الحالة والإعدادات',
        'usage'            => 'نظرة عامة على الاستخدام',
        'usage_desc'       => 'عرض إحصائيات الاستخدام الحالية',
    ],

    'fields' => [
        'event'                     => 'الفعالية',
        'event_helper'              => 'اختر الفعالية لهذه الخدمة الإضافية',
        'name_en'                   => 'اسم الخدمة (إنجليزي)',
        'name_en_helper'            => 'أدخل اسم الخدمة بالإنجليزية',
        'name_ar'                   => 'اسم الخدمة (عربي)',
        'name_ar_helper'            => 'أدخل اسم الخدمة بالعربية',
        'description_en'            => 'الوصف (إنجليزي)',
        'description_ar'            => 'الوصف (عربي)',
        'price'                     => 'السعر',
        'price_helper'              => 'حدد سعر الخدمة بالدولار الأمريكي',
        'quantity_available'        => 'الكمية المتاحة',
        'quantity_available_helper' => 'اتركه فارغًا للكمية غير المحدودة',
        'quantity_used'             => 'الكمية المستخدمة',
        'quantity_used_helper'      => 'يُحدَّث تلقائيًا عند الحجز',
        'is_active'                 => 'نشط',
        'is_active_helper'          => 'الخدمات النشطة فقط هي المتاحة أثناء الحجز',
    ],

    'placeholders' => [
        'name_en'        => 'e.g., Lunch, Transportation, T-Shirt',
        'name_ar'        => 'مثال: وجبة غداء، مواصلات، تي شيرت',
        'description_en' => 'Describe what this service includes...',
        'description_ar' => 'اشرح ما تتضمنه هذه الخدمة...',
        'quantity'       => 'غير محدود',
    ],

    'create_event_form' => [
        'title_en'  => 'العنوان (إنجليزي)',
        'title_ar'  => 'العنوان (عربي)',
        'draft'     => 'مسودة',
        'published' => 'منشور',
    ],

    'quantity_info' => [
        'unlimited_badge'       => '♾️ كمية غير محدودة',
        'unlimited_description' => 'هذه الخدمة لا تخضع لأي قيود على الكمية.',
        'limited_badge'         => '✓ كمية محدودة',
        'limited_description'   => 'هذه الخدمة لها حد أقصى بـ :count وحدة.',
    ],

    'usage_info' => [
        'pending'           => 'ستتوفر معلومات الاستخدام بعد إنشاء الخدمة.',
        'total_used'        => 'إجمالي المستخدم',
        'no_limit'          => 'بلا حد',
        'revenue_generated' => 'الإيراد المحقق',
        'unlimited_service' => 'خدمة غير محدودة',
        'used'              => 'مستخدم',
        'available'         => 'متاح',
        'total'             => 'الإجمالي',
        'usage_progress'    => 'نسبة الاستخدام',
        'potential_revenue' => 'الإيراد المحتمل',
    ],

    'columns' => [
        'event'      => 'الفعالية',
        'name'       => 'اسم الخدمة',
        'price'      => 'السعر',
        'total'      => 'الإجمالي',
        'used'       => 'المستخدم',
        'available'  => 'المتاح',
        'used_pct'   => '% المستخدم',
        'revenue'    => 'الإيراد',
        'status'     => 'الحالة',
        'bookings'   => 'الحجوزات',
        'created_at' => 'تاريخ الإنشاء',
        'updated_at' => 'تاريخ التعديل',
    ],

    'filters' => [
        'by_event'           => 'تصفية حسب الفعالية',
        'status'             => 'الحالة',
        'status_all'         => 'جميع الخدمات',
        'status_active'      => 'النشطة فقط',
        'status_inactive'    => 'غير النشطة فقط',
        'quantity_type'      => 'نوع الكمية',
        'quantity_all'       => 'جميع الخدمات',
        'quantity_limited'   => 'كمية محدودة',
        'quantity_unlimited' => 'كمية غير محدودة',
        'availability'       => 'التوفر',
        'availability_show'  => 'عرض',
        'avail_available'    => 'الخدمات المتاحة',
        'avail_depleted'     => 'المنتهية (0 متبقي)',
        'avail_almost'       => 'شبه منتهية (>80%)',
        'avail_low'          => 'مخزون منخفض (<20 متبقي)',
        'ind_available'      => 'الخدمات المتاحة فقط',
        'ind_depleted'       => 'الخدمات المنتهية فقط',
        'ind_almost'         => 'شبه منتهية (>80%)',
        'ind_low'            => 'مخزون منخفض (<20 متبقي)',
        'price_range'        => 'نطاق السعر',
        'price_from'         => 'من',
        'price_to'           => 'إلى',
        'ind_price_from'     => 'السعر من: $:amount',
        'ind_price_to'       => 'السعر إلى: $:amount',
    ],

    'actions' => [
        'new'                 => 'خدمة إضافية جديدة',
        'deactivate'          => 'تعطيل',
        'activate'            => 'تفعيل',
        'activate_selected'   => 'تفعيل المحدد',
        'deactivate_selected' => 'تعطيل المحدد',
        'create_first'        => 'أنشئ أول خدمة إضافية',
    ],

    'modals' => [
        'deactivate_heading'     => 'تعطيل الخدمة',
        'activate_heading'       => 'تفعيل الخدمة',
        'deactivate_description' => 'لن تكون هذه الخدمة متاحة أثناء الحجز.',
        'activate_description'   => 'ستصبح هذه الخدمة متاحة أثناء الحجز.',
    ],

    'empty_state' => [
        'heading'     => 'لا توجد خدمات إضافية بعد',
        'description' => 'أنشئ خدمات إضافية يمكن للحضور إضافتها إلى حجوزاتهم.',
    ],

    'notifications' => [
        'status_updated'       => 'تم تحديث الحالة',
        'service_activated'    => 'تم تفعيل الخدمة',
        'service_deactivated'  => 'تم تعطيل الخدمة',
        'services_activated'   => 'تم تفعيل الخدمات',
        'services_deactivated' => 'تم تعطيل الخدمات',
        'cannot_delete'        => 'لا يمكن الحذف',
        'has_bookings'         => 'هذه الخدمة لديها حجوزات موجودة.',
        'bulk_has_bookings'    => ':count خدمة/خدمات لديها حجوزات موجودة.',
        'created'              => 'تم إنشاء الخدمة الإضافية بنجاح',
        'updated'              => 'تم تحديث الخدمة الإضافية بنجاح',
    ],

    'suffix' => [
        'units' => 'وحدة',
        'used'  => 'مستخدم',
    ],

];
