<?php

return [

    'navigation' => [
        'group'  => 'Event Management',
        'label'  => 'Extra Service',
        'plural' => 'Extra Services',
    ],

    'sections' => [
        'information'        => 'Service Information',
        'information_desc'   => 'Define extra services for events (meals, transportation, merchandise, etc.)',
        'pricing'            => 'Pricing & Availability',
        'pricing_desc'       => 'Set price and quantity settings',
        'status'             => 'Status & Settings',
        'usage'              => 'Usage Overview',
        'usage_desc'         => 'View current usage statistics',
    ],

    'fields' => [
        'event'                   => 'Event',
        'event_helper'            => 'Select the event for this extra service',
        'name_en'                 => 'Service Name (English)',
        'name_en_helper'          => 'Enter the service name in English',
        'name_ar'                 => 'Service Name (Arabic)',
        'name_ar_helper'          => 'Enter the service name in Arabic',
        'description_en'          => 'Description (English)',
        'description_ar'          => 'Description (Arabic)',
        'price'                   => 'Price',
        'price_helper'            => 'Set service price in USD',
        'quantity_available'      => 'Quantity Available',
        'quantity_available_helper' => 'Leave empty for unlimited quantity',
        'quantity_used'           => 'Quantity Used',
        'quantity_used_helper'    => 'Auto-updated on bookings',
        'is_active'               => 'Active',
        'is_active_helper'        => 'Only active services are available during booking',
    ],

    'placeholders' => [
        'name_en'         => 'e.g., Lunch, Transportation, T-Shirt',
        'name_ar'         => 'مثال: وجبة غداء، مواصلات، تي شيرت',
        'description_en'  => 'Describe what this service includes...',
        'description_ar'  => 'اشرح ما تتضمنه هذه الخدمة...',
        'quantity'        => 'Unlimited',
    ],

    'create_event_form' => [
        'title_en'   => 'Title (English)',
        'title_ar'   => 'Title (Arabic)',
        'draft'      => 'Draft',
        'published'  => 'Published',
    ],

    'quantity_info' => [
        'unlimited_badge'       => '♾️ Unlimited Quantity',
        'unlimited_description' => 'This service has no quantity restrictions.',
        'limited_badge'         => '✓ Limited Quantity',
        'limited_description'   => 'This service has a quantity limit of :count units.',
    ],

    'usage_info' => [
        'pending'           => 'Usage information will be available after creating the service.',
        'total_used'        => 'Total Used',
        'no_limit'          => 'No limit',
        'revenue_generated' => 'Revenue Generated',
        'unlimited_service' => 'Unlimited service',
        'used'              => 'Used',
        'available'         => 'Available',
        'total'             => 'Total',
        'usage_progress'    => 'Usage Progress',
        'potential_revenue' => 'Potential Revenue',
    ],

    'columns' => [
        'event'       => 'Event',
        'name'        => 'Service Name',
        'price'       => 'Price',
        'total'       => 'Total',
        'used'        => 'Used',
        'available'   => 'Available',
        'used_pct'    => 'Used %',
        'revenue'     => 'Revenue',
        'status'      => 'Status',
        'bookings'    => 'Bookings',
        'created_at'  => 'Created At',
        'updated_at'  => 'Updated At',
    ],

    'filters' => [
        'by_event'            => 'Filter by Event',
        'status'              => 'Status',
        'status_all'          => 'All services',
        'status_active'       => 'Active only',
        'status_inactive'     => 'Inactive only',
        'quantity_type'       => 'Quantity Type',
        'quantity_all'        => 'All services',
        'quantity_limited'    => 'Limited quantity',
        'quantity_unlimited'  => 'Unlimited quantity',
        'availability'        => 'Availability',
        'availability_show'   => 'Show',
        'avail_available'     => 'Available services',
        'avail_depleted'      => 'Depleted (0 remaining)',
        'avail_almost'        => 'Almost depleted (>80%)',
        'avail_low'           => 'Low stock (<20 remaining)',
        'ind_available'       => 'Available services only',
        'ind_depleted'        => 'Depleted services only',
        'ind_almost'          => 'Almost depleted (>80%)',
        'ind_low'             => 'Low stock (<20 remaining)',
        'price_range'         => 'Price Range',
        'price_from'          => 'From',
        'price_to'            => 'To',
        'ind_price_from'      => 'Price from: $:amount',
        'ind_price_to'        => 'Price to: $:amount',
    ],

    'actions' => [
        'new'              => 'New Extra Service',
        'deactivate'       => 'Deactivate',
        'activate'         => 'Activate',
        'activate_selected'=> 'Activate Selected',
        'deactivate_selected' => 'Deactivate Selected',
        'create_first'     => 'Create first extra service',
    ],

    'modals' => [
        'deactivate_heading'     => 'Deactivate Service',
        'activate_heading'       => 'Activate Service',
        'deactivate_description' => 'This service will not be available during booking.',
        'activate_description'   => 'This service will be available during booking.',
    ],

    'empty_state' => [
        'heading'     => 'No extra services yet',
        'description' => 'Create extra services that attendees can add to their bookings.',
    ],

    'notifications' => [
        'status_updated'          => 'Status updated',
        'service_activated'       => 'Service activated',
        'service_deactivated'     => 'Service deactivated',
        'services_activated'      => 'Services activated',
        'services_deactivated'    => 'Services deactivated',
        'cannot_delete'           => 'Cannot delete',
        'has_bookings'            => 'This service has existing bookings.',
        'bulk_has_bookings'       => ':count service(s) have existing bookings.',
        'created'                 => 'Extra service created successfully',
        'updated'                 => 'Extra service updated successfully',
    ],

    'suffix' => [
        'units' => 'units',
        'used'  => 'used',
    ],

];
