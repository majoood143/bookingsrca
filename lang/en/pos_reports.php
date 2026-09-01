<?php

return [

    'sales' => [
        'title' => 'Sales Report',
        'document_title' => 'POS Sales Report',
        'filename' => 'pos-sales-report',
        'filters' => [
            'section' => 'Filters',
            'period' => 'Period',
            'channel' => 'Channel',
            'all_channels' => 'All Channels',
            'language' => 'Report Language',
        ],
        'channels' => [
            'counter' => 'Counter',
            'online' => 'Online Shop',
        ],
        'stats' => [
            'total_revenue' => 'Total Revenue',
            'total_quantity' => 'Units Sold',
            'order_count' => 'Sales Count',
            'avg_order_value' => 'Avg. Sale Value',
        ],
        'sections' => [
            'by_product' => 'Sales by Product',
            'by_category' => 'Sales by Category',
            'by_brand' => 'Sales by Brand',
            'by_channel' => 'Sales by Channel',
            'trend' => 'Daily Trend',
        ],
        'columns' => [
            'product' => 'Product',
            'category' => 'Category',
            'brand' => 'Brand',
            'channel' => 'Channel',
            'quantity' => 'Qty',
            'revenue' => 'Revenue',
            'date' => 'Date',
        ],
        'no_data' => 'No sales in this period.',
    ],

    'transactions' => [
        'title' => 'Transactions Report',
        'document_title' => 'POS Transactions Report',
        'filename' => 'pos-transactions-report',
        'filters' => [
            'section' => 'Filters',
            'period' => 'Period',
            'language' => 'Report Language',
        ],
        'stats' => [
            'total_amount' => 'Total Collected',
            'transaction_count' => 'Transaction Count',
        ],
        'sections' => [
            'by_method' => 'By Payment Method',
            'by_status' => 'By Status',
            'list' => 'Transactions',
        ],
        'columns' => [
            'method' => 'Method',
            'status' => 'Status',
            'count' => 'Count',
            'amount' => 'Amount',
            'reference' => 'Reference',
            'source' => 'Source',
            'date' => 'Date',
        ],
        'sources' => [
            'counter' => 'Counter',
            'online' => 'Online Shop',
        ],
        'no_data' => 'No transactions in this period.',
    ],

    'periods' => [
        'today' => 'Today',
        'this_week' => 'This Week',
        'this_month' => 'This Month',
        'last_month' => 'Last Month',
        'this_year' => 'This Year',
        'custom' => 'Custom Range',
    ],
    'date_from' => 'From',
    'date_to' => 'To',
    'languages' => [
        'en' => 'English',
        'ar' => 'Arabic',
    ],
    'document' => [
        'period' => 'Period: :from to :to',
        'generated_on' => 'Generated on',
    ],
    'actions' => [
        'download_pdf' => 'Download PDF',
        'download_excel' => 'Download Excel',
    ],
    'currency' => 'OMR',

];
