<?php

return [

    'navigation' => [
        'label' => 'Reports',
        'group' => 'Booking Management',
    ],

    'title' => 'Reports',
    'document_title' => 'Official Booking Report',

    'sections' => [
        'filters'       => 'Filters',
        'summary'       => 'Summary',
        'by_event'      => 'Breakdown by Event',
        'by_ticket'     => 'Breakdown by Ticket Type',
    ],

    'filters' => [
        'period'     => 'Period',
        'event'      => 'Event',
        'all_events' => 'All Events',
        'event_date' => 'Event Date',
        'all_dates'  => 'All Dates',
        'time_slot'  => 'Time Slot',
        'all_slots'  => 'All Slots',
        'date_from'  => 'From Date',
        'date_to'    => 'To Date',
    ],

    'periods' => [
        'today'      => 'Today',
        'this_week'  => 'This Week',
        'this_month' => 'This Month',
        'last_month' => 'Last Month',
        'this_year'  => 'This Year',
        'custom'     => 'Custom Range',
    ],

    'stats' => [
        'total_bookings'    => 'Total Bookings',
        'confirmed'         => 'Confirmed',
        'pending'           => 'Pending',
        'cancelled'         => 'Cancelled',
        'checked_in'        => 'Checked In',
        'total_revenue'     => 'Total Revenue',
        'total_attendees'   => 'Total Attendees',
    ],

    'columns' => [
        'event'         => 'Event',
        'ticket_type'   => 'Ticket Type',
        'total'         => 'Total Bookings',
        'confirmed'     => 'Confirmed',
        'pending'       => 'Pending',
        'cancelled'     => 'Cancelled',
        'checked_in'    => 'Checked In',
        'revenue'       => 'Revenue',
        'attendees'     => 'Attendees',
        'bookings'      => 'Bookings',
    ],

    'actions' => [
        'export'       => 'Export Excel',
        'apply'        => 'Apply Filters',
        'download_pdf' => 'Download PDF',
    ],

    'export' => [
        'sheet_summary'  => 'Summary',
        'sheet_bookings' => 'Bookings',
        'filename'       => 'report',

        'headings' => [
            'reference'      => 'Reference',
            'event'          => 'Event',
            'event_date'     => 'Event Date',
            'time_slot'      => 'Time Slot',
            'ticket_type'    => 'Ticket Type',
            'quantity'       => 'Quantity',
            'ticket_price'   => 'Ticket Price',
            'services_price' => 'Services Price',
            'total_price'    => 'Total Price',
            'status'         => 'Status',
            'attendees'      => 'Attendee Names',
            'confirmed_at'   => 'Confirmed At',
            'cancelled_at'   => 'Cancelled At',
            'created_at'     => 'Created At',
        ],
    ],

    'no_data' => 'No data available for the selected period.',

    'period_label' => 'Period: :from — :to',

    'document' => [
        'generated_on' => 'Generated on',
        'period'       => ':from — :to',
    ],

];
