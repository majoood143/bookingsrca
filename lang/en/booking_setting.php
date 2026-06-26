<?php

return [

    'navigation' => [
        'label' => 'Booking Settings',
        'group' => 'Settings',
        'plural' => 'Booking Settings',
    ],

    'sections' => [
        'configuration' => 'Setting Configuration',
    ],

    'fields' => [
        'key'         => 'Setting Key',
        'key_helper'  => 'System identifier (cannot be changed)',
        'value'       => 'Value',
        'description' => 'Description',
        'enabled'     => 'Enabled',
        'disabled'    => 'Disabled',
        'content_set' => 'Content Set',
        'not_set'     => 'Not Set',
    ],

    'columns' => [
        'setting'       => 'Setting',
        'current_value' => 'Current Value',
        'description'   => 'Description',
        'last_modified' => 'Last Modified',
    ],

    'notifications' => [
        'updated'      => 'Settings Updated',
        'updated_body' => 'Booking settings have been updated successfully.',
    ],

];
