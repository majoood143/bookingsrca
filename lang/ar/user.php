<?php

return [

    'navigation' => [
        'group'  => 'الإدارة',
        'label'  => 'مستخدم',
        'plural' => 'المستخدمون',
    ],

    'fields' => [
        'name'              => 'الاسم',
        'email'             => 'البريد الإلكتروني',
        'email_verified_at' => 'تاريخ التحقق من البريد',
        'password'          => 'كلمة المرور',
        'is_active'         => 'مفعل',
    ],

    'columns' => [
        'name'              => 'الاسم',
        'email'             => 'البريد الإلكتروني',
        'email_verified_at' => 'تاريخ التحقق من البريد',
        'created_at'        => 'تاريخ الإنشاء',
        'updated_at'        => 'تاريخ التعديل',
        'role'              => 'الصلاحية',
        'is_active'         => 'مفعل',
    ],

];
