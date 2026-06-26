<?php

return [

    'navigation' => [
        'label'  => 'إعدادات الحجز',
        'group'  => 'الإعدادات',
        'plural' => 'إعدادات الحجز',
    ],

    'sections' => [
        'configuration' => 'تهيئة الإعداد',
    ],

    'fields' => [
        'key'         => 'مفتاح الإعداد',
        'key_helper'  => 'معرّف النظام (لا يمكن تغييره)',
        'value'       => 'القيمة',
        'description' => 'الوصف',
        'enabled'     => 'مفعّل',
        'disabled'    => 'معطّل',
        'content_set' => 'تم تعيين المحتوى',
        'not_set'     => 'غير معيّن',
    ],

    'columns' => [
        'setting'       => 'الإعداد',
        'current_value' => 'القيمة الحالية',
        'description'   => 'الوصف',
        'last_modified' => 'آخر تعديل',
    ],

    'notifications' => [
        'updated'      => 'تم تحديث الإعدادات',
        'updated_body' => 'تم تحديث إعدادات الحجز بنجاح.',
    ],

];
