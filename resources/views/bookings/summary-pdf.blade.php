<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking Summary - {{ $booking->booking_reference }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Almarai', 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
        }

        .header {
            height: 83mm;
            background-size: 100% 100%;
            background-repeat: no-repeat;
            background-position: center top;
            border-radius: 10px;
            margin-bottom: 25px;
        }

        .header-table {
            width: 100%;
            height: 83mm;
        }

        .header-text {
            padding: 15mm 10px 23mm 28px;
            vertical-align: middle;
        }

        .header-kicker {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #6b7280;
            margin-bottom: 5px;
        }

        .header .event-name {
            font-size: 19px;
            font-weight: bold;
            color: #14532d;
            margin-bottom: 10px;
        }

        .booking-ref {
            background: #ecfdf5;
            border: 1px solid #14532d;
            color: #14532d;
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .qr-cell {
            width: 110px;
            text-align: center;
            vertical-align: middle;
            padding: 15mm 28px 23mm 10px;
        }

        .qr-cell img {
            width: 60px;
            height: 60px;
            /* background: #fff; */
            padding: 4px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
        }

        .qr-caption {
            font-size: 8px;
            margin-top: 4px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stats-strip {
            width: 100%;
            margin-bottom: 25px;
        }

        .stats-strip td {
            width: 33.33%;
        }

        .stat-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px;
            text-align: center;
        }

        .stat-gap {
            width: 10px;
        }

        .stat-value {
            font-size: 17px;
            font-weight: bold;
            color: #667eea;
        }

        .stat-label {
            font-size: 9px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }

        .section {
            margin-bottom: 22px;
            page-break-inside: avoid;
        }

        .section-title {
            font-size: 15px;
            font-weight: bold;
            color: #14532d;
            margin-bottom: 12px;
            padding-bottom: 6px;
            border-bottom: 2px solid #14532d;
        }

        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 12px;
        }

        .info-row {
            display: table-row;
        }

        .info-label {
            display: table-cell;
            font-weight: bold;
            padding: 6px 15px 6px 0;
            width: 35%;
            color: #666;
        }

        .info-value {
            display: table-cell;
            padding: 6px 0;
            color: #333;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-pending { background: #fef3c7; color: #92400e; }
        .status-confirmed { background: #d1fae5; color: #065f46; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        .status-checked_in { background: #dbeafe; color: #1e40af; }

        .attendees-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        .attendees-table th {
            background: #f3f4f6;
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
            border-bottom: 2px solid #d1d5db;
        }

        .attendees-table td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .attendees-table tr:nth-child(even) {
            background: #f9fafb;
        }

        .checked-in { color: #059669; font-weight: bold; }
        .not-checked-in { color: #6b7280; }

        .services-list {
            list-style: none;
            padding: 0;
        }

        .services-list li {
            padding: 8px 0;
            border-bottom: 1px dashed #e5e7eb;
        }

        .services-list li:last-child {
            border-bottom: none;
        }

        .service-amount {
            color: #6b7280;
            font-size: 11px;
        }

        .price-summary {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 18px;
            border-radius: 8px;
            margin-top: 15px;
        }

        .price-table {
            width: 100%;
        }

        .price-table td {
            padding: 6px 0;
            font-size: 12px;
        }

        .price-table .price-amount {
            text-align: right;
        }

        .price-total-row td {
            font-size: 16px;
            font-weight: bold;
            color: #059669;
            border-top: 2px solid #d1d5db;
            padding-top: 10px;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px dashed #d1d5db;
            text-align: center;
            color: #6b7280;
            font-size: 10px;
        }

        .footer p {
            margin: 2px 0;
        }

        .divider {
            border-top: 2px dashed #d1d5db;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header" style="background-image: url('{{ $headerBg }}');">
        <table class="header-table">
            <tr>
                <td class="header-text">
                    <div class="header-kicker">Booking Summary</div>
                    <div class="event-name">{{ $booking->event->getTranslation('title', 'en') }}</div>
                    <div class="booking-ref">{{ $booking->booking_reference }}</div>
                </td>
                <td class="qr-cell">
                    <img src="{{ $qrCode }}" alt="QR code">
                    <div class="qr-caption">Scan to verify</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Quick Stats -->
    {{-- <table class="stats-strip">
        <tr>
            <td class="stat-box">
                <div class="stat-value">{{ $booking->quantity }}</div>
                <div class="stat-label">Ticket(s)</div>
            </td>
            <td class="stat-gap"></td>
            <td class="stat-box">
                <div class="stat-value">{{ $booking->attendees->count() }}</div>
                <div class="stat-label">Attendees</div>
            </td>
            <td class="stat-gap"></td>
            <td class="stat-box">
                <div class="stat-value">OMR {{ number_format($booking->total_price, 3) }}</div>
                <div class="stat-label">Total Paid</div>
            </td>
        </tr>
    </table> --}}

    <!-- Booking Status -->
    <div class="section">
        <div class="section-title">Booking Status</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Status:</div>
                <div class="info-value">
                    <span class="status-badge status-{{ $booking->status }}">
                        {{ ucfirst($booking->status) }}
                    </span>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Booked On:</div>
                <div class="info-value">{{ $booking->created_at->format('l, F j, Y \a\t H:i') }}</div>
            </div>
            @if($booking->confirmed_at)
            <div class="info-row">
                <div class="info-label">Confirmed On:</div>
                <div class="info-value">{{ $booking->confirmed_at->format('l, F j, Y \a\t H:i') }}</div>
            </div>
            @endif
        </div>
    </div>

    <div class="divider"></div>

    <!-- Event Details -->
    <div class="section">
        <div class="section-title">Event Details</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Event Name:</div>
                <div class="info-value">{{ $booking->event->getTranslation('title', 'en') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Date:</div>
                <div class="info-value">{{ $booking->event_date->format('l, F j, Y') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Time:</div>
                <div class="info-value">{{ $booking->timeSlot->getTimeRange() }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Location:</div>
                <div class="info-value">{{ $booking->event->getTranslation('location', 'en') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Organizer:</div>
                <div class="info-value">{{ $booking->event->organizer }}</div>
            </div>
        </div>
    </div>

    <div class="divider"></div>

    <!-- Ticket Information -->
    <div class="section">
        <div class="section-title">Ticket Information</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Ticket Type:</div>
                <div class="info-value">{{ $booking->ticketType->getTranslation('name', 'en') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Quantity:</div>
                <div class="info-value">{{ $booking->quantity }} ticket(s)</div>
            </div>
            <div class="info-row">
                <div class="info-label">Price per Ticket:</div>
                <div class="info-value">OMR {{ number_format($booking->ticket_price / $booking->quantity, 3) }}</div>
            </div>
        </div>
    </div>

    <div class="divider"></div>

    <!-- Attendees List -->
    <div class="section">
        <div class="section-title">Attendees ({{ $booking->attendees->count() }})</div>

        <table class="attendees-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 30%;">Name</th>
                    <th style="width: 30%;">Email</th>
                    <th style="width: 20%;">Ticket Number</th>
                    <th style="width: 15%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($booking->attendees as $index => $attendee)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $attendee->getFullName() }}</td>
                    <td>{{ $attendee->email }}</td>
                    <td style="font-family: monospace; font-size: 10px;">{{ $attendee->ticket_number }}</td>
                    <td>
                        @if($attendee->checked_in)
                            <span class="checked-in">&#10003; Checked In</span>
                        @else
                            <span class="not-checked-in">Not Checked In</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($booking->extraServices->count() > 0)
    <div class="divider"></div>

    <!-- Extra Services -->
    <div class="section">
        <div class="section-title">Extra Services</div>
        <ul class="services-list">
            @foreach($booking->extraServices as $service)
            <li>
                <strong>{{ $service->getTranslation('name', 'en') }}</strong><br>
                <span class="service-amount">
                    Quantity: {{ $service->pivot->quantity }} &times; OMR {{ number_format($service->pivot->price, 3) }}
                    = OMR {{ number_format($service->pivot->quantity * $service->pivot->price, 3) }}
                </span>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="divider"></div>

    <!-- Price Summary -->
    <div class="section">
        <div class="section-title">Payment Summary</div>
        <div class="price-summary">
            <table class="price-table">
                <tr>
                    <td>Tickets ({{ $booking->quantity }} &times; OMR{{ number_format($booking->ticket_price / $booking->quantity, 3) }}):</td>
                    <td class="price-amount">OMR {{ number_format($booking->ticket_price, 3) }}</td>
                </tr>

                @if($booking->services_price > 0)
                <tr>
                    <td>Extra Services:</td>
                    <td class="price-amount">OMR {{ number_format($booking->services_price, 3) }}</td>
                </tr>
                @endif

                <tr class="price-total-row">
                    <td>Total Amount:</td>
                    <td class="price-amount">OMR {{ number_format($booking->total_price, 3) }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p><strong>Important:</strong> Each attendee will receive their individual ticket with a unique QR code.</p>
        <p>Please present the QR code at the event entrance for check-in.</p>
        <p style="margin-top: 12px;">Generated on {{ now()->format('F j, Y \a\t H:i') }}</p>
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
    </div>
</body>
</html>
