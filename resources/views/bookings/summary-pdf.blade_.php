<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking Pass - {{ $booking->booking_reference }}</title>
    <style>
        @page {
            margin: 0;
            size: A4;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #1f2937;
            background: #f8f9fa;
        }

        /* Boarding Pass Style Container */
        .boarding-pass {
            width: 100%;
            height: 297mm;
            background: white;
            position: relative;
            overflow: hidden;
        }

        /* Top Tear Line */
        .tear-line-top {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 25px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-bottom: 2px dashed white;
        }

        /* Main Ticket Body */
        .ticket-body {
            margin: 30px 20px 20px 20px;
            background: white;
            border: 3px solid #667eea;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(102, 126, 234, 0.2);
        }

        /* Header Section - Airline Style */
        .ticket-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 25px 30px;
            color: white;
            position: relative;
        }

        .ticket-header::after {
            content: '';
            position: absolute;
            bottom: -20px;
            left: 0;
            right: 0;
            height: 20px;
            background: white;
            border-radius: 50% 50% 0 0 / 100% 100% 0 0;
        }

        .header-top {
            text-align: center;
            margin-bottom: 15px;
        }

        .airline-name {
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 3px;
            margin-bottom: 5px;
        }

        .booking-class {
            font-size: 11px;
            opacity: 0.9;
            letter-spacing: 1px;
        }

        .booking-number {
            text-align: center;
            background: rgba(255,255,255,0.2);
            padding: 12px 25px;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 3px;
            border: 2px solid rgba(255,255,255,0.4);
            display: inline-block;
        }

        /* Flight Route Style */
        .route-section {
            padding: 25px 30px;
            background: white;
            position: relative;
            z-index: 2;
        }

        .route-container {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .route-from, .route-to {
            display: table-cell;
            width: 40%;
            vertical-align: top;
        }

        .route-arrow {
            display: table-cell;
            width: 20%;
            text-align: center;
            vertical-align: middle;
            font-size: 28px;
            color: #667eea;
        }

        .route-code {
            font-size: 36px;
            font-weight: 700;
            color: #667eea;
            letter-spacing: 2px;
            margin-bottom: 5px;
        }

        .route-name {
            font-size: 14px;
            color: #1f2937;
            font-weight: 600;
            margin-bottom: 3px;
        }

        .route-detail {
            font-size: 9px;
            color: #6b7280;
            text-transform: uppercase;
        }

        /* Passenger Info Bar */
        .passenger-bar {
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            padding: 15px 30px;
            border-top: 2px dashed #d1d5db;
            border-bottom: 2px dashed #d1d5db;
        }

        .passenger-info {
            display: table;
            width: 100%;
        }

        .pax-item {
            display: table-cell;
            width: 25%;
        }

        .pax-label {
            font-size: 8px;
            text-transform: uppercase;
            color: #6b7280;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }

        .pax-value {
            font-size: 12px;
            color: #1f2937;
            font-weight: 700;
        }

        /* Status Badge - Boarding Pass Style */
        .status-section {
            padding: 20px 30px;
            text-align: center;
        }

        .status-badge-large {
            display: inline-block;
            padding: 12px 40px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .status-confirmed { background: #d1fae5; color: #065f46; border: 2px solid #10b981; }
        .status-pending { background: #fef3c7; color: #92400e; border: 2px solid #f59e0b; }
        .status-cancelled { background: #fee2e2; color: #991b1b; border: 2px solid #ef4444; }
        .status-checked_in { background: #dbeafe; color: #1e40af; border: 2px solid #3b82f6; }

        /* Attendees - Compact Grid */
        .attendees-section {
            padding: 20px 30px;
            background: #f9fafb;
        }

        .section-header {
            font-size: 14px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #667eea;
        }

        .attendees-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }

        .attendee-row {
            display: table-row;
            border-bottom: 1px solid #e5e7eb;
        }

        .attendee-cell {
            display: table-cell;
            padding: 10px 8px;
            font-size: 9px;
        }

        .att-number {
            width: 8%;
        }

        .att-name {
            width: 30%;
            font-weight: 600;
            color: #1f2937;
        }

        .att-email {
            width: 32%;
            color: #6b7280;
        }

        .att-ticket {
            width: 22%;
        }

        .att-status {
            width: 8%;
            text-align: center;
        }

        .mini-badge {
            background: #667eea;
            color: white;
            width: 24px;
            height: 24px;
            line-height: 24px;
            display: inline-block;
            border-radius: 50%;
            text-align: center;
            font-weight: 700;
        }

        .mini-ticket {
            font-family: 'Courier New', monospace;
            background: #f3f4f6;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 8px;
        }

        .check-icon {
            color: #10b981;
            font-size: 16px;
        }

        /* Services - Compact */
        .services-section {
            padding: 15px 30px;
            background: white;
        }

        .services-list {
            display: table;
            width: 100%;
        }

        .service-item {
            display: table-row;
        }

        .service-item td {
            display: table-cell;
            padding: 8px 5px;
            font-size: 10px;
            border-bottom: 1px dashed #e5e7eb;
        }

        .service-name-col {
            width: 70%;
            font-weight: 600;
            color: #1f2937;
        }

        .service-price-col {
            width: 30%;
            text-align: right;
            color: #059669;
            font-weight: 700;
        }

        /* Price Summary - Boarding Pass Style */
        .price-section {
            padding: 20px 30px;
            background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
            border-top: 3px dashed #d1d5db;
        }

        .price-grid {
            display: table;
            width: 100%;
        }

        .price-row {
            display: table-row;
        }

        .price-row td {
            display: table-cell;
            padding: 8px 0;
            font-size: 11px;
        }

        .price-label {
            color: #6b7280;
        }

        .price-value {
            text-align: right;
            font-weight: 700;
            color: #1f2937;
        }

        .price-total-row {
            border-top: 3px solid #667eea;
            padding-top: 10px !important;
        }

        .price-total-row td {
            font-size: 18px !important;
            font-weight: 700 !important;
            color: #667eea !important;
            padding-top: 12px !important;
        }

        /* Important Notice */
        .notice-box {
            margin: 15px 30px;
            padding: 12px 15px;
            background: #dbeafe;
            border-left: 4px solid #3b82f6;
            border-radius: 6px;
        }

        .notice-text {
            font-size: 9px;
            color: #1e40af;
            line-height: 1.5;
        }

        /* Footer - Boarding Pass Style */
        .ticket-footer {
            padding: 20px 30px;
            background: #1f2937;
            color: white;
            text-align: center;
            border-radius: 0 0 15px 15px;
        }

        .footer-brand {
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 2px;
            margin-bottom: 8px;
        }

        .footer-info {
            font-size: 8px;
            opacity: 0.8;
            line-height: 1.8;
        }

        .footer-barcode {
            margin: 10px 0;
            height: 40px;
            background: repeating-linear-gradient(
                90deg,
                white 0px,
                white 2px,
                transparent 2px,
                transparent 4px
            );
            opacity: 0.3;
        }

        /* Tear Line Bottom */
        .tear-line-bottom {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-top: 2px dashed white;
        }

        /* Perforations */
        .perforation {
            position: absolute;
            width: 100%;
            height: 2px;
            background: repeating-linear-gradient(
                90deg,
                #d1d5db 0px,
                #d1d5db 10px,
                transparent 10px,
                transparent 20px
            );
        }

        .perf-top {
            top: 25px;
        }
    </style>
</head>
<body>
    <div class="boarding-pass">
        <!-- Top Tear Line -->
        <div class="tear-line-top"></div>
        <div class="perforation perf-top"></div>

        <!-- Main Ticket -->
        <div class="ticket-body">
            <!-- Header -->
            <div class="ticket-header">
                <div class="header-top">
                    <div class="airline-name">{{ strtoupper(config('app.name', 'EVENT PASS')) }}</div>
                    <div class="booking-class">BOARDING PASS - {{ strtoupper($booking->ticketType->getTranslation('name', 'en')) }}</div>
                </div>
                <div style="text-align: center;">
                    <div class="booking-number">{{ $booking->booking_reference }}</div>
                </div>
            </div>

            <!-- Route Section -->
            <div class="route-section">
                <table class="route-container">
                    <tr>
                        <td class="route-from">
                            <div class="route-code">EVT</div>
                            <div class="route-name">{{ $booking->event->getTranslation('title', 'en') }}</div>
                            <div class="route-detail">Event</div>
                        </td>
                        <td class="route-arrow">
                            ✈
                        </td>
                        <td class="route-to">
                            <div class="route-code">{{ strtoupper(substr($booking->event->getTranslation('location', 'en'), 0, 3)) }}</div>
                            <div class="route-name">{{ $booking->event->getTranslation('location', 'en') }}</div>
                            <div class="route-detail">Venue</div>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Passenger Info Bar -->
            <div class="passenger-bar">
                <table class="passenger-info">
                    <tr>
                        <td class="pax-item">
                            <div class="pax-label">Date</div>
                            <div class="pax-value">{{ $booking->event_date->format('d M Y') }}</div>
                        </td>
                        <td class="pax-item">
                            <div class="pax-label">Time</div>
                            <div class="pax-value">{{ $booking->timeSlot->getTimeRange() }}</div>
                        </td>
                        <td class="pax-item">
                            <div class="pax-label">Passengers</div>
                            <div class="pax-value">{{ $booking->quantity }} PAX</div>
                        </td>
                        <td class="pax-item">
                            <div class="pax-label">Booked</div>
                            <div class="pax-value">{{ $booking->created_at->format('d M') }}</div>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Status -->
            <div class="status-section">
                <div class="status-badge-large status-{{ $booking->status }}">
                    @if($booking->status === 'confirmed') ✓ CONFIRMED
                    @elseif($booking->status === 'pending') ⏱ PENDING
                    @elseif($booking->status === 'cancelled') ✗ CANCELLED
                    @elseif($booking->status === 'checked_in') ✓ CHECKED IN
                    @endif
                </div>
            </div>

            <!-- Attendees -->
            <div class="attendees-section">
                <div class="section-header">👥 PASSENGER MANIFEST ({{ $booking->attendees->count() }})</div>
                <table class="attendees-grid">
                    @foreach($booking->attendees as $index => $attendee)
                    <tr class="attendee-row">
                        <td class="attendee-cell att-number">
                            <span class="mini-badge">{{ $index + 1 }}</span>
                        </td>
                        <td class="attendee-cell att-name">{{ $attendee->getFullName() }}</td>
                        <td class="attendee-cell att-email">{{ $attendee->email }}</td>
                        <td class="attendee-cell att-ticket">
                            <span class="mini-ticket">{{ $attendee->ticket_number }}</span>
                        </td>
                        <td class="attendee-cell att-status">
                            @if($attendee->checked_in)
                            <span class="check-icon">✓</span>
                            @else
                            <span style="color: #d1d5db;">○</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </table>
            </div>

            <!-- Services -->
            @if($booking->extraServices->count() > 0)
            <div class="services-section">
                <div class="section-header">➕ ADDITIONAL SERVICES</div>
                <table class="services-list">
                    @foreach($booking->extraServices as $service)
                    <tr class="service-item">
                        <td class="service-name-col">
                            {{ $service->getTranslation('name', 'en') }}
                            <span style="color: #6b7280; font-size: 8px;">
                                ({{ $service->pivot->quantity }}x)
                            </span>
                        </td>
                        <td class="service-price-col">
                            ${{ number_format($service->pivot->quantity * $service->pivot->price, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </table>
            </div>
            @endif

            <!-- Price Summary -->
            <div class="price-section">
                <table class="price-grid">
                    <tr class="price-row">
                        <td class="price-label">Tickets ({{ $booking->quantity }}x ${{ number_format($booking->ticket_price / $booking->quantity, 2) }})</td>
                        <td class="price-value">${{ number_format($booking->ticket_price, 2) }}</td>
                    </tr>
                    @if($booking->services_price > 0)
                    <tr class="price-row">
                        <td class="price-label">Additional Services</td>
                        <td class="price-value">${{ number_format($booking->services_price, 2) }}</td>
                    </tr>
                    @endif
                    <tr class="price-row price-total-row">
                        <td>TOTAL AMOUNT</td>
                        <td style="text-align: right;">${{ number_format($booking->total_price, 2) }}</td>
                    </tr>
                </table>
            </div>

            <!-- Important Notice -->
            <div class="notice-box">
                <div class="notice-text">
                    <strong>⚠ IMPORTANT:</strong> Each passenger must present their individual ticket with QR code at the entrance. 
                    Arrive 15 minutes early for check-in. Keep this boarding pass for your records.
                </div>
            </div>

            <!-- Footer -->
            <div class="ticket-footer">
                <div class="footer-brand">{{ strtoupper(config('app.name', 'EVENT SYSTEM')) }}</div>
                <div class="footer-barcode"></div>
                <div class="footer-info">
                    BOOKING CONFIRMATION • GENERATED {{ strtoupper(now()->format('d M Y H:i')) }}<br>
                    FOR ASSISTANCE CONTACT SUPPORT • {{ strtoupper($booking->event->organizer) }}<br>
                    © {{ date('Y') }} ALL RIGHTS RESERVED
                </div>
            </div>
        </div>

        <!-- Bottom Tear Line -->
        <div class="tear-line-bottom"></div>
    </div>
</body>
</html><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Summary - {{ $booking->booking_reference }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            line-height: 1.6;
            color: #1f2937;
            background: #f9fafb;
        }

        .page {
            background: white;
            padding: 0;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 32px;
            margin-bottom: 8px;
            font-weight: 700;
        }

        .header-subtitle {
            font-size: 16px;
            margin-bottom: 20px;
            opacity: 0.95;
        }

        .booking-ref {
            background: rgba(255, 255, 255, 0.25);
            display: inline-block;
            padding: 12px 30px;
            border-radius: 25px;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 1.5px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            margin-top: 15px;
        }

        /* Content */
        .content {
            padding: 30px;
        }

        /* Status Container */
        .status-container {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: #f3f4f6;
            border-radius: 15px;
        }

        .status-badge {
            display: inline-block;
            padding: 10px 25px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-confirmed {
            background: #d1fae5;
            color: #065f46;
        }

        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-checked_in {
            background: #dbeafe;
            color: #1e40af;
        }

        /* Section */
        .section {
            margin-bottom: 25px;
            background: white;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #e5e7eb;
        }

        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 3px solid #667eea;
        }

        /* Info Table - Using Real Table */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .info-table td {
            padding: 8px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .info-table td:first-child {
            font-weight: 600;
            color: #6b7280;
            font-size: 11px;
            text-transform: uppercase;
            width: 35%;
        }

        .info-table td:last-child {
            color: #1f2937;
            font-weight: 500;
            font-size: 12px;
        }

        .info-table tr:last-child td {
            border-bottom: none;
        }

        /* Highlight Box */
        .highlight-box {
            background: #ede9fe;
            padding: 15px;
            border-radius: 10px;
            border-left: 4px solid #7c3aed;
            margin: 15px 0;
        }

        /* Attendees Table */
        .attendees-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .attendees-table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .attendees-table th {
            padding: 12px 10px;
            text-align: left;
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
        }

        .attendees-table td {
            padding: 10px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 11px;
        }

        .attendees-table tbody tr:nth-child(even) {
            background: #f9fafb;
        }

        .attendee-number {
            display: inline-block;
            width: 28px;
            height: 28px;
            line-height: 28px;
            text-align: center;
            background: #667eea;
            color: white;
            border-radius: 50%;
            font-weight: 700;
        }

        .ticket-number {
            font-family: 'Courier New', monospace;
            background: #f3f4f6;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 9px;
        }

        .check-status {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: 600;
        }

        .checked-in {
            background: #d1fae5;
            color: #065f46;
        }

        .not-checked-in {
            background: #f3f4f6;
            color: #6b7280;
        }

        /* Services Table */
        .services-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .services-table td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        .services-table tr:last-child td {
            border-bottom: none;
        }

        .services-table tr {
            background: #f9fafb;
        }

        .service-name {
            font-weight: 600;
            color: #1f2937;
            font-size: 12px;
        }

        .service-details {
            color: #6b7280;
            font-size: 10px;
            margin-top: 4px;
        }

        .service-price {
            text-align: right;
            font-weight: 700;
            color: #059669;
            font-size: 12px;
        }

        /* Price Summary */
        .price-summary {
            background: #f9fafb;
            padding: 25px;
            border-radius: 12px;
            margin-top: 20px;
            border: 2px solid #e5e7eb;
        }

        .price-table {
            width: 100%;
            border-collapse: collapse;
        }

        .price-table td {
            padding: 10px 0;
            font-size: 12px;
            color: #4b5563;
        }

        .price-table td:last-child {
            text-align: right;
            font-weight: 600;
            color: #1f2937;
        }

        .price-divider {
            border-top: 2px dashed #d1d5db;
            margin: 12px 0;
        }

        .price-total {
            font-size: 20px;
            font-weight: 700;
            color: #059669;
            border-top: 3px solid #059669;
            padding-top: 15px;
        }

        .price-total td {
            font-size: 20px !important;
            color: #059669 !important;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Info Banner */
        .info-banner {
            background: #dbeafe;
            padding: 15px 20px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 4px solid #3b82f6;
        }

        .info-text {
            font-size: 11px;
            color: #1e40af;
            line-height: 1.5;
        }

        /* Footer */
        .footer {
            margin-top: 40px;
            padding: 25px 30px;
            background: #1f2937;
            color: white;
            text-align: center;
            border-radius: 12px;
        }

        .footer-logo {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 15px;
            letter-spacing: 1px;
        }

        .footer-divider {
            width: 60px;
            height: 2px;
            background: rgba(255, 255, 255, 0.3);
            margin: 15px auto;
        }

        .footer-text {
            font-size: 10px;
            opacity: 0.8;
            line-height: 1.8;
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- Header -->
        <div class="header">
            <h1>🎫 Booking Confirmation</h1>
            <div class="header-subtitle">{{ $booking->event->getTranslation('title', 'en') }}</div>
            <div class="booking-ref">{{ $booking->booking_reference }}</div>
        </div>

        <div class="content">
            <!-- Status Badge -->
            <div class="status-container">
                <div class="status-badge status-{{ $booking->status }}">
                    @if($booking->status === 'confirmed') ✓
                    @elseif($booking->status === 'pending') ⏱
                    @elseif($booking->status === 'cancelled') ✗
                    @elseif($booking->status === 'checked_in') ✓
                    @endif
                    {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                </div>
                <div style="margin-top: 10px; font-size: 10px; color: #6b7280;">
                    Booked on {{ $booking->created_at->format('F j, Y \a\t H:i') }}
                </div>
            </div>

            <!-- Event Details Section -->
            <div class="section">
                <div class="section-title">📅 Event Information</div>
                <table class="info-table">
                    <tr>
                        <td>Event Name</td>
                        <td>{{ $booking->event->getTranslation('title', 'en') }}</td>
                    </tr>
                    <tr>
                        <td>Date</td>
                        <td>📆 {{ $booking->event_date->format('l, F j, Y') }}</td>
                    </tr>
                    <tr>
                        <td>Time</td>
                        <td>🕒 {{ $booking->timeSlot->getTimeRange() }}</td>
                    </tr>
                    <tr>
                        <td>Venue</td>
                        <td>📍 {{ $booking->event->getTranslation('location', 'en') }}</td>
                    </tr>
                    <tr>
                        <td>Organized By</td>
                        <td>{{ $booking->event->organizer }}</td>
                    </tr>
                </table>
            </div>

            <!-- Ticket Information -->
            <div class="section">
                <div class="section-title">🎟️ Ticket Details</div>
                <div class="highlight-box">
                    <table class="info-table">
                        <tr>
                            <td>Ticket Type</td>
                            <td style="font-weight: 700; color: #7c3aed;">
                                {{ $booking->ticketType->getTranslation('name', 'en') }}
                            </td>
                        </tr>
                        <tr>
                            <td>Total Tickets</td>
                            <td style="font-weight: 700;">
                                {{ $booking->quantity }} {{ $booking->quantity > 1 ? 'Tickets' : 'Ticket' }}
                            </td>
                        </tr>
                        <tr>
                            <td>Price per Ticket</td>
                            <td style="font-weight: 700; color: #059669;">
                                ${{ number_format($booking->ticket_price / $booking->quantity, 2) }}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Attendees Section -->
            <div class="section">
                <div class="section-title">👥 Attendees ({{ $booking->attendees->count() }})</div>
                <table class="attendees-table">
                    <thead>
                        <tr>
                            <th style="width: 8%;">#</th>
                            <th style="width: 30%;">Full Name</th>
                            <th style="width: 28%;">Email Address</th>
                            <th style="width: 20%;">Ticket Number</th>
                            <th style="width: 14%;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($booking->attendees as $index => $attendee)
                        <tr>
                            <td>
                                <span class="attendee-number">{{ $index + 1 }}</span>
                            </td>
                            <td style="font-weight: 600;">{{ $attendee->getFullName() }}</td>
                            <td style="color: #6b7280;">{{ $attendee->email }}</td>
                            <td>
                                <span class="ticket-number">{{ $attendee->ticket_number }}</span>
                            </td>
                            <td>
                                @if($attendee->checked_in)
                                <span class="check-status checked-in">✓ Checked In</span>
                                @else
                                <span class="check-status not-checked-in">○ Pending</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Extra Services (if any) -->
            @if($booking->extraServices->count() > 0)
            <div class="section">
                <div class="section-title">➕ Extra Services</div>
                <table class="services-table">
                    @foreach($booking->extraServices as $service)
                    <tr>
                        <td style="width: 60%;">
                            <div class="service-name">{{ $service->getTranslation('name', 'en') }}</div>
                            <div class="service-details">
                                Quantity: {{ $service->pivot->quantity }} × ${{ number_format($service->pivot->price, 2) }}
                            </div>
                        </td>
                        <td class="service-price">
                            ${{ number_format($service->pivot->quantity * $service->pivot->price, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </table>
            </div>
            @endif

            <!-- Payment Summary -->
            <div class="section">
                <div class="section-title">💳 Payment Summary</div>
                <div class="price-summary">
                    <table class="price-table">
                        <tr>
                            <td>Tickets ({{ $booking->quantity }} × ${{ number_format($booking->ticket_price / $booking->quantity, 2) }})</td>
                            <td>${{ number_format($booking->ticket_price, 2) }}</td>
                        </tr>
                        @if($booking->services_price > 0)
                        <tr>
                            <td>Extra Services</td>
                            <td>${{ number_format($booking->services_price, 2) }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td colspan="2">
                                <div class="price-divider"></div>
                            </td>
                        </tr>
                        <tr class="price-total">
                            <td>Total Amount Paid</td>
                            <td>${{ number_format($booking->total_price, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Important Information Banner -->
            <div class="info-banner">
                <div class="info-text">
                    <strong>Important:</strong> Each attendee has received their individual ticket with a unique QR code.
                    Please ensure all attendees present their QR codes at the event entrance for smooth check-in.
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <div class="footer-logo">{{ config('app.name', 'Event Booking System') }}</div>
                <div class="footer-divider"></div>
                <div class="footer-text">
                    This is an official booking confirmation.<br>
                    For any inquiries, please contact our support team.<br>
                    <br>
                    Generated on {{ now()->format('F j, Y \a\t H:i') }}<br>
                    © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                </div>
            </div>
        </div>
    </div>
</body>
</html>