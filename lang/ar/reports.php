<?php

return [

    'navigation' => [
        'label' => 'التقارير',
        'group' => 'إدارة الحجوزات',
    ],

    'title' => 'التقارير',
    'document_title' => 'تقرير الحجوزات الرسمي',

    'sections' => [
        'filters'   => 'الفلاتر',
        'summary'   => 'الملخص',
        'by_event'  => 'تفصيل حسب الفعالية',
        'by_ticket' => 'تفصيل حسب نوع التذكرة',
    ],

    'filters' => [
        'period'     => 'الفترة الزمنية',
        'event'      => 'الفعالية',
        'all_events' => 'جميع الفعاليات',
        'event_date' => 'تاريخ الفعالية',
        'all_dates'  => 'جميع التواريخ',
        'time_slot'  => 'الفترة الزمنية للحجز',
        'all_slots'  => 'جميع الفترات',
        'date_from'  => 'من تاريخ',
        'date_to'    => 'إلى تاريخ',
    ],

    'periods' => [
        'today'      => 'اليوم',
        'this_week'  => 'هذا الأسبوع',
        'this_month' => 'هذا الشهر',
        'last_month' => 'الشهر الماضي',
        'this_year'  => 'هذا العام',
        'custom'     => 'نطاق مخصص',
    ],

    'stats' => [
        'total_bookings'  => 'إجمالي الحجوزات',
        'confirmed'       => 'مؤكدة',
        'pending'         => 'قيد الانتظار',
        'cancelled'       => 'ملغاة',
        'checked_in'      => 'تم تسجيل الدخول',
        'total_revenue'   => 'إجمالي الإيرادات',
        'total_attendees' => 'إجمالي الزوار',
    ],

    'columns' => [
        'event'       => 'الفعالية',
        'ticket_type' => 'نوع التذكرة',
        'total'       => 'إجمالي الحجوزات',
        'confirmed'   => 'مؤكدة',
        'pending'     => 'قيد الانتظار',
        'cancelled'   => 'ملغاة',
        'checked_in'  => 'تسجيل الدخول',
        'revenue'     => 'الإيرادات',
        'attendees'   => 'الزوار',
        'bookings'    => 'الحجوزات',
    ],

    'actions' => [
        'export'       => 'تصدير Excel',
        'apply'        => 'تطبيق الفلاتر',
        'download_pdf' => 'تحميل PDF',
    ],

    'export' => [
        'sheet_summary'  => 'الملخص',
        'sheet_bookings' => 'الحجوزات',
        'filename'       => 'تقرير',

        'headings' => [
            'reference'      => 'رقم المرجع',
            'event'          => 'الفعالية',
            'event_date'     => 'تاريخ الفعالية',
            'time_slot'      => 'الوقت',
            'ticket_type'    => 'نوع التذكرة',
            'quantity'       => 'الكمية',
            'ticket_price'   => 'سعر التذكرة',
            'services_price' => 'سعر الخدمات',
            'total_price'    => 'السعر الإجمالي',
            'status'         => 'الحالة',
            'attendees'      => 'أسماء الزوار',
            'confirmed_at'   => 'تاريخ التأكيد',
            'cancelled_at'   => 'تاريخ الإلغاء',
            'created_at'     => 'تاريخ الإنشاء',
        ],
    ],

    'no_data'      => 'لا توجد بيانات متاحة للفترة المحددة.',
    'period_label' => 'الفترة: :from — :to',

    'document' => [
        'generated_on' => 'تاريخ الإنشاء',
        'period'       => ':from — :to',
    ],

];
