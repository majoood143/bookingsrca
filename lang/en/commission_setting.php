<?php

return [

    'navigation' => [
        'group' => 'Booking Management',
        'label' => 'Commission Setting',
        'plural' => 'Commission Settings',
    ],

    'types' => [
        'percentage' => 'Percentage',
        'fixed' => 'Fixed Amount',
    ],

    'sections' => [
        'scope' => 'Commission Scope',
        'scope_desc' => 'Leave the event empty to set the global (platform-wide) commission rate.',
        'details' => 'Commission Details',
        'validity' => 'Validity Period',
    ],

    'fields' => [
        'event' => 'Event',
        'event_helper' => 'Applies only to bookings for this event. Leave empty for the global rate.',
        'scope_note' => 'Event-specific rates take priority over the global rate when both are active and effective on the same date.',
        'name_en' => 'Name (English)',
        'name_ar' => 'Name (Arabic)',
        'commission_type' => 'Commission Type',
        'commission_value' => 'Commission Value',
        'description_en' => 'Description (English)',
        'description_ar' => 'Description (Arabic)',
        'effective_from' => 'Effective From',
        'effective_from_helper' => 'Leave empty to apply immediately.',
        'effective_to' => 'Effective To',
        'effective_to_helper' => 'Leave empty for no end date.',
        'is_active' => 'Active',
    ],

    'columns' => [
        'scope' => 'Scope',
        'global' => 'Global',
        'event_specific' => 'Event: :name',
        'name' => 'Name',
        'type' => 'Type',
        'value' => 'Rate',
        'effective_from' => 'Effective From',
        'effective_to' => 'Effective To',
        'indefinite' => 'Indefinite',
        'is_active' => 'Active',
        'created_at' => 'Created At',
    ],

    'filters' => [
        'active' => 'Active',
        'scope_type' => 'Scope Type',
        'event_specific' => 'Event-Specific',
    ],

];
