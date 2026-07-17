<?php

return [

    'navigation' => [
        'group'  => 'Administration',
        'label'  => 'User',
        'plural' => 'Users',
    ],

    'fields' => [
        'name'              => 'Name',
        'email'             => 'Email',
        'email_verified_at' => 'Email Verified At',
        'password'          => 'Password',
        'is_active'         => 'Active',
    ],

    'columns' => [
        'name'              => 'Name',
        'email'             => 'Email',
        'email_verified_at' => 'Email Verified At',
        'created_at'        => 'Created At',
        'updated_at'        => 'Updated At',
        'role'              => 'Role',
        'is_active'         => 'Active',
    ],

];
