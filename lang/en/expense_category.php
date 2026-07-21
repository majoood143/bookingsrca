<?php

return [

    'navigation' => [
        'group' => 'Booking Management',
        'label' => 'Expense Category',
        'plural' => 'Expense Categories',
    ],

    'sections' => [
        'information' => 'Category Information',
    ],

    'fields' => [
        'name_en' => 'Name (English)',
        'name_ar' => 'Name (Arabic)',
        'description_en' => 'Description (English)',
        'description_ar' => 'Description (Arabic)',
        'color' => 'Color',
        'icon' => 'Icon',
        'order' => 'Display Order',
        'is_active' => 'Active',
    ],

    'columns' => [
        'name' => 'Name',
        'color' => 'Color',
        'expenses_count' => 'Expenses',
        'is_active' => 'Active',
        'order' => 'Order',
    ],

    'filters' => [
        'is_active' => 'Active',
    ],

    'actions' => [
        'create_first' => 'Create first category',
    ],

    'notifications' => [
        'cannot_delete' => 'Cannot delete',
        'has_expenses' => 'This category has expenses linked to it.',
    ],

    'empty_state' => [
        'heading' => 'No expense categories yet',
        'description' => 'Create categories to classify your expenses (utilities, marketing, maintenance, etc.)',
    ],
];
