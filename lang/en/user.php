<?php

return [

    'navigation' => [
        'group'  => 'Administration',
        'label'  => 'User',
        'plural' => 'Users',
    ],

    'sections' => [
        'account'  => 'Account Details',
        'personal' => 'Personal Details',
        'bank'     => 'Bank Details',
    ],

    'fields' => [
        'name'                      => 'Name',
        'prefix'                    => 'Prefix',
        'email'                     => 'Email',
        'email_verified_at'         => 'Email Verified At',
        'password'                  => 'Password',
        'is_active'                 => 'Active',
        'roles'                     => 'Roles',
        'date_of_birth'             => 'Date of Birth',
        'gender'                    => 'Gender',
        'marital_status'            => 'Marital Status',
        'blood_group'               => 'Blood Group',
        'mobile_number'             => 'Mobile Number',
        'guardian_name'             => "Guardian's Name",
        'nationality'               => 'Nationality',
        'national_id_number'        => 'National ID / Passport Number',
        'address'                   => 'Address',
        'bank_account_holder_name'  => "Account Holder's Name",
        'bank_account_number'       => 'Account Number',
        'bank_name'                 => 'Bank Name',
        'bank_identifier_code'      => 'Bank Identifier Code (BIC/SWIFT)',
        'bank_branch'               => 'Branch',
    ],

    'columns' => [
        'name'              => 'Name',
        'email'             => 'Email',
        'email_verified_at' => 'Email Verified At',
        'created_at'        => 'Created At',
        'updated_at'        => 'Updated At',
        'role'              => 'Role',
        'is_active'         => 'Active',
        'mobile_number'     => 'Mobile Number',
        'gender'            => 'Gender',
    ],

    'prefixes' => [
        'mr'   => 'Mr.',
        'mrs'  => 'Mrs.',
        'ms'   => 'Ms.',
        'miss' => 'Miss',
        'dr'   => 'Dr.',
        'prof' => 'Prof.',
        'eng'  => 'Eng.',
    ],

    'genders' => [
        'male'   => 'Male',
        'female' => 'Female',
        'other'  => 'Other',
    ],

    'marital_statuses' => [
        'single'   => 'Single',
        'married'  => 'Married',
        'divorced' => 'Divorced',
        'widowed'  => 'Widowed',
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
