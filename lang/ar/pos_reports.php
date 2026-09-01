<?php

return [

    'sales' => [
        'title' => 'تقرير المبيعات',
        'document_title' => 'تقرير مبيعات نقطة البيع',
        'filename' => 'تقرير-مبيعات-نقطة-البيع',
        'filters' => [
            'section' => 'عوامل التصفية',
            'period' => 'الفترة',
            'channel' => 'القناة',
            'all_channels' => 'جميع القنوات',
            'language' => 'لغة التقرير',
        ],
        'channels' => [
            'counter' => 'نقطة البيع',
            'online' => 'المتجر الإلكتروني',
        ],
        'stats' => [
            'total_revenue' => 'إجمالي الإيرادات',
            'total_quantity' => 'الوحدات المباعة',
            'order_count' => 'عدد المبيعات',
            'avg_order_value' => 'متوسط قيمة البيع',
        ],
        'sections' => [
            'by_product' => 'المبيعات حسب المنتج',
            'by_category' => 'المبيعات حسب الفئة',
            'by_brand' => 'المبيعات حسب العلامة التجارية',
            'by_channel' => 'المبيعات حسب القناة',
            'trend' => 'الاتجاه اليومي',
        ],
        'columns' => [
            'product' => 'المنتج',
            'category' => 'الفئة',
            'brand' => 'العلامة التجارية',
            'channel' => 'القناة',
            'quantity' => 'الكمية',
            'revenue' => 'الإيرادات',
            'date' => 'التاريخ',
        ],
        'no_data' => 'لا توجد مبيعات في هذه الفترة.',
    ],

    'transactions' => [
        'title' => 'تقرير المعاملات',
        'document_title' => 'تقرير معاملات نقطة البيع',
        'filename' => 'تقرير-معاملات-نقطة-البيع',
        'filters' => [
            'section' => 'عوامل التصفية',
            'period' => 'الفترة',
            'language' => 'لغة التقرير',
        ],
        'stats' => [
            'total_amount' => 'إجمالي المحصّل',
            'transaction_count' => 'عدد المعاملات',
        ],
        'sections' => [
            'by_method' => 'حسب طريقة الدفع',
            'by_status' => 'حسب الحالة',
            'list' => 'المعاملات',
        ],
        'columns' => [
            'method' => 'الطريقة',
            'status' => 'الحالة',
            'count' => 'العدد',
            'amount' => 'المبلغ',
            'reference' => 'المرجع',
            'source' => 'المصدر',
            'date' => 'التاريخ',
        ],
        'sources' => [
            'counter' => 'نقطة البيع',
            'online' => 'المتجر الإلكتروني',
        ],
        'no_data' => 'لا توجد معاملات في هذه الفترة.',
    ],

    'periods' => [
        'today' => 'اليوم',
        'this_week' => 'هذا الأسبوع',
        'this_month' => 'هذا الشهر',
        'last_month' => 'الشهر الماضي',
        'this_year' => 'هذا العام',
        'custom' => 'نطاق مخصص',
    ],
    'date_from' => 'من',
    'date_to' => 'إلى',
    'languages' => [
        'en' => 'الإنجليزية',
        'ar' => 'العربية',
    ],
    'document' => [
        'period' => 'الفترة: من :from إلى :to',
        'generated_on' => 'تاريخ الإنشاء',
    ],
    'actions' => [
        'download_pdf' => 'تنزيل PDF',
        'download_excel' => 'تنزيل Excel',
    ],
    'currency' => 'ر.ع.',

];
