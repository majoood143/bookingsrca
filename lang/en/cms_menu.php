<?php

return [
    'navigation' => [
        'label' => 'Menu',
        'plural' => 'Menus',
    ],
    'sections' => [
        'information' => 'Information',
    ],
    'relation' => [
        'items' => 'Menu Items',
    ],
    'fields' => [
        'name' => 'Name',
        'slug' => 'Slug',
        'location' => 'Location',
        'location_helper' => 'e.g. header, footer — used to render this menu in the right place on the frontend.',
        'label_en' => 'Label (English)',
        'label_ar' => 'Label (Arabic)',
        'link_type' => 'Link type',
        'url' => 'URL',
        'linkable' => 'Link to',
        'icon' => 'Icon',
        'icon_helper' => 'A Heroicon name, e.g. heroicon-o-home',
        'parent' => 'Parent item',
        'is_active' => 'Active',
    ],
    'link_types' => [
        'custom' => 'Custom URL',
        'page' => 'Page',
        'post' => 'Post',
        'category' => 'Category',
    ],
    'columns' => [
        'name' => 'Name',
        'location' => 'Location',
        'items_count' => 'Items',
        'label' => 'Label',
        'url' => 'URL',
        'is_active' => 'Active',
        'sort_order' => 'Order',
    ],
    'actions' => [
        'create_first' => 'Create first menu',
    ],
    'empty_state' => [
        'heading' => 'No menus yet',
        'description' => 'Create a menu, then add items to it below.',
    ],
];
