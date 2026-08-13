<?php

return [

    'navigation' => [
        'group' => 'إدارة الحجوزات',
        'label' => 'إعداد عمولة',
        'plural' => 'إعدادات العمولة',
    ],

    'types' => [
        'percentage' => 'نسبة مئوية',
        'fixed' => 'مبلغ ثابت',
    ],

    'sections' => [
        'scope' => 'نطاق العمولة',
        'scope_desc' => 'اترك حقل الفعالية فارغًا لتعيين النسبة العامة (على مستوى المنصة).',
        'details' => 'تفاصيل العمولة',
        'validity' => 'فترة السريان',
    ],

    'fields' => [
        'event' => 'الفعالية',
        'event_helper' => 'تُطبّق فقط على حجوزات هذه الفعالية. اتركها فارغة للنسبة العامة.',
        'scope_note' => 'النسب الخاصة بفعالية معينة لها الأولوية على النسبة العامة عند سريان كلتيهما في نفس التاريخ.',
        'name_en' => 'الاسم بالإنجليزية',
        'name_ar' => 'الاسم بالعربية',
        'commission_type' => 'نوع العمولة',
        'commission_value' => 'قيمة العمولة',
        'description_en' => 'الوصف بالإنجليزية',
        'description_ar' => 'الوصف بالعربية',
        'effective_from' => 'ساري من',
        'effective_from_helper' => 'اتركه فارغًا للتطبيق الفوري.',
        'effective_to' => 'ساري حتى',
        'effective_to_helper' => 'اتركه فارغًا لعدم وجود تاريخ انتهاء.',
        'is_active' => 'نشط',
    ],

    'columns' => [
        'scope' => 'النطاق',
        'global' => 'عام',
        'event_specific' => 'الفعالية: :name',
        'name' => 'الاسم',
        'type' => 'النوع',
        'value' => 'النسبة',
        'effective_from' => 'ساري من',
        'effective_to' => 'ساري حتى',
        'indefinite' => 'غير محدد',
        'is_active' => 'نشط',
        'created_at' => 'تاريخ الإنشاء',
    ],

    'filters' => [
        'active' => 'نشط',
        'scope_type' => 'نوع النطاق',
        'event_specific' => 'خاص بفعالية',
    ],

];
