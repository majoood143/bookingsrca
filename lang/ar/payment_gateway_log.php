<?php

return [

    'navigation' => [
        'group'  => 'إدارة الحجوزات',
        'label'  => 'سجل بوابة الدفع',
        'plural' => 'سجلات بوابة الدفع',
    ],

    'columns' => [
        'created_at'       => 'التاريخ',
        'booking'          => 'الحجز',
        'gateway'          => 'البوابة',
        'event'            => 'الحدث',
        'result'           => 'النتيجة',
        'status_code'      => 'حالة HTTP',
        'response_preview' => 'معاينة الاستجابة',
    ],

    'filters' => [
        'gateway'                    => 'البوابة',
        'event'                      => 'الحدث',
        'result'                     => 'النتيجة',
        'status_code'                => 'حالة HTTP',
        'date'                       => 'التاريخ',
        'from'                       => 'من',
        'to'                         => 'إلى',
        'payload_search'             => 'بحث في البيانات',
        'payload_search_placeholder' => 'مثال: رقم الطلب، رقم التتبع، رسالة الخطأ...',
    ],

    'events' => [
        'create_session'   => 'إنشاء جلسة',
        'get_session'       => 'جلب الجلسة',
        'initiate_payment' => 'بدء الدفع',
        'callback'          => 'استدعاء',
        'webhook'           => 'ويب هوك',
    ],

    'outcomes' => [
        'success' => 'ناجح',
        'failed'  => 'فاشل',
        'pending' => 'قيد الانتظار',
        'error'   => 'خطأ',
        'unknown' => 'غير معروف',
    ],

    'status_ranges' => [
        '2xx' => 'ناجح (2xx)',
        '4xx' => 'خطأ في الطلب (4xx)',
        '5xx' => 'خطأ في الخادم (5xx)',
        'none' => 'بدون حالة HTTP',
    ],

    'tabs' => [
        'all'     => 'الكل',
        'success' => 'ناجح',
        'failed'  => 'فاشل',
        'error'   => 'خطأ',
    ],

    'sections' => [
        'overview' => 'نظرة عامة',
        'payloads' => 'بيانات الطلب والاستجابة',
    ],

    'gateway_logs' => [
        'request'  => 'الطلب',
        'response' => 'الاستجابة',
        'empty'    => 'لا توجد معاملات بوابة دفع مسجلة.',
    ],

    'widgets' => [
        'total_last_7_days' => 'المعاملات (7 أيام)',
        'total_desc'        => 'عمليات بوابة الدفع المسجلة خلال آخر 7 أيام',
        'success_rate'      => 'نسبة النجاح',
        'success_desc'      => ':count ناجحة',
        'failed'            => 'فاشلة',
        'failed_desc'       => 'محاولات لم تنتج عنها عملية دفع',
        'errors'            => 'أخطاء',
        'errors_desc'       => ':count اليوم',
    ],

    'empty_state' => [
        'heading'     => 'لا توجد سجلات بوابة دفع بعد',
        'description' => 'ستظهر هنا معاملات ثواني وبنك مسقط عند حدوثها.',
    ],

];
