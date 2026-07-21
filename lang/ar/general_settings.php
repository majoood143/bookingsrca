<?php

return [

    'title' => 'الإعدادات العامة',
    'save' => 'حفظ الإعدادات',

    'tabs' => [
        'general' => 'عام',
        'branding' => 'الهوية والمظهر',
        'booking_rules' => 'قواعد الحجز',
        'attendee_fields' => 'حقول الحضور',
        'terms' => 'الشروط والأحكام',
        'modules' => 'الوحدات',
    ],

    'sections' => [
        'site_identity' => 'هوية الموقع',
        'site_identity_desc' => 'الاسم الظاهر في الترويسة وعنوان المتصفح والمراسلات.',
        'localization' => 'الإعدادات المحلية',
        'localization_desc' => 'المنطقة الزمنية والعملة المستخدمة في النظام.',
        'logos' => 'الشعارات والأيقونات',
        'logos_desc' => 'الصور المستخدمة في هوية موقع الحجز العام ولوحة التحكم.',
        'colors' => 'ألوان المظهر',
        'colors_desc' => 'الألوان المستخدمة في موقع الحجز العام ولوحة التحكم.',
        'booking_rules' => 'قواعد الحجز',
        'attendee_fields' => 'حقول الحضور',
        'attendee_fields_desc' => 'اختر الحقول التي تظهر في نموذج الحجز.',
        'modules' => 'الوحدات',
        'modules_desc' => 'تفعيل أو تعطيل الميزات الاختيارية في النظام.',
    ],

    'fields' => [
        'site_name_en' => 'اسم الموقع (إنجليزي)',
        'site_name_ar' => 'اسم الموقع (عربي)',
        'timezone' => 'المنطقة الزمنية',
        'currency_code' => 'العملة',
        'currency_symbol' => 'رمز العملة',
        'currency_icon' => 'أيقونة العملة (SVG)',
        'currency_icon_helper' => 'أيقونة SVG اختيارية تُستخدم بدلاً من نص رمز العملة في صفحة الحجز.',

        'site_logo' => 'شعار الموقع العام',
        'site_logo_helper' => 'يظهر في ترويسة موقع الحجز العام.',
        'app_logo' => 'شعار لوحة التحكم',
        'app_logo_helper' => 'يظهر في القائمة الجانبية للوحة تحكم المسؤول.',
        'favicon' => 'أيقونة المتصفح',
        'favicon_helper' => 'الأيقونة الظاهرة في تبويب المتصفح.',
        'primary_color' => 'اللون الأساسي للموقع العام',
        'primary_color_helper' => 'يستخدم في الأزرار والعناصر البارزة في الموقع العام.',
        'secondary_color' => 'اللون الثانوي للموقع العام',
        'secondary_color_helper' => 'يستخدم في حالات التحويم والتدرجات في الموقع العام.',
        'panel_primary_color' => 'لون لوحة التحكم',
        'panel_primary_color_helper' => 'اللون الأساسي المستخدم في جميع أنحاء لوحة تحكم المسؤول.',

        'min_tickets_per_booking' => 'الحد الأدنى للتذاكر لكل حجز',
        'max_tickets_per_booking' => 'الحد الأقصى للتذاكر لكل حجز',
        'max_attendee_age_years' => 'الحد الأقصى لعمر الحاضر (سنوات)',
        'pending_booking_expiry_minutes' => 'انتهاء صلاحية الحجز المعلق (دقائق)',
        'pending_booking_expiry_minutes_helper' => 'يتم إلغاء الحجوزات غير المدفوعة تلقائيًا بعد هذا العدد من الدقائق.',
        'show_slot_end_time' => 'إظهار وقت انتهاء الفترة الزمنية',
        'show_slot_end_time_helper' => 'عند إيقافه، يظهر وقت البدء فقط للفترات الزمنية في صفحتي الحجز والكشك (مثال: "09:00" بدلاً من "09:00 - 10:00").',

        'show_email' => 'إظهار حقل البريد الإلكتروني',
        'show_phone' => 'إظهار حقل رقم الجوال',
        'show_date_of_birth' => 'إظهار حقل تاريخ الميلاد',
        'show_gender' => 'إظهار حقل الجنس',
        'show_nationality' => 'إظهار حقل الجنسية',
        'show_identity_number' => 'إظهار حقل رقم الهوية',

        'terms_en' => 'الشروط والأحكام (إنجليزي)',
        'terms_ar' => 'الشروط والأحكام (عربي)',

        'module_kiosk_enabled' => 'وحدة تسجيل الدخول عبر الكشك',
        'module_kiosk_enabled_helper' => 'يفعّل وحدة تسجيل الدخول الذاتي عبر الكشك وقائمتها في التنقل.',
        'module_extra_services_enabled' => 'وحدة الخدمات الإضافية',
        'module_extra_services_enabled_helper' => 'يفعّل الخدمات الإضافية التي يمكن إضافتها إلى الحجوزات.',
        'module_private_events_enabled' => 'وحدة الفعاليات الخاصة',
        'module_private_events_enabled_helper' => 'يسمح بإنشاء فعاليات خاصة محمية بكلمة مرور.',
        'module_promo_codes_enabled' => 'وحدة رموز الخصم',
        'module_promo_codes_enabled_helper' => 'يسمح للعملاء بتطبيق رموز الخصم للحصول على خصم عند الدفع.',
        'module_expenses_enabled' => 'وحدة المصروفات',
        'module_expenses_enabled_helper' => 'يفعّل تتبع المصروفات وفئات المصروفات، وتبويب المصروفات داخل الحجوزات.',
    ],

    'notifications' => [
        'updated' => 'تم تحديث الإعدادات بنجاح.',
    ],

];
