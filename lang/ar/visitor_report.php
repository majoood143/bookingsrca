<?php

return [

    'navigation' => [
        'label' => 'تقرير الزوار',
        'group' => 'إدارة الحجوزات',
    ],

    'title' => 'تقرير الزوار',
    'document_title' => 'تقرير الزوار الرسمي',

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
        'filters'      => 'الفلاتر',
        'summary'      => 'الملخص',
        'by_gender'    => 'الزوار حسب الجنس',
        'by_ticket'    => 'الزوار حسب نوع التذكرة',
        'by_time_slot' => 'الزوار حسب الفترة الزمنية',
        'by_country'   => 'الزوار حسب الدولة',
    ],

    'stats' => [
        'total_visitors' => 'إجمالي الزوار',
        'total_bookings' => 'إجمالي الحجوزات',
        'checked_in'      => 'تم تسجيل الدخول',
        'events_covered'  => 'عدد الفعاليات',
    ],

    'gender' => [
        'male'        => 'ذكر',
        'female'      => 'أنثى',
        'unspecified' => 'غير محدد',
    ],

    'columns' => [
        'gender'      => 'الجنس',
        'count'       => 'عدد الزوار',
        'percentage'  => 'النسبة',
        'ticket_type' => 'نوع التذكرة',
        'time_slot'   => 'الفترة الزمنية',
        'country'     => 'الدولة',
        'other'       => 'أخرى',
    ],

    'actions' => [
        'download_pdf'   => 'تحميل PDF',
        'download_csv'   => 'تحميل CSV',
        'download_excel' => 'تحميل Excel',
    ],

    'export' => [
        'filename' => 'تقرير-الزوار',
    ],

    'document' => [
        'generated_on' => 'تاريخ الإنشاء',
        'period_label' => 'الفترة المشمولة بالتقرير',
        'period'       => ':from — :to',
        'page'         => 'صفحة',
        'confidential' => 'وثيقة رسمية — تم إنشاؤها بواسطة نظام إدارة الحجوزات',
    ],

    'no_data' => 'لا توجد بيانات زوار متاحة للفترة المحددة.',

];
