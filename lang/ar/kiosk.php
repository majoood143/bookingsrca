<?php

return [

    'navigation' => [
        'group'  => 'إدارة الأكشاك',
        'label'  => 'كشك',
        'plural' => 'الأكشاك',
    ],

    'sections' => [
        'information'      => 'معلومات الكشك',
        'information_desc' => 'حدد هذا الجهاز والفعالية التي يخدمها.',
        'payment'           => 'الدفع والسلوك',
        'payment_desc'      => 'طرق الدفع المتاحة في هذا الكشك، وسرعة إعادة ضبط الجلسة الخاملة.',
        'hardware'          => 'حالة الأجهزة',
        'hardware_desc'     => 'يتم الإبلاغ عنها تلقائيًا من تطبيق الكشك — للقراءة فقط.',
        'receipt'           => 'الإيصال',
        'receipt_desc'      => 'نص التذييل المطبوع على إيصال العميل.',
    ],

    'fields' => [
        'name'                    => 'اسم الكشك',
        'code'                    => 'رمز الكشك',
        'code_helper'             => 'معرف فريد يُستخدم في رابط الكشك وإعداد الجهاز. لا يمكن تغييره بعد الإنشاء.',
        'event'                   => 'الفعالية',
        'event_helper'            => 'اتركه فارغًا للسماح للعميل باختيار أي فعالية متاحة على هذا الكشك.',
        'is_active'               => 'مفعل',
        'is_active_helper'        => 'الأكشاك غير المفعلة تعرض شاشة صيانة بدلاً من واجهة الحجز.',
        'idle_timeout_seconds'    => 'مهلة الخمول (ثانية)',
        'idle_timeout_helper'     => 'يعيد ضبط واجهة الحجز بعد هذه المدة من عدم النشاط.',
        'enabled_payment_methods' => 'طرق الدفع المفعلة',
        'receipt_footer_en'       => 'تذييل الإيصال (إنجليزي)',
        'receipt_footer_ar'       => 'تذييل الإيصال (عربي)',
    ],

    'payment_methods' => [
        'wallet'         => 'الدفع بالبطاقة (محفظة مسبقة الدفع)',
        'pay_at_counter' => 'الدفع عند الكاونتر',
    ],

    'columns' => [
        'name'            => 'الاسم',
        'code'            => 'الرمز',
        'event'           => 'الفعالية',
        'all_events'      => 'كل الفعاليات',
        'status'          => 'الحالة',
        'payment_methods' => 'طرق الدفع',
        'reader'          => 'القارئ',
        'printer'         => 'الطابعة',
        'last_seen'       => 'آخر ظهور',
        'app_version'     => 'إصدار التطبيق',
        'bookings'        => 'الحجوزات',
        'never'           => 'أبداً',
        'connected'       => 'متصل',
        'disconnected'    => 'غير متصل',
    ],

    'actions' => [
        'new'         => 'كشك جديد',
        'activate'    => 'تفعيل',
        'deactivate'  => 'تعطيل',
    ],

    'notifications' => [
        'created'          => 'تم إنشاء الكشك.',
        'updated'          => 'تم تحديث الكشك.',
        'status_updated'   => 'تم تحديث حالة الكشك.',
        'kiosk_activated'  => 'تم تفعيل الكشك.',
        'kiosk_deactivated' => 'تم تعطيل الكشك.',
    ],

    'empty_state' => [
        'heading'     => 'لا توجد أكشاك بعد',
        'description' => 'سجل جهازًا للحصول على رابط الكشك الخاص به للإعداد.',
    ],
];
