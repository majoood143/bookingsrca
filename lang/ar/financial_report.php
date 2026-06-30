<?php

return [

    'navigation' => [
        'label' => 'التقرير المالي',
        'group' => 'إدارة الحجوزات',
    ],

    'title' => 'التقرير المالي',
    'document_title' => 'التقرير المالي الرسمي',

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
        'language'   => 'لغة التقرير',
    ],

    'periods' => [
        'today'      => 'اليوم',
        'this_week'  => 'هذا الأسبوع',
        'this_month' => 'هذا الشهر',
        'last_month' => 'الشهر الماضي',
        'this_year'  => 'هذا العام',
        'custom'     => 'نطاق مخصص',
    ],

    'languages' => [
        'en' => 'الإنجليزية',
        'ar' => 'العربية',
    ],

    'sections' => [
        'filters'           => 'الفلاتر',
        'summary'           => 'الملخص',
        'revenue_trend'     => 'اتجاه الإيرادات',
        'by_ticket'         => 'الإيرادات حسب نوع التذكرة',
        'by_event'          => 'الإيرادات حسب الفعالية',
        'by_payment_method' => 'الإيرادات حسب طريقة الدفع',
    ],

    'stats' => [
        'total_revenue'  => 'إجمالي الإيرادات',
        'total_paid'     => 'إجمالي المدفوع',
        'balance_due'    => 'المبلغ المتبقي',
        'total_bookings' => 'إجمالي الحجوزات',
        'avg_booking'    => 'متوسط قيمة الحجز',
    ],

    'payment_methods' => [
        'cash'         => 'نقدًا',
        'credit_debit' => 'بطاقة ائتمان / خصم',
        'partial'      => 'دفعة جزئية',
    ],

    'columns' => [
        'date'           => 'التاريخ',
        'ticket_type'    => 'نوع التذكرة',
        'event'          => 'الفعالية',
        'payment_method' => 'طريقة الدفع',
        'bookings'       => 'الحجوزات',
        'revenue'        => 'الإيرادات',
        'percentage'     => 'النسبة',
    ],

    'currency' => 'ر.ع.',

    'actions' => [
        'download_pdf'   => 'تحميل PDF',
        'download_csv'   => 'تحميل CSV',
        'download_excel' => 'تحميل Excel',
    ],

    'export' => [
        'filename' => 'التقرير-المالي',
    ],

    'document' => [
        'generated_on' => 'تاريخ الإنشاء',
        'period_label' => 'الفترة المشمولة بالتقرير',
        'period'       => ':from — :to',
        'page'         => 'صفحة',
        'confidential' => 'وثيقة رسمية — تم إنشاؤها بواسطة نظام إدارة الحجوزات',
    ],

    'no_data' => 'لا توجد بيانات مالية متاحة للفترة المحددة.',

];
