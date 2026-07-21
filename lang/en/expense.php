<?php

return [

    'navigation' => [
        'group' => 'Booking Management',
        'label' => 'Expense',
        'plural' => 'Expenses',
    ],

    'types' => [
        'event' => 'Event Expense',
        'operational' => 'Operational',
        'recurring' => 'Recurring',
        'one_time' => 'One-Time',
    ],

    'type_descriptions' => [
        'event' => 'Expenses directly linked to a specific event or booking (catering, decoration, staff)',
        'operational' => 'Day-to-day operational costs of running the business',
        'recurring' => 'Regularly occurring expenses (rent, utilities, subscriptions)',
        'one_time' => 'One-off expenses (equipment purchase, major repairs)',
    ],

    'payment_methods' => [
        'cash' => 'Cash',
        'bank_transfer' => 'Bank Transfer',
        'card' => 'Card',
        'cheque' => 'Cheque',
        'other' => 'Other',
    ],

    'payment_statuses' => [
        'pending' => 'Pending',
        'paid' => 'Paid',
        'partial' => 'Partial',
        'cancelled' => 'Cancelled',
    ],

    'statuses' => [
        'draft' => 'Draft',
        'submitted' => 'Submitted',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'archived' => 'Archived',
    ],

    'recurring_frequencies' => [
        'daily' => 'Daily',
        'weekly' => 'Weekly',
        'monthly' => 'Monthly',
        'quarterly' => 'Quarterly',
        'yearly' => 'Yearly',
    ],

    'sections' => [
        'information' => 'Expense Information',
        'information_desc' => 'Enter the basic expense details',
        'description' => 'Description',
        'financial' => 'Financial Details',
        'vendor' => 'Vendor Information',
        'recurring' => 'Recurring Settings',
        'attachments' => 'Attachments',
        'notes' => 'Notes',
    ],

    'fields' => [
        'expense_number' => 'Expense Number',
        'expense_type' => 'Expense Type',
        'event' => 'Event',
        'event_helper' => 'Select the event related to this expense (optional)',
        'booking' => 'Booking',
        'booking_helper' => 'Link this expense to a specific booking',
        'category' => 'Category',
        'title_en' => 'Title (English)',
        'title_ar' => 'Title (Arabic)',
        'description_en' => 'Description (English)',
        'description_ar' => 'Description (Arabic)',
        'amount' => 'Amount (OMR)',
        'tax_amount' => 'Tax (OMR)',
        'total' => 'Total',
        'expense_date' => 'Expense Date',
        'payment_method' => 'Payment Method',
        'payment_status' => 'Payment Status',
        'payment_reference' => 'Payment Reference',
        'due_date' => 'Due Date',
        'vendor_name' => 'Vendor Name',
        'vendor_phone' => 'Vendor Phone',
        'vendor_email' => 'Vendor Email',
        'is_recurring' => 'Recurring Expense',
        'recurring_frequency' => 'Frequency',
        'recurring_start_date' => 'Start Date',
        'recurring_end_date' => 'End Date',
        'recurring_end_date_helper' => 'Leave empty for indefinite recurring',
        'attachments' => 'Receipts & Documents',
        'attachments_helper' => 'Upload images or PDF files (max 5MB each)',
        'notes' => 'Internal Notes',
        'status' => 'Status',
    ],

    'columns' => [
        'number' => 'Number',
        'title' => 'Title',
        'type' => 'Type',
        'category' => 'Category',
        'event' => 'Event',
        'booking' => 'Booking',
        'amount' => 'Amount',
        'date' => 'Date',
        'payment' => 'Payment',
        'method' => 'Method',
        'vendor' => 'Vendor',
        'status' => 'Status',
        'created_at' => 'Created',
    ],

    'filters' => [
        'type' => 'Type',
        'category' => 'Category',
        'event' => 'Event',
        'payment_status' => 'Payment Status',
        'payment_method' => 'Payment Method',
        'status' => 'Status',
        'date_from' => 'From',
        'date_until' => 'Until',
        'has_booking' => 'Linked to Booking',
    ],

    'actions' => [
        'mark_paid' => 'Mark Paid',
        'bulk_mark_paid' => 'Mark as Paid',
        'approve' => 'Approve',
        'reject' => 'Reject',
        'create_first' => 'Create first expense',
    ],

    'notifications' => [
        'marked_paid' => 'Expense marked as paid',
        'approved' => 'Expense approved',
        'rejected' => 'Expense rejected',
        'cannot_delete' => 'Cannot delete',
        'has_children' => 'This expense has recurring child expenses.',
    ],

    'empty_state' => [
        'heading' => 'No expenses yet',
        'description' => 'Track operational costs, event expenses, and recurring bills here.',
    ],
];
