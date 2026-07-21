<?php

return [

    'navigation' => [
        'group' => 'إدارة الحجوزات',
        'label' => 'مصروف',
        'plural' => 'المصروفات',
    ],

    'types' => [
        'event' => 'مصروف فعالية',
        'operational' => 'تشغيلي',
        'recurring' => 'متكرر',
        'one_time' => 'لمرة واحدة',
    ],

    'type_descriptions' => [
        'event' => 'مصروفات مرتبطة مباشرة بفعالية أو حجز معين (تموين، ديكور، موظفين)',
        'operational' => 'التكاليف التشغيلية اليومية لإدارة الأعمال',
        'recurring' => 'مصروفات تتكرر بشكل منتظم (إيجار، كهرباء، اشتراكات)',
        'one_time' => 'مصروفات لمرة واحدة (شراء معدات، إصلاحات كبرى)',
    ],

    'payment_methods' => [
        'cash' => 'نقداً',
        'bank_transfer' => 'تحويل بنكي',
        'card' => 'بطاقة',
        'cheque' => 'شيك',
        'other' => 'أخرى',
    ],

    'payment_statuses' => [
        'pending' => 'في الانتظار',
        'paid' => 'مدفوع',
        'partial' => 'مدفوع جزئياً',
        'cancelled' => 'ملغي',
    ],

    'statuses' => [
        'draft' => 'مسودة',
        'submitted' => 'تم الإرسال',
        'approved' => 'معتمد',
        'rejected' => 'مرفوض',
        'archived' => 'مؤرشف',
    ],

    'recurring_frequencies' => [
        'daily' => 'يومي',
        'weekly' => 'أسبوعي',
        'monthly' => 'شهري',
        'quarterly' => 'ربع سنوي',
        'yearly' => 'سنوي',
    ],

    'sections' => [
        'information' => 'معلومات المصروف',
        'information_desc' => 'أدخل التفاصيل الأساسية للمصروف',
        'description' => 'الوصف',
        'financial' => 'التفاصيل المالية',
        'vendor' => 'معلومات المورد',
        'recurring' => 'إعدادات التكرار',
        'attachments' => 'المرفقات',
        'notes' => 'ملاحظات',
    ],

    'fields' => [
        'expense_number' => 'رقم المصروف',
        'expense_type' => 'نوع المصروف',
        'event' => 'الفعالية',
        'event_helper' => 'اختر الفعالية المرتبطة بهذا المصروف (اختياري)',
        'booking' => 'الحجز',
        'booking_helper' => 'ربط هذا المصروف بحجز معين',
        'category' => 'الفئة',
        'title_en' => 'العنوان بالإنجليزية',
        'title_ar' => 'العنوان بالعربية',
        'description_en' => 'الوصف بالإنجليزية',
        'description_ar' => 'الوصف بالعربية',
        'amount' => 'المبلغ (ريال عماني)',
        'tax_amount' => 'الضريبة (ريال عماني)',
        'total' => 'المجموع',
        'expense_date' => 'تاريخ المصروف',
        'payment_method' => 'طريقة الدفع',
        'payment_status' => 'حالة الدفع',
        'payment_reference' => 'رقم المرجع',
        'due_date' => 'تاريخ الاستحقاق',
        'vendor_name' => 'اسم المورد',
        'vendor_phone' => 'هاتف المورد',
        'vendor_email' => 'بريد المورد',
        'is_recurring' => 'مصروف متكرر',
        'recurring_frequency' => 'التكرار',
        'recurring_start_date' => 'تاريخ البداية',
        'recurring_end_date' => 'تاريخ النهاية',
        'recurring_end_date_helper' => 'اتركه فارغاً للتكرار بلا نهاية',
        'attachments' => 'إيصالات ومستندات',
        'attachments_helper' => 'يمكنك رفع صور أو ملفات PDF (حد أقصى 5 ميجابايت لكل ملف)',
        'notes' => 'ملاحظات داخلية',
        'status' => 'الحالة',
    ],

    'columns' => [
        'number' => 'الرقم',
        'title' => 'العنوان',
        'type' => 'النوع',
        'category' => 'الفئة',
        'event' => 'الفعالية',
        'booking' => 'الحجز',
        'amount' => 'المبلغ',
        'date' => 'التاريخ',
        'payment' => 'الدفع',
        'method' => 'الطريقة',
        'vendor' => 'المورد',
        'status' => 'الحالة',
        'created_at' => 'تاريخ الإنشاء',
    ],

    'filters' => [
        'type' => 'النوع',
        'category' => 'الفئة',
        'event' => 'الفعالية',
        'payment_status' => 'حالة الدفع',
        'payment_method' => 'طريقة الدفع',
        'status' => 'الحالة',
        'date_from' => 'من',
        'date_until' => 'إلى',
        'has_booking' => 'مرتبط بحجز',
    ],

    'actions' => [
        'mark_paid' => 'تم الدفع',
        'bulk_mark_paid' => 'تحديد كمدفوع',
        'approve' => 'اعتماد',
        'reject' => 'رفض',
        'create_first' => 'إنشاء أول مصروف',
    ],

    'notifications' => [
        'marked_paid' => 'تم تحديد المصروف كمدفوع',
        'approved' => 'تم اعتماد المصروف',
        'rejected' => 'تم رفض المصروف',
        'cannot_delete' => 'لا يمكن الحذف',
        'has_children' => 'هذا المصروف له مصروفات فرعية متكررة.',
    ],

    'empty_state' => [
        'heading' => 'لا توجد مصروفات بعد',
        'description' => 'تتبع التكاليف التشغيلية ومصروفات الفعاليات والفواتير المتكررة هنا.',
    ],
];
