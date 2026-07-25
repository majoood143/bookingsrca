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
            line-height: 1.5;
            color: #333;
        }

        .header {
            height: 83mm;
            background-size: 100% 100%;
            background-repeat: no-repeat;
            background-position: center top;
            border-radius: 10px;
            margin-bottom: 14px;
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
            width: 40px;
            height: 40px;
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
            margin-bottom: 14px;
        }

        .stats-strip td {
            width: 33.33%;
        }

        .stat-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
        }

        .stat-gap {
            width: 10px;
        }

        .stat-value {
            font-size: 16px;
            font-weight: bold;
            color: #14532d;
        }

        .stat-label {
            font-size: 9px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }

        .section {
            margin-bottom: 12px;
            page-break-inside: avoid;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #14532d;
            margin-bottom: 8px;
            padding-bottom: 4px;
            border-bottom: 2px solid #14532d;
        }

        /* Real tables (not CSS display:table) so labels and values always
           stay on the same line — mPDF does not reliably lay out
           display:table/table-row/table-cell divs as columns. */
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 4px 8px 4px 0;
            vertical-align: top;
            font-size: 12px;
        }

        .info-table td.lbl {
            font-weight: bold;
            color: #6b7280;
            white-space: nowrap;
        }

        .info-table td.val {
            color: #333;
            padding-right: 18px;
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
        .status-refunded { background: #e5e7eb; color: #374151; }

        .attendees-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .attendees-table th {
            background: #f3f4f6;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
            border-bottom: 2px solid #d1d5db;
        }

        .attendees-table td {
            padding: 6px 8px;
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
            padding: 6px 0;
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
            padding: 12px;
            border-radius: 8px;
            margin-top: 8px;
        }

        .price-table {
            width: 100%;
        }

        .price-table td {
            padding: 5px 0;
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
            padding-top: 8px;
        }

        .footer {
            margin-top: 16px;
            padding-top: 10px;
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
            margin: 12px 0;
        }

        .policies-block {
            margin-top: 10px;
        }

        .policies-block:first-of-type {
            margin-top: 0;
        }

        .policies-lang-label {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            margin-bottom: 4px;
        }

        .policies-block ul,
        .policies-block ol {
            margin: 0;
            padding-left: 18px;
        }

        .policies-block li {
            padding: 3px 0;
            font-size: 11px;
        }

        .policies-block.policies-rtl {
            text-align: right;
        }

        .policies-block.policies-rtl ul,
        .policies-block.policies-rtl ol {
            padding-left: 0;
            padding-right: 18px;
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
                    <img src="{{ $qrCode }}" alt="QR code" width="160" height="160">
                    <div class="qr-caption">Scan to verify</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Quick Stats -->
    <table class="stats-strip">
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
                <div class="stat-value">@include('partials.currency-amount', ['amount' => $booking->total_price])</div>
                <div class="stat-label">Total Paid</div>
            </td>
        </tr>
    </table>

    <!-- Booking Status -->
    <div class="section">
        <div class="section-title">Booking Status</div>
        <table class="info-table">
            <colgroup>
                <col style="width: 16%"><col style="width: 34%"><col style="width: 16%"><col style="width: 34%">
            </colgroup>
            <tr>
                <td class="lbl">Status:</td>
                <td class="val">
                    <span class="status-badge status-{{ $booking->status }}">
                        {{ __('booking.options.status.' . $booking->status) }}
                    </span>
                </td>
                <td class="lbl">Booked On:</td>
                <td class="val">{{ $booking->created_at->format('M j, Y - H:i') }}</td>
            </tr>
            @if($booking->confirmed_at || $booking->cancelled_at)
            <tr>
                @if($booking->confirmed_at)
                    <td class="lbl">Confirmed On:</td>
                    <td class="val">{{ $booking->confirmed_at->format('M j, Y - H:i') }}</td>
                @endif
                @if($booking->cancelled_at)
                    <td class="lbl">Cancelled On:</td>
                    <td class="val">{{ $booking->cancelled_at->format('M j, Y - H:i') }}</td>
                @endif
            </tr>
            @endif
            @if($booking->refunded_at)
            <tr>
                <td class="lbl">Refunded On:</td>
                <td class="val" colspan="3">{{ $booking->refunded_at->format('M j, Y - H:i') }}</td>
            </tr>
            @endif
        </table>
    </div>

    <div class="divider"></div>

    <!-- Event Details -->
    <div class="section">
        <div class="section-title">Event Details</div>
        <table class="info-table">
            <colgroup>
                <col style="width: 16%"><col style="width: 34%"><col style="width: 16%"><col style="width: 34%">
            </colgroup>
            <tr>
                <td class="lbl">Event Name:</td>
                <td class="val">{{ $booking->event->getTranslation('title', 'en') }}</td>
                <td class="lbl">Date:</td>
                <td class="val">{{ $booking->event_date->format('M j, Y') }}</td>
            </tr>
            <tr>
                <td class="lbl">Time:</td>
                <td class="val">{{ $booking->timeSlot->getTimeRange() }}</td>
                <td class="lbl">Location:</td>
                <td class="val">{{ $booking->event->getTranslation('location', 'en') }}</td>
            </tr>
            <tr>
                <td class="lbl">Organizer:</td>
                <td class="val" colspan="3">{{ $booking->event->organizer }}</td>
            </tr>
        </table>
    </div>

    <div class="divider"></div>

    <!-- Ticket Information -->
    <div class="section">
        <div class="section-title">Ticket Information</div>
        <table class="info-table">
            <colgroup>
                <col style="width: 16%"><col style="width: 34%"><col style="width: 16%"><col style="width: 34%">
            </colgroup>
            <tr>
                <td class="lbl">Ticket Type:</td>
                <td class="val">{{ $booking->ticketType->getTranslation('name', 'en') }}</td>
                <td class="lbl">Quantity:</td>
                <td class="val">{{ $booking->quantity }} ticket(s)</td>
            </tr>
            <tr>
                <td class="lbl">Price per Ticket:</td>
                <td class="val" colspan="3">@include('partials.currency-amount', ['amount' => $booking->ticket_price / $booking->quantity])</td>
            </tr>
        </table>
    </div>

    <div class="divider"></div>

    <!-- Attendees List -->
    <div class="section">
        <div class="section-title">Attendees ({{ $booking->attendees->count() }})</div>

        <table class="attendees-table">
            <thead>
                <tr>
                    <th style="width: 4%;">#</th>
                    <th style="width: 20%;">Name</th>
                    <th style="width: 22%;">Email</th>
                    <th style="width: 15%;">Ticket Number</th>
                    <th style="width: 15%;">Ticket Type</th>
                    <th style="width: 12%;">Price</th>
                    <th style="width: 12%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($booking->attendees as $index => $attendee)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $attendee->getFullName() }}</td>
                    <td>{{ $attendee->email }}</td>
                    <td style="font-family: monospace; font-size: 10px;">{{ $attendee->ticket_number }}</td>
                    <td>{{ ($attendee->ticketType ?? $booking->ticketType)?->getTranslation('name', 'en') }}</td>
                    <td>@include('partials.currency-amount', ['amount' => $attendee->ticket_price ?? ($booking->ticket_price / $booking->quantity)])</td>
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
                    Quantity: {{ $service->pivot->quantity }} &times; @include('partials.currency-amount', ['amount' => $service->pivot->price])
                    = @include('partials.currency-amount', ['amount' => $service->pivot->quantity * $service->pivot->price])
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
                    <td>Tickets ({{ $booking->quantity }} &times; @include('partials.currency-amount', ['amount' => $booking->ticket_price / $booking->quantity])):</td>
                    <td class="price-amount">@include('partials.currency-amount', ['amount' => $booking->ticket_price])</td>
                </tr>

                @if($booking->services_price > 0)
                <tr>
                    <td>Extra Services:</td>
                    <td class="price-amount">@include('partials.currency-amount', ['amount' => $booking->services_price])</td>
                </tr>
                @endif

                <tr class="price-total-row">
                    <td>Total Amount:</td>
                    <td class="price-amount">@include('partials.currency-amount', ['amount' => $booking->total_price])</td>
                </tr>
            </table>
        </div>
    </div>

    @php
        $admissionPoliciesEn = $booking->event->getTranslation('admission_policies', 'en', false);
        $admissionPoliciesAr = $booking->event->getTranslation('admission_policies', 'ar', false);
    @endphp
    @if($admissionPoliciesEn || $admissionPoliciesAr)
    <div class="divider"></div>

    <!-- Admission & Visiting Policies -->
    <div class="section">
        <div class="section-title">Admission &amp; Visiting Policies</div>

        @if($admissionPoliciesEn)
        <div class="policies-block">
            <div class="policies-lang-label">English</div>
            {!! $admissionPoliciesEn !!}
        </div>
        @endif

        @if($admissionPoliciesAr)
        <div class="policies-block policies-rtl" dir="rtl">
            <div class="policies-lang-label">العربية</div>
            {!! $admissionPoliciesAr !!}
        </div>
        @endif
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p><strong>Important:</strong> Each attendee will receive their individual ticket with a unique QR code.</p>
        <p>Please present the QR code at the event entrance for check-in.</p>
        <p style="margin-top: 8px;">Generated on {{ now()->format('F j, Y \a\t H:i') }}</p>
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
    </div>
</body>
</html>
