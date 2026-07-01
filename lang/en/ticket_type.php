<?php

return [

    'navigation' => [
        'group'  => 'Event Management',
        'label'  => 'Ticket Type',
        'plural' => 'Ticket Types',
    ],

    'sections' => [
        'information'      => 'Ticket Type Information',
        'information_desc' => 'Define ticket types for events (Standard, VIP, Student, etc.)',
        'pricing'          => 'Pricing & Capacity',
        'pricing_desc'     => 'Set price and available quantity',
        'sale_period'      => 'Sale Period',
        'sale_period_desc' => 'Optional: Restrict when this ticket type can be purchased',
        'status'           => 'Status & Settings',
        'dependency'       => 'Ticket Dependency',
        'dependency_desc'  => 'Optional: Require another ticket type to be selected before this one can be booked',
        'sales_overview'   => 'Sales Overview',
        'sales_overview_desc' => 'View current sales and availability',
    ],

    'fields' => [
        'event'                      => 'Event',
        'event_helper'               => 'Select the event for this ticket type',
        'name_en'                    => 'Name (English)',
        'name_en_helper'             => 'Enter the ticket type name in English',
        'name_ar'                    => 'Name (Arabic)',
        'name_ar_helper'             => 'Enter the ticket type name in Arabic',
        'description_en'             => 'Description (English)',
        'description_ar'             => 'Description (Arabic)',
        'price'                      => 'Price',
        'price_helper'               => 'Set ticket price in OMR',
        'quantity_available'         => 'Quantity Available',
        'quantity_available_helper'  => 'Total tickets available',
        'quantity_sold'              => 'Quantity Sold',
        'quantity_sold_helper'       => 'Auto-updated on bookings',
        'sale_start_date'            => 'Sale Start Date',
        'sale_start_helper'          => 'Leave empty for no restriction',
        'sale_end_date'              => 'Sale End Date',
        'sale_end_helper'            => 'Leave empty for no restriction',
        'is_active'                  => 'Active',
        'is_active_helper'           => 'Only active ticket types are available for purchase',
        'depends_on'                 => 'Depends On',
        'depends_on_helper'          => 'Bookers must select this parent ticket type before they can add the current one',
        'depends_on_placeholder'     => 'None (standalone ticket)',
    ],

    'placeholders' => [
        'name_en'        => 'e.g., Standard, VIP, Student',
        'name_ar'        => 'مثال: عادي، في آي بي، طالب',
        'description_en' => 'Describe what this ticket includes...',
        'description_ar' => 'اشرح ما تتضمنه هذه التذكرة...',
    ],

    'create_event_form' => [
        'title_en'  => 'Title (English)',
        'title_ar'  => 'Title (Arabic)',
        'draft'     => 'Draft',
        'published' => 'Published',
    ],

    'sale_period_status' => [
        'always_available'  => '✓ Always available for purchase',
        'not_yet'           => '⚠ Not yet available (starts :date)',
        'ended'             => '✗ Sale period ended (:date)',
        'currently'         => '✓ Currently available for purchase',
        'pending'           => 'Sale period settings will apply after creating the ticket type.',
    ],

    'sales_info' => [
        'pending'           => 'Sales information will be available after creating the ticket type.',
        'sold'              => 'Sold',
        'available'         => 'Available',
        'total'             => 'Total',
        'sales_progress'    => 'Sales Progress',
        'revenue_generated' => 'Revenue Generated',
        'potential_revenue' => 'Potential Revenue',
    ],

    'columns' => [
        'event'       => 'Event',
        'name'        => 'Ticket Type',
        'price'       => 'Price',
        'total'       => 'Total',
        'sold'        => 'Sold',
        'available'   => 'Available',
        'sold_pct'    => 'Sold %',
        'revenue'     => 'Revenue',
        'status'      => 'Status',
        'sale_period' => 'Sale Period',
        'depends_on'  => 'Depends On',
        'bookings'    => 'Bookings',
        'created_at'  => 'Created At',
        'updated_at'  => 'Updated At',
    ],

    'sale_period_column' => [
        'always'    => 'Always',
        'from'      => 'From :date',
        'until'     => 'Until :date',
    ],

    'filters' => [
        'by_event'         => 'Filter by Event',
        'status'           => 'Status',
        'status_all'       => 'All ticket types',
        'status_active'    => 'Active only',
        'status_inactive'  => 'Inactive only',
        'availability'     => 'Availability',
        'availability_show'=> 'Show',
        'avail_available'  => 'Available tickets',
        'avail_sold_out'   => 'Sold out',
        'avail_almost'     => 'Almost sold out (>80%)',
        'avail_low'        => 'Low stock (<50 remaining)',
        'ind_available'    => 'Available tickets only',
        'ind_sold_out'     => 'Sold out tickets only',
        'ind_almost'       => 'Almost sold out (>80%)',
        'ind_low'          => 'Low stock (<50 remaining)',
        'price_range'      => 'Price Range',
        'price_from'       => 'From',
        'price_to'         => 'To',
        'ind_price_from'   => 'Price from: $:amount',
        'ind_price_to'     => 'Price to: $:amount',
    ],

    'actions' => [
        'new'                 => 'New Ticket Type',
        'deactivate'          => 'Deactivate',
        'activate'            => 'Activate',
        'activate_selected'   => 'Activate Selected',
        'deactivate_selected' => 'Deactivate Selected',
        'create_first'        => 'Create first ticket type',
    ],

    'modals' => [
        'deactivate_heading'     => 'Deactivate Ticket Type',
        'activate_heading'       => 'Activate Ticket Type',
        'deactivate_description' => 'This ticket type will not be available for purchase.',
        'activate_description'   => 'This ticket type will be available for purchase.',
    ],

    'empty_state' => [
        'heading'     => 'No ticket types yet',
        'description' => 'Create ticket types for your events to enable bookings.',
    ],

    'notifications' => [
        'status_updated'        => 'Status updated',
        'type_activated'        => 'Ticket type activated',
        'type_deactivated'      => 'Ticket type deactivated',
        'types_activated'       => 'Ticket types activated',
        'types_deactivated'     => 'Ticket types deactivated',
        'cannot_delete'         => 'Cannot delete',
        'has_bookings'          => 'This ticket type has existing bookings.',
        'bulk_has_bookings'     => ':count ticket type(s) have existing bookings.',
        'created'               => 'Ticket type created successfully',
        'updated'               => 'Ticket type updated successfully',
    ],

    'suffix' => [
        'tickets' => 'tickets',
        'sold'    => 'sold',
    ],

];
