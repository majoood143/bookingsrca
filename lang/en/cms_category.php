<?php

return [
    'navigation' => [
        'label' => 'Category',
        'plural' => 'Categories',
    ],
    'sections' => [
        'information' => 'Information',
    ],
    'fields' => [
        'name_en' => 'Name (English)',
        'name_ar' => 'Name (Arabic)',
        'slug' => 'Slug',
        'description_en' => 'Description (English)',
        'description_ar' => 'Description (Arabic)',
        'parent' => 'Parent category',
        'order' => 'Order',
    ],
    'columns' => [
        'name' => 'Name',
        'parent' => 'Parent',
        'posts_count' => 'Posts',
        'order' => 'Order',
    ],
    'actions' => [
        'create_first' => 'Create first category',
    ],
    'empty_state' => [
        'heading' => 'No categories yet',
        'description' => 'Create a category to start organizing posts.',
    ],
    'notifications' => [
        'cannot_delete' => 'Cannot delete category',
        'has_posts' => 'This category still has posts or subcategories attached to it.',
    ],
];
