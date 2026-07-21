<?php

return [

    'navigation' => [
        'label' => 'تقرير المصروفات',
        'group' => 'إدارة الحجوزات',
    ],

    'title' => 'تقرير المصروفات',
    'document_title' => 'التقرير الرسمي للمصروفات',

    'filters' => [
        'period' => 'الفترة',
        'event' => 'الفعالية',
        'all_events' => 'جميع الفعاليات',
        'category' => 'الفئة',
        'all_categories' => 'جميع الفئات',
        'type' => 'نوع المصروف',
        'all_types' => 'جميع الأنواع',
        'payment_status' => 'حالة الدفع',
        'all_statuses' => 'جميع الحالات',
        'date_from' => 'من تاريخ',
        'date_to' => 'إلى تاريخ',
        'language' => 'لغة التقرير',
    ],

    'periods' => [
        'today' => 'اليوم',
        'this_week' => 'هذا الأسبوع',
        'this_month' => 'هذا الشهر',
        'last_month' => 'الشهر الماضي',
        'this_year' => 'هذا العام',
        'custom' => 'نطاق مخصص',
    ],

    'languages' => [
        'en' => 'الإنجليزية',
        'ar' => 'العربية',
    ],

    'sections' => [
        'filters' => 'التصفية',
        'summary' => 'الملخص',
        'expense_trend' => 'اتجاه المصروفات',
        'by_category' => 'المصروفات حسب الفئة',
        'by_type' => 'المصروفات حسب النوع',
        'by_event' => 'المصروفات حسب الفعالية',
        'by_payment_status' => 'المصروفات حسب حالة الدفع',
    ],

    'stats' => [
        'total_amount' => 'إجمالي المصروفات',
        'total_tax' => 'إجمالي الضريبة',
        'total_paid' => 'إجمالي المدفوع',
        'total_pending' => 'إجمالي المعلق',
        'expense_count' => 'عدد المصروفات',
        'avg_expense' => 'متوسط المصروف',
    ],

    'columns' => [
        'date' => 'التاريخ',
        'category' => 'الفئة',
        'type' => 'النوع',
        'event' => 'الفعالية',
        'payment_status' => 'حالة الدفع',
        'count' => 'العدد',
        'amount' => 'المبلغ',
    ],

    'currency' => 'ريال عماني',

    'uncategorized' => 'غير مصنف',

    'actions' => [
        'download_pdf' => 'تحميل PDF',
        'download_csv' => 'تحميل CSV',
        'download_excel' => 'تحميل Excel',
    ],

    'export' => [
        'filename' => 'تقرير-المصروفات',
    ],

    'document' => [
        'generated_on' => 'تم الإنشاء في',
        'period_label' => 'فترة التقرير',
        'period' => ':from — :to',
        'page' => 'صفحة',
        'confidential' => 'وثيقة رسمية — تم إنشاؤها بواسطة نظام إدارة الحجوزات',
    ],

    'no_data' => 'لا توجد بيانات مصروفات متاحة للفترة المحددة.',

];
