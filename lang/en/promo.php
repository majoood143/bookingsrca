<?php

return [
    // Navigation
    'navigation_group' => 'Event Management',
    'navigation_label' => 'Promo Codes',
    'singular'         => 'Promo Code',
    'plural'           => 'Promo Codes',

    // Form sections
    'section_details'  => 'Code Details',
    'section_discount' => 'Discount',
    'section_validity' => 'Validity',
    'section_scope'    => 'Scope (Event)',

    // Form fields
    'field_code'                 => 'Code',
    'field_name'                 => 'Label / Name',
    'field_description'          => 'Description',
    'field_discount_type'        => 'Discount Type',
    'field_discount_value_pct'   => 'Discount (%)',
    'field_discount_value_fixed' => 'Discount Amount',
    'field_valid_from'           => 'Valid From',
    'field_valid_until'          => 'Valid Until',
    'field_max_uses'             => 'Max Uses',
    'field_max_uses_helper'      => 'Leave empty for unlimited uses.',
    'field_is_active'            => 'Active',
    'field_event'                 => 'Event (optional)',
    'field_event_helper'          => 'Leave empty to allow the code on all events.',

    // Discount types
    'type_percentage' => 'Percentage (%)',
    'type_fixed'      => 'Fixed Amount',

    // Table columns
    'col_code'        => 'Code',
    'col_name'        => 'Name',
    'col_discount'    => 'Discount',
    'col_event'       => 'Event',
    'col_used'        => 'Uses',
    'col_valid_until' => 'Expires',
    'col_active'      => 'Active',

    // Filters
    'filter_active' => 'Active status',
    'filter_type'   => 'Discount type',
    'filter_event'  => 'Event',

    // Values
    'all_events' => 'All Events',
    'no_expiry'  => 'No expiry',

    // Frontend labels
    'label'          => 'Promo Code',
    'placeholder'    => 'Enter promo code',
    'apply'          => 'Apply',
    'applied'        => '✓ Applied',
    'remove'         => 'Remove',
    'checking'       => 'Checking...',
    'discount_label' => 'Promo Discount',
    'subtotal_label' => 'Subtotal',
    'error'          => 'Could not validate the promo code. Please try again.',

    // Validation messages
    'invalid_code'      => 'Invalid promo code.',
    'code_inactive'     => 'This promo code is not active.',
    'code_not_started'  => 'This promo code is not valid yet.',
    'code_expired'      => 'This promo code has expired.',
    'code_used_up'      => 'This promo code has reached its usage limit.',
    'code_applied'      => 'Promo code applied! You save :amount OMR.',

    // Bookings relation manager
    'rel_bookings_title'       => 'Bookings Using This Code',
    'rel_col_booking_reference' => 'Booking #',
    'rel_col_customer'         => 'Customer',
    'rel_col_event'            => 'Event',
    'rel_col_booking_date'     => 'Booking Date',
    'rel_col_discount'         => 'Discount',
    'rel_col_total'            => 'Total',
    'rel_col_status'           => 'Status',
    'rel_col_payment_status'   => 'Payment',
    'rel_col_created_at'       => 'Created At',
    'rel_export_bookings'      => 'Export Bookings',

    // Export
    'export_btn'            => 'Export CSV',
    'export_col_email'      => 'Email',
    'export_col_phone'      => 'Phone',
    'export_success_title'  => 'Export Ready',
    'export_success_body'   => 'File :filename has been generated.',
    'export_download'       => 'Download',
];
