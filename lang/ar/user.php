<?php

return [

    'navigation' => [
        'group'  => 'الإدارة',
        'label'  => 'مستخدم',
        'plural' => 'المستخدمون',
    ],

    'sections' => [
        'account'  => 'بيانات الحساب',
        'personal' => 'البيانات الشخصية',
        'bank'     => 'البيانات المصرفية',
    ],

    'fields' => [
        'name'                      => 'الاسم',
        'prefix'                    => 'اللقب',
        'email'                     => 'البريد الإلكتروني',
        'email_verified_at'         => 'تاريخ التحقق من البريد',
        'password'                  => 'كلمة المرور',
        'is_active'                 => 'مفعل',
        'roles'                     => 'الصلاحيات',
        'date_of_birth'             => 'تاريخ الميلاد',
        'gender'                    => 'الجنس',
        'marital_status'            => 'الحالة الاجتماعية',
        'blood_group'               => 'فصيلة الدم',
        'mobile_number'             => 'رقم الجوال',
        'guardian_name'             => 'اسم ولي الأمر',
        'nationality'               => 'الجنسية',
        'national_id_number'        => 'رقم الهوية / جواز السفر',
        'address'                   => 'العنوان',
        'bank_account_holder_name'  => 'اسم صاحب الحساب',
        'bank_account_number'       => 'رقم الحساب',
        'bank_name'                 => 'اسم البنك',
        'bank_identifier_code'      => 'رمز تعريف البنك (BIC/SWIFT)',
        'bank_branch'               => 'الفرع',
    ],

    'columns' => [
        'name'              => 'الاسم',
        'email'             => 'البريد الإلكتروني',
        'email_verified_at' => 'تاريخ التحقق من البريد',
        'created_at'        => 'تاريخ الإنشاء',
        'updated_at'        => 'تاريخ التعديل',
        'role'              => 'الصلاحية',
        'is_active'         => 'مفعل',
        'mobile_number'     => 'رقم الجوال',
        'gender'            => 'الجنس',
    ],

    'prefixes' => [
        'mr'   => 'السيد',
        'mrs'  => 'السيدة',
        'ms'   => 'الآنسة',
        'miss' => 'آنسة',
        'dr'   => 'الدكتور',
        'prof' => 'الأستاذ الدكتور',
        'eng'  => 'المهندس',
    ],

    'genders' => [
        'male'   => 'ذكر',
        'female' => 'أنثى',
        'other'  => 'أخرى',
    ],

    'marital_statuses' => [
        'single'   => 'أعزب',
        'married'  => 'متزوج',
        'divorced' => 'مطلق',
        'widowed'  => 'أرمل',
    ],

    'blood_groups' => [
        'a+'  => 'A+',
        'a-'  => 'A-',
        'b+'  => 'B+',
        'b-'  => 'B-',
        'ab+' => 'AB+',
        'ab-' => 'AB-',
        'o+'  => 'O+',
        'o-'  => 'O-',
    ],

];
