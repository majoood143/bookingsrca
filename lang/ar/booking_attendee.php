<?php

return [

    'navigation' => [
        'group' => 'إدارة الحجوزات',
        'label' => 'الزائر',
        'plural' => 'الزوار',
    ],

    'sections' => [
        'attendee_info'   => 'معلومات الزائر',
        'booking_info'    => 'معلومات الحجز',
        'ticket_info'     => 'معلومات التذكرة',
        'extra_services'  => 'الخدمات الإضافية',
    ],

    'fields' => [
        'full_name'         => 'الاسم الكامل',
        'first_name'        => 'الاسم الأول',
        'last_name'         => 'اسم العائلة',
        'email'             => 'البريد الإلكتروني',
        'phone'             => 'رقم الهاتف',
        'date_of_birth'     => 'تاريخ الميلاد',
        'gender'            => 'الجنس',
        'nationality'       => 'الجنسية',
        'identity_number'   => 'رقم الهوية',
        'ticket_number'     => 'رقم التذكرة',
        'ticket_type'       => 'نوع التذكرة',
        'ticket_price'      => 'سعر التذكرة',
        'email_sent'        => 'تم إرسال البريد',
        'email_sent_at'     => 'تاريخ إرسال البريد',
        'checked_in'        => 'تم تسجيل الدخول',
        'checked_in_at'     => 'وقت تسجيل الدخول',
        'booking_reference' => 'رقم مرجع الحجز',
        'event'             => 'الفعالية',
        'event_date'        => 'تاريخ الفعالية',
        'time_slot'         => 'الوقت',
        'booking_status'    => 'حالة الحجز',
        'service_name'      => 'الخدمة',
        'service_quantity'  => 'الكمية',
        'service_price'     => 'السعر',
        'qr_code'           => 'رمز QR',
        'created_at'        => 'تاريخ التسجيل',
    ],

    'columns' => [
        'name'              => 'الاسم',
        'email'             => 'البريد الإلكتروني',
        'ticket_number'     => 'رقم التذكرة',
        'event'             => 'الفعالية',
        'event_date'        => 'التاريخ',
        'ticket_type'       => 'نوع التذكرة',
        'email_sent'        => 'البريد',
        'checked_in'        => 'تسجيل الدخول',
        'booking_reference' => 'رقم الحجز',
        'booking_status'    => 'حالة الحجز',
        'created_at'        => 'تاريخ التسجيل',
        'phone'             => 'رقم الهاتف',
    ],

    'actions' => [
        'resend_ticket'     => 'إعادة إرسال التذكرة',
        'download_ticket'   => 'تحميل التذكرة',
        'print_ticket'      => 'طباعة التذكرة',
        'check_in'          => 'تسجيل الدخول',
        'undo_check_in'     => 'إلغاء تسجيل الدخول',
        'view_booking'      => 'عرض الحجز',
    ],

    'tabs' => [
        'all'            => 'جميع الزوار',
        'checked_in'     => 'تم تسجيل دخولهم',
        'not_checked_in' => 'لم يسجلوا دخولهم',
        'email_sent'     => 'تم إرسال البريد',
        'email_pending'  => 'البريد معلق',
    ],

    'filters' => [
        'event'          => 'الفعالية',
        'booking_status' => 'حالة الحجز',
        'checked_in'     => 'حالة تسجيل الدخول',
        'email_sent'     => 'حالة البريد الإلكتروني',
    ],

    'notifications' => [
        'ticket_resent'             => 'تم إعادة إرسال التذكرة',
        'ticket_resent_body'        => 'تم إرسال التذكرة إلى :email',
        'ticket_resend_failed'      => 'فشل الإرسال',
        'ticket_resend_failed_body' => 'تعذّر إعادة إرسال التذكرة. يرجى التحقق من البريد الإلكتروني.',
        'checked_in'                => 'تم تسجيل دخول الزائر',
        'checked_in_body'           => 'تم تسجيل دخول :name بنجاح.',
        'check_in_undone'           => 'تم إلغاء تسجيل الدخول',
        'check_in_undone_body'      => 'تم إلغاء تسجيل دخول :name.',
    ],

    'modals' => [
        'resend_heading'       => 'إعادة إرسال التذكرة',
        'resend_description'   => 'سيتم إعادة إرسال التذكرة إلى :email. هل تريد المتابعة؟',
        'resend_submit'        => 'نعم، أعد الإرسال',
        'check_in_heading'     => 'تسجيل دخول الزائر',
        'check_in_description' => 'تأكيد تسجيل دخول :name؟',
        'check_in_submit'      => 'تسجيل الدخول',
    ],

    'tooltips' => [
        'resend_ticket'   => 'إعادة إرسال بريد التذكرة لهذا الزائر',
        'download_ticket' => 'تحميل تذكرة PDF',
        'check_in'        => 'تسجيل دخول الزائر',
    ],

    'no_extra_services' => 'لا توجد خدمات إضافية محجوزة.',
    'no_qr_code'        => 'لم يتم إنشاء رمز QR بعد.',
    'no_pdf'            => 'لم يتم إنشاء تذكرة PDF بعد.',

];
