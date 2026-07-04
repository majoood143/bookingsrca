<?php

return [

    'navigation' => [
        'group'  => 'Event Management',
        'label'  => 'Time Slot',
        'plural' => 'Time Slots',
    ],

    'sections' => [
        'information'      => 'Time Slot Information',
        'information_desc' => 'Configure time slot for an event',
        'capacity'         => 'Capacity Overview',
        'capacity_desc'    => 'View current capacity and availability',
    ],

    'fields' => [
        'event'                    => 'Event',
        'event_helper'             => 'Select the event for this time slot',
        'date'                     => 'Date',
        'date_helper'              => 'The specific calendar date this slot applies to. Capacity is tracked independently for each date.',
        'date_not_recurring_day'   => 'This date does not fall on one of the event\'s recurring days.',
        'date_unique'              => 'A time slot with this date and time range already exists for this event.',
        'start_time'               => 'Start Time',
        'start_time_helper'        => 'Select the start time for this slot',
        'end_time'                 => 'End Time',
        'end_time_helper'          => 'Select the end time for this slot',
        'label'                    => 'Display Label',
        'label_placeholder'        => 'e.g. Bus 2 / Gate A',
        'label_helper'             => 'Optional label shown on the digital signage screen. Leave blank to auto-number the slot.',
        'max_attendees'            => 'Maximum Attendees',
        'max_attendees_helper'     => 'Maximum number of bookings for this time slot',
        'current_bookings'         => 'Current Bookings',
        'current_bookings_helper'  => 'This is automatically updated when bookings are made',
        'is_active'                => 'Active',
        'is_active_helper'         => 'Only active time slots are available for booking',
    ],

    'create_event_form' => [
        'title_en'  => 'Title (English)',
        'title_ar'  => 'Title (Arabic)',
        'draft'     => 'Draft',
        'published' => 'Published',
    ],

    'capacity_info' => [
        'pending'    => 'Capacity information will be available after creating the time slot.',
        'booked'     => 'Booked:',
        'available'  => 'Available:',
        'total'      => 'Total:',
        'filled_pct' => ':percent% capacity filled',
    ],

    'columns' => [
        'event'          => 'Event',
        'date'           => 'Date',
        'start_time'     => 'Start Time',
        'end_time'       => 'End Time',
        'time_range'     => 'Time Range',
        'label'          => 'Label',
        'capacity'       => 'Capacity',
        'booked'         => 'Booked',
        'available'      => 'Available',
        'filled'         => 'Filled',
        'status'         => 'Status',
        'total_bookings' => 'Total Bookings',
        'created_at'     => 'Created At',
        'updated_at'     => 'Updated At',
    ],

    'filters' => [
        'by_event'          => 'Filter by Event',
        'status'            => 'Status',
        'status_all'        => 'All slots',
        'status_active'     => 'Active only',
        'status_inactive'   => 'Inactive only',
        'availability'      => 'Availability',
        'availability_show' => 'Show',
        'avail_available'   => 'Available slots',
        'avail_full'        => 'Full slots',
        'avail_almost'      => 'Almost full (>80%)',
        'ind_available'     => 'Available slots only',
        'ind_full'          => 'Full slots only',
        'ind_almost'        => 'Almost full slots (>80%)',
        'time_range'        => 'Time Range',
        'from'              => 'From',
        'to'                => 'To',
        'date'              => 'Date',
    ],

    'actions' => [
        'new'                 => 'New Time Slot',
        'deactivate'          => 'Deactivate',
        'activate'            => 'Activate',
        'activate_selected'   => 'Activate Selected',
        'deactivate_selected' => 'Deactivate Selected',
        'create_first'        => 'Create first time slot',
        'generate_slots'      => 'Generate Slots',
        'edit_time_range'     => 'Edit Time Range',
    ],

    'modals' => [
        'deactivate_heading'        => 'Deactivate Time Slot',
        'activate_heading'          => 'Activate Time Slot',
        'deactivate_description'    => 'This time slot will not be available for new bookings.',
        'activate_description'      => 'This time slot will be available for bookings.',
        'generate_slots_description'   => 'Creates one time slot for each available date of the selected event (respecting its recurring days, if any). Existing slots for a date/time combination are left untouched.',
        'edit_time_range_heading'      => 'Edit Time Range',
        'edit_time_range_description'  => 'Update the start and end times for the selected slots. Optionally restrict the update to a specific date range.',
        'edit_time_range_period_hint'  => 'Leave blank to update all selected slots regardless of date.',
    ],

    'empty_state' => [
        'heading'     => 'No time slots yet',
        'description' => 'Create time slots for your events to enable bookings.',
    ],

    'notifications' => [
        'status_updated'   => 'Status updated',
        'slot_activated'   => 'Time slot activated',
        'slot_deactivated' => 'Time slot deactivated',
        'slots_activated'  => 'Time slots activated',
        'slots_deactivated'=> 'Time slots deactivated',
        'cannot_delete'    => 'Cannot delete',
        'has_bookings'     => 'This time slot has existing bookings.',
        'bulk_has_bookings'=> ':count time slot(s) have existing bookings.',
        'created'          => 'Time slot created successfully',
        'updated'          => 'Time slot updated successfully',
        'slots_generated'      => 'Slots generated',
        'slots_generated_body'     => ':created created, :skipped already existed.',
        'time_range_updated'       => 'Time range updated',
        'time_range_updated_body'  => ':count slot(s) updated.',
    ],

    'suffix' => [
        'people' => 'people',
        'booked' => 'booked',
    ],

];
