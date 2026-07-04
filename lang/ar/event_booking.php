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

    // ── Unavailable / maintenance mode ────────────────────────────────────────
    'unavailable' => [
        'heading'           => 'قيد الصيانة',
        'draft_message'     => 'هذه الفعالية قيد التحضير وغير متاحة للحجز حالياً. يرجى المحاولة مرة أخرى قريباً.',
        'cancelled_message' => 'تم إلغاء هذه الفعالية ولم تعد متاحة للحجز.',
        'back_home'         => 'العودة إلى الصفحة الرئيسية',
    ],

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
        'free'              => 'مجاني',
        'subtotal'          => 'المجموع الجزئي',
        'running_total'     => 'المجموع الحالي',
        'n_tickets'         => '(:n تذكرة)',
        'min_tickets'         => 'اختر :n تذكرة على الأقل للمتابعة',
        'dependency_required' => ':child تتطلب :parent — يرجى إضافة تذكرة :parent أولاً.',
        'requires_parent'     => 'تتطلب :parent',
        'add_parent_first'    => 'أضف :parent أولاً',
        'slot_limit_reached'  => 'لا توجد سعة متبقية في هذا الموعد لإضافة تذاكر أخرى.',
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
        'heading'                => 'بيانات الزوار',
        'subheading'             => 'أدخل بيانات كل حامل تذكرة',
        'copy_contact'           => 'تطبيق بيانات الزائر الأول على جميع الزوار',
        'copy_contact_hint'      => 'سيتم نسخها من الزائر الأول',
        'attendee_n'             => 'الزائر :n',
        'primary'                => 'رئيسي',
        'first_name'             => 'الاسم الأول',
        'first_name_placeholder' => 'محمد',
        'last_name'              => 'اسم العائلة',
        'last_name_placeholder'  => 'الحمداني',
        'email_address'          => 'البريد الإلكتروني',
        'email_hint'             => 'ستُرسل التذكرة التأكيدية إلى هذا البريد',
        'phone_number'           => 'رقم الهاتف',
        'phone_invalid'          => 'يجب أن يكون رقم الهاتف من 7 إلى 15 رقمًا، ويمكن أن يبدأ بعلامة +.',
        'date_of_birth'          => 'تاريخ الميلاد',
        'date_of_birth_invalid'  => 'يرجى إدخال تاريخ ميلاد صحيح.',
        'date_of_birth_max_age'  => 'لا يمكن أن يتجاوز عمر الزائر :age سنة.',
        'date_of_birth_future'   => 'لا يمكن أن يكون تاريخ الميلاد في المستقبل.',
        'gender'                 => 'الجنس',
        'select_gender'          => 'اختر الجنس',
        'male'                   => 'ذكر',
        'female'                 => 'أنثى',
        'nationality'            => 'الجنسية',
        'nationality_no_results' => 'لم يتم العثور على دول',
        'identity_number'        => 'رقم الهوية',
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
        'attendees'      => 'الزوار',
        'total'          => 'الإجمالي',
    ],

    // ── Success page ──────────────────────────────────────────────────────────
    'success' => [
        'title'              => 'تم تأكيد الحجز',
        'heading'            => 'تم تأكيد الحجز!',
        'subheading'         => 'تم حجز مكانك',
        'booking_reference'  => 'رقم الحجز',
        'event'              => 'الفعالية',
        'date'               => 'التاريخ',
        'time'               => 'الوقت',
        'ticket_type'        => 'نوع التذكرة',
        'quantity'           => 'الكمية',
        'ticket_unit'        => 'تذكرة/تذاكر',
        'total_paid'         => 'الإجمالي المدفوع',
        'confirmation_email' => 'تم إرسال بريد تأكيد إلى',
        'present_qr'         => 'يرجى إبراز رمز QR هذا عند الدخول',
        'print_ticket'       => 'طباعة التذكرة',
        'book_again'         => 'حجز جديد',
        'back_to_events'     => 'العودة إلى الفعاليات',
    ],

    // ── Booking confirmation email ────────────────────────────────────────────
    'email' => [
        'subject'         => 'تأكيد الحجز - :reference',
        'confirmed'       => 'تم تأكيد الحجز!',
        'reference'       => 'الرقم المرجعي: :reference',
        'dear'            => 'عزيزي :name،',
        'valued_customer' => 'عميلنا الكريم',
        'details_below'   => 'تم تأكيد حجزك. يرجى الاطلاع على التفاصيل أدناه:',
        'event_details'   => 'تفاصيل الفعالية',
        'event'           => 'الفعالية',
        'location'        => 'الموقع',
        'date'            => 'التاريخ',
        'time'            => 'الوقت',
        'ticket_type'     => 'نوع التذكرة',
        'quantity'        => 'الكمية',
        'extra_services'  => 'الخدمات الإضافية',
        'total_amount'    => 'المبلغ الإجمالي',
        'qr_heading'      => 'رمز QR الخاص بتذكرتك',
        'qr_notice'       => 'يرجى إبراز رمز QR هذا عند مدخل الفعالية.',
        'support'         => 'إذا كان لديك أي استفسار، يرجى التواصل مع فريق الدعم.',
        'thank_you'       => 'شكرًا لحجزك معنا!',

        // Individual ticket email
        'ticket_subject'        => 'تذكرة الفعالية الخاصة بك - :ticket_number',
        'ticket_heading'        => 'تذكرة الفعالية الخاصة بك',
        'ticket_hello'          => 'مرحباً :name!',
        'ticket_intro'          => 'شكراً لحجزك! تذكرتك جاهزة. يرجى الاطلاع على تفاصيل تذكرتك أدناه:',
        'ticket_number'         => 'رقم التذكرة',
        'ticket_attendee'       => 'الحاضر',
        'ticket_extra_services' => 'الخدمات الإضافية المشمولة',
        'ticket_qr_heading'     => 'رمز QR الخاص بك',
        'ticket_qr_important'   => 'مهم',
        'ticket_qr_notice'      => 'يرجى إبراز رمز QR هذا عند مدخل الفعالية.',
        'ticket_attachments'    => 'المرفقات',
        'ticket_pdf_label'      => 'تذكرة PDF (ticket-:number.pdf)',
        'ticket_qr_label'       => 'رمز QR (qr-code-:number.png)',
        'ticket_see_you'        => 'نتطلع إلى رؤيتك في الفعالية!',
        'ticket_reference'      => 'رقم الحجز',
        'ticket_automated'      => 'هذا بريد إلكتروني آلي. يرجى عدم الرد على هذه الرسالة.',
        'ticket_support'        => 'إذا كان لديك أي استفسار، يرجى التواصل مع فريق دعم الفعالية.',
        'ticket_all_rights'     => 'جميع الحقوق محفوظة.',

        // Combined tickets email (all attendees, sent to the first attendee)
        'tickets_subject'      => 'تذاكر الفعالية الخاصة بك - :reference',
        'tickets_heading'      => 'تذاكر الفعالية الخاصة بك',
        'tickets_intro'        => 'شكراً لحجزك! جميع التذاكر (:count) الخاصة بحجزك جاهزة. يرجى الاطلاع على التفاصيل والمرفقات أدناه:',
        'tickets_list_heading' => 'التذاكر',
    ],

    // ── Individual ticket PDF ─────────────────────────────────────────────────
    'ticket' => [
        'event_ticket'      => 'تذكرة الفعالية',
        'scan_to_verify'    => 'امسح للتحقق',
        'attendee'          => 'الحاضر',
        'name'              => 'الاسم',
        'email'             => 'البريد الإلكتروني',
        'phone'             => 'الهاتف',
        'ticket_number'     => 'رقم التذكرة',
        'ticket_type'       => 'نوع التذكرة',
        'event_details'     => 'تفاصيل الفعالية',
        'date'              => 'التاريخ',
        'time'              => 'الوقت',
        'location'          => 'الموقع',
        'organizer'         => 'المنظم',
        'extra_services'    => 'الخدمات الإضافية',
        'quantity'          => 'الكمية',
        'entry_pass'        => 'بطاقة الدخول',
        'present_qr'        => 'يرجى إبراز رمز QR هذا عند مدخل الفعالية للتسجيل.',
        'present_barcode'   => 'يرجى إبراز هذا الباركود عند مدخل الفعالية للتسجيل.',
        'booking_reference' => 'رقم الحجز',
        'booked_on'         => 'تم الحجز في',
        'support_note'      => 'لأي استفسارات، يرجى التواصل مع فريق دعم الفعالية.',
        'all_rights'        => 'جميع الحقوق محفوظة.',
    ],

];
