<?php

return [

    'navigation' => [
        'label' => 'تقرير العمولة',
        'group' => 'إدارة الحجوزات',
    ],

    'title' => 'تقرير العمولة',
    'document_title' => 'التقرير الرسمي للعمولة',

    'filters' => [
        'period' => 'الفترة',
        'event' => 'الفعالية',
        'all_events' => 'جميع الفعاليات',
        'date_from' => 'من تاريخ',
        'date_to' => 'إلى تاريخ',
        'language' => 'لغة التقرير',
    ],

    'periods' => [
        'today' => 'اليوم',
        'this_week' => 'هذا الأسبوع',
        'this_month' => 'هذا الشهر',
        'last_month' => 'الشهر الماضي',
        'this_year' => 'هذه السنة',
        'custom' => 'فترة مخصصة',
    ],

    'languages' => [
        'en' => 'الإنجليزية',
        'ar' => 'العربية',
    ],

    'sections' => [
        'filters' => 'التصفية',
        'summary' => 'الملخص',
        'by_event' => 'العمولة حسب الفعالية',
        'trend' => 'اتجاه الإيرادات والعمولة',
    ],

    'stats' => [
        'total_revenue' => 'إجمالي الإيرادات',
        'total_commission' => 'إجمالي العمولة',
        'booking_count' => 'عدد الحجوزات',
        'avg_rate' => 'متوسط نسبة العمولة',
    ],

    'columns' => [
        'date' => 'التاريخ',
        'event' => 'الفعالية',
        'count' => 'الحجوزات',
        'rate' => 'النسبة',
        'revenue' => 'الإيرادات',
        'commission' => 'العمولة',
    ],

    'currency' => 'ر.ع',

    'no_rate' => 'لا توجد نسبة محددة',
    'no_rate_warning' => 'لا توجد نسبة عمولة محددة حاليًا لعدد :count حجز/حجوزات في هذه الفترة. تظهر عمولتها كصفر. أضف إعداد عمولة عام أو خاص بالفعالية لتغطيتها.',

    'actions' => [
        'download_pdf' => 'تحميل PDF',
        'download_csv' => 'تحميل CSV',
        'download_excel' => 'تحميل Excel',
    ],

    'export' => [
        'filename' => 'تقرير-العمولة',
    ],

    'document' => [
        'generated_on' => 'تاريخ الإنشاء',
        'period' => ':from — :to',
        'confidential' => 'وثيقة رسمية — تم إنشاؤها بواسطة نظام إدارة الحجوزات',
    ],

    'no_data' => 'لا تتوفر بيانات إيرادات للفترة المحددة.',

];
