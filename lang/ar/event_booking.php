<?php

return [

    // ── Language switcher ─────────────────────────────────────────────────────
    'switch_to_arabic' => 'العربية',
    'switch_to_english' => 'English',

    // ── Progress stepper ──────────────────────────────────────────────────────
    'steps' => [
        'date'    => 'التاريخ',
        'time'    => 'الوقت',
        'tickets' => 'التذاكر',
        'extras'  => 'إضافات',
        'details' => 'التفاصيل',
        'payment' => 'الدفع',
    ],

    // ── Common actions ────────────────────────────────────────────────────────
    'back'     => 'رجوع',
    'continue' => 'متابعة',

    // ── Step 1 — Select Date ──────────────────────────────────────────────────
    'step1' => [
        'heading'       => 'اختر التاريخ',
        'subheading'    => 'اختر التاريخ المناسب للفعالية',
        'no_dates'      => 'لا توجد تواريخ متاحة',
        'no_dates_body' => 'لا توجد مواعيد قادمة لهذه الفعالية.',
    ],

    // ── Step 2 — Choose Time Slot ─────────────────────────────────────────────
    'step2' => [
        'heading'         => 'اختر الفترة الزمنية',
        'subheading'      => 'الفترات المتاحة ليوم',
        'full'            => 'ممتلئ',
        'almost_full'     => 'يكاد يمتلئ',
        'available'       => 'متاح',
        'spots_remaining' => 'مقاعد متبقية',
    ],

    // ── Step 3 — Tickets ──────────────────────────────────────────────────────
    'step3' => [
        'heading'           => 'اختر تذاكرك',
        'subheading'        => 'حدد أنواع التذاكر والكمية المطلوبة',
        'n_selected'        => 'تم اختيار :n',
        'sold_out'          => 'نفذت التذاكر',
        'tickets_available' => 'تذكرة متاحة',
        'per_ticket'        => 'للتذكرة',
        'subtotal'          => 'المجموع الجزئي',
        'running_total'     => 'المجموع الحالي',
        'n_tickets'         => '(:n تذكرة)',
        'min_tickets'       => 'اختر :n تذكرة على الأقل للمتابعة',
    ],

    // ── Step 4 — Extra Services ───────────────────────────────────────────────
    'step4' => [
        'heading'    => 'أضف خدمات إضافية',
        'subheading' => 'عزّز تجربتك بخيارات إضافية اختيارية',
        'no_extras'  => 'لا توجد خدمات إضافية لهذه الفعالية.',
        'available'  => 'متاح',
        'per_ticket' => 'للتذكرة',
    ],

    // ── Step 5 — Attendee Details ─────────────────────────────────────────────
    'step5' => [
        'heading'                => 'بيانات الحضور',
        'subheading'             => 'أدخل بيانات كل حامل تذكرة',
        'copy_contact'           => 'تطبيق بيانات الحاضر الأول على جميع الحضور',
        'copy_contact_hint'      => 'سيتم نسخها من الحاضر الأول',
        'attendee_n'             => 'الحاضر :n',
        'primary'                => 'رئيسي',
        'first_name'             => 'الاسم الأول',
        'first_name_placeholder' => 'محمد',
        'last_name'              => 'اسم العائلة',
        'last_name_placeholder'  => 'الحمداني',
        'email_address'          => 'البريد الإلكتروني',
        'email_hint'             => 'ستُرسل التذكرة التأكيدية إلى هذا البريد',
        'phone_number'           => 'رقم الهاتف',
        'date_of_birth'          => 'تاريخ الميلاد',
        'date_of_birth_max_age'  => 'لا يمكن أن يتجاوز عمر الحاضر :age سنة.',
        'date_of_birth_future'   => 'لا يمكن أن يكون تاريخ الميلاد في المستقبل.',
        'gender'                 => 'الجنس',
        'select_gender'          => 'اختر الجنس',
        'male'                   => 'ذكر',
        'female'                 => 'أنثى',
        'nationality'            => 'الجنسية',
        'nationality_no_results' => 'لم يتم العثور على دول',
        'terms_heading'          => 'الشروط والأحكام',
        'terms_agree'            => 'لقد قرأت وأوافق على',
        'terms_required'         => 'يجب الموافقة على الشروط والأحكام للمتابعة.',
        'continue_to_payment'    => 'الانتقال إلى الدفع',
        'validating'             => 'جارٍ التحقق...',
        'booking_summary'        => 'ملخص الحجز',
        'email_label'            => 'البريد الإلكتروني',
        'phone_label'            => 'الهاتف',
    ],

    // ── Step 6 — Payment ──────────────────────────────────────────────────────
    'step6' => [
        'heading'               => 'الدفع',
        'subheading'            => 'راجع طلبك وأكمل عملية الدفع',
        'thawani_title'         => 'ثواني',
        'thawani_subtitle'      => 'دفع آمن عبر الإنترنت مدعوم من ثواني',
        'thawani_redirect_note' => 'سيتم تحويلك إلى صفحة الدفع الآمنة لإتمام عملية الشراء.',
        'pay_at_door_title'     => 'الدفع عند الباب',
        'pay_at_door_subtitle'  => 'ادفع نقدًا عند وصولك إلى الفعالية',
        'free_title'            => 'دخول مجاني',
        'free_subtitle'         => 'لا يتطلب هذا الحدث أي رسوم',
        'pay_thawani_btn'       => 'الدفع عبر ثواني',
        'redirecting_thawani'   => 'جارٍ التحويل إلى ثواني...',
        'nbo_title'             => 'الدفع الموحّد - البنك الوطني العماني',
        'nbo_subtitle'          => 'دفع آمن عبر الإنترنت مدعوم من البنك الوطني العُماني',
        'nbo_redirect_note'     => 'سيتم تحويلك إلى صفحة الدفع الآمنة لإتمام عملية الشراء.',
        'pay_nbo_btn'           => 'الدفع الآن',
        'redirecting_nbo'       => 'جارٍ التحويل إلى NBO...',
        'confirm_booking'       => 'تأكيد الحجز',
        'processing'            => 'جارٍ المعالجة...',
        'order_summary'         => 'ملخص الطلب',
        'secured_by_thawani'    => 'مؤمَّن بواسطة ثواني',
        'secured_by_nbo'        => 'مؤمَّن بواسطة NBO',
    ],

    // ── Shared summary sidebar ────────────────────────────────────────────────
    'summary' => [
        'event'          => 'الفعالية',
        'date'           => 'التاريخ',
        'time'           => 'الوقت',
        'tickets'        => 'التذاكر',
        'extra_services' => 'الخدمات الإضافية',
        'attendees'      => 'الحضور',
        'total'          => 'الإجمالي',
    ],

];
