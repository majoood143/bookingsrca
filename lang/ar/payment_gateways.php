<?php

return [

    'title' => 'بوابات الدفع',
    'save' => 'حفظ الإعدادات',

    'sections' => [
        'active_gateway' => 'بوابة الدفع النشطة',
        'active_gateway_desc' => 'اختر بوابة الدفع المستخدمة عند إتمام الحجز.',
        'thawani' => 'إعدادات ثواني',
        'thawani_desc' => 'بيانات اعتماد بوابة الدفع ثواني (Thawani).',
        'nbo' => 'إعدادات NBO',
        'nbo_desc' => 'بيانات اعتماد بوابة الدفع الموحدة لبنك مسقط الوطني (NBO).',
        'ccavenue' => 'إعدادات CCAvenue',
        'ccavenue_desc' => 'بيانات اعتماد بوابة الدفع CCAvenue (بنك مسقط).',
    ],

    'options' => [
        'active_gateway' => 'بوابة الدفع النشطة',
        'free' => 'مجاني (بدون دفع)',
        'cash' => 'نقدًا / الدفع عند الباب',
        'thawani' => 'ثواني',
        'nbo' => 'NBO الدفع الموحد',
        'ccavenue' => 'CCAvenue (بنك مسقط)',
    ],

    'fields' => [
        'test_mode' => 'وضع التجربة',
        'thawani_test_mode_helper' => 'عند التفعيل، تُرسل الطلبات إلى uatcheckout.thawani.om بدلاً من الرابط الفعلي.',
        'secret_key' => 'المفتاح السري',
        'publishable_key' => 'مفتاح النشر',
        'base_url_override' => 'تجاوز الرابط الأساسي',
        'url_override_helper' => 'اتركه فارغًا لاستخدام الرابط الافتراضي بناءً على إعداد وضع التجربة.',
        'webhook_secret' => 'مفتاح Webhook السري',
        'webhook_secret_helper' => 'مفتاح HMAC-SHA256 اختياري يُستخدم للتحقق من أحداث Webhook الواردة من ثواني.',

        'nbo_test_mode_helper' => 'عند التفعيل، تُرسل الطلبات إلى بيئة الاختبار الخاصة بـ NBO بدلاً من الرابط الفعلي.',
        'tranportal_id' => 'معرّف Tranportal',
        'tranportal_password' => 'كلمة مرور Tranportal',
        'resource_key' => 'مفتاح المورد',
        'resource_key_helper' => 'مفتاح تشفير AES يوفره NBO. يجب أن يتكون من 16 حرفًا بالضبط.',
        'endpoint_url_override' => 'تجاوز رابط نقطة النهاية',

        'ccavenue_test_mode_helper' => 'عند التفعيل، تُرسل الطلبات إلى بيئة الاختبار الخاصة بـ CCAvenue بدلاً من الرابط الفعلي.',
        'merchant_id' => 'معرّف التاجر',
        'access_code' => 'رمز الوصول',
        'working_key' => 'مفتاح التشغيل',
        'working_key_helper' => 'مفتاح تشفير AES-256 يوفره بنك مسقط/CCAvenue.',
        'ccavenue_endpoint_url_helper' => 'اتركه فارغًا لاستخدام الرابط الافتراضي المؤقت؛ يجب تعديله بالرابط الفعلي لمعاملات بنك مسقط/CCAvenue قبل التشغيل الفعلي.',
    ],

    'notifications' => [
        'saved' => 'تم حفظ إعدادات بوابة الدفع.',
    ],

];
