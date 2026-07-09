<?php

return [

    'navigation' => [
        'group'  => 'إدارة الأكشاك',
        'label'  => 'بطاقة محفظة',
        'plural' => 'بطاقات المحفظة',
    ],

    'sections' => [
        'information'      => 'معلومات البطاقة',
        'information_desc' => 'يتم قراءة معرف البطاقة عن طريق تمريرها على القارئ، أو إدخاله يدويًا.',
        'balance'           => 'الرصيد',
        'balance_desc'      => 'الرصيد الابتدائي للبطاقة الجديدة. استخدم إجراء "شحن الرصيد" لإضافة أموال لاحقًا.',
    ],

    'fields' => [
        'uid'          => 'معرف البطاقة',
        'uid_helper'   => 'المعرف الفريد الذي يظهر عند تمرير البطاقة على قارئ ACR122U.',
        'holder_name'  => 'اسم الحامل',
        'phone'        => 'الهاتف',
        'balance'      => 'الرصيد',
        'status'       => 'الحالة',
        'notes'        => 'ملاحظات',
    ],

    'statuses' => [
        'active'  => 'مفعلة',
        'blocked' => 'محظورة',
    ],

    'columns' => [
        'uid'                => 'المعرف',
        'holder_name'        => 'الحامل',
        'phone'              => 'الهاتف',
        'balance'            => 'الرصيد',
        'status'             => 'الحالة',
        'transactions_count' => 'المعاملات',
        'created_at'         => 'تاريخ الإصدار',
    ],

    'actions' => [
        'new'    => 'بطاقة جديدة',
        'top_up' => 'شحن الرصيد',
        'block'  => 'حظر',
        'unblock' => 'إلغاء الحظر',
    ],

    'top_up_modal' => [
        'heading'     => 'شحن رصيد البطاقة',
        'description' => 'يضيف رصيدًا إلى هذه البطاقة ويسجل المعاملة.',
        'amount'      => 'المبلغ المراد إضافته',
        'reference'   => 'المرجع',
        'notes'       => 'ملاحظات',
    ],

    'notifications' => [
        'created'        => 'تم تسجيل البطاقة.',
        'updated'        => 'تم تحديث البطاقة.',
        'topped_up'      => 'تم شحن رصيد البطاقة.',
        'status_updated' => 'تم تحديث حالة البطاقة.',
        'card_blocked'   => 'تم حظر البطاقة.',
        'card_unblocked' => 'تم إلغاء حظر البطاقة.',
        'insufficient_for_block' => 'لا يمكن شحن بطاقة محظورة. قم بإلغاء الحظر أولاً.',
    ],

    'transactions' => [
        'title' => 'سجل المعاملات',
        'fields' => [
            'date'          => 'التاريخ',
            'type'          => 'النوع',
            'amount'        => 'المبلغ',
            'balance_after' => 'الرصيد بعد',
            'kiosk'         => 'الكشك',
            'booking'       => 'الحجز',
            'recorded_by'   => 'سجلها',
            'reference'     => 'المرجع',
            'notes'         => 'ملاحظات',
        ],
        'types' => [
            'topup'      => 'شحن رصيد',
            'payment'    => 'دفع',
            'refund'     => 'استرداد',
            'adjustment' => 'تعديل',
        ],
        'self_service' => 'ذاتي (كشك)',
        'total' => 'الصافي الإجمالي',
    ],

    'empty_state' => [
        'heading'     => 'لا توجد بطاقات بعد',
        'description' => 'سجل بطاقة للبدء بشحن الأرصدة للعملاء.',
    ],
];
