@php
    $isAr  = ($locale ?? 'en') === 'ar';
    $dir   = $isAr ? 'rtl' : 'ltr';
    $lang  = $isAr ? 'ar' : 'en';
    $t     = fn(string $key) => trans("event_booking.ticket.$key", [], $lang);
    $dateFormatted = $isAr
        ? $booking->event_date->locale('ar')->translatedFormat('l، j F Y')
        : $booking->event_date->format('l, F j, Y');
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}" dir="{{ $dir }}">
<head>
    <meta charset="UTF-8">
    <title>{{ $t('event_ticket') }} - {{ $attendee->ticket_number }}</title>
    <style>
        /* "size" is intentionally omitted: this mPDF version mis-handles a
           named @page size (e.g. "A4 portrait"), causing runaway pagination
           (hundreds of extra pages) even for trivial content. Page format is
           already set via the Mpdf 'format' constructor option in PHP. */
        @page {
            margin: 14mm 12mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Almarai', 'DejaVu Sans', sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #1f2937;
            direction: {{ $dir }};
            background: #fff;
        }

        /* ── TICKET WRAPPER ── */
        .ticket {
            border: 1px solid #d1d5db;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(0,0,0,.08);
        }

        /* ── HEADER BANNER ── */
        /* mPDF's background-size:cover resize math divides by zero on this
           wide-aspect image, so the "cover" crop is emulated manually with a
           fixed height + overflow:hidden instead. */
        .header {
            background-size: 100% auto;
            background-position: center;
            background-repeat: no-repeat;
            height: 52mm;
            overflow: hidden;
            padding: 0;
        }

        .header-inner {
            display: table;
            width: 100%;
            min-height: 52mm;
            background: linear-gradient(
                {{ $isAr ? '270deg' : '90deg' }},
                rgba(0,0,0,.55) 0%,
                rgba(0,0,0,.15) 60%,
                transparent 100%
            );
        }

        .header-left {
            display: table-cell;
            vertical-align: middle;
            @if($isAr)
            padding: 12mm 20px 12mm 12px;
            text-align: right;
            @else
            padding: 12mm 12px 12mm 20px;
            text-align: left;
            @endif
        }

        .header-kicker {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: rgba(255,255,255,.75);
            margin-bottom: 5px;
        }

        .event-name {
            font-size: 18px;
            font-weight: bold;
            color: #fff;
            margin-bottom: 8px;
            line-height: 1.3;
        }

        .ticket-badge {
            display: inline-block;
            background: rgba(255,255,255,.18);
            border: 1px solid rgba(255,255,255,.5);
            color: #fff;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .header-right {
            display: table-cell;
            vertical-align: middle;
            width: 90px;
            text-align: center;
            @if($isAr)
            padding: 12mm 20px 12mm 12px;
            @else
            padding: 12mm 20px 12mm 12px;
            @endif
        }

        .header-right img {
            width: 68px;
            height: 68px;
            border-radius: 8px;
            border: 3px solid rgba(255,255,255,.85);
            padding: 3px;
            background: #fff;
            display: block;
            margin: 0 auto 4px;
        }

        .qr-caption {
            font-size: 7px;
            color: rgba(255,255,255,.8);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ── TEAR LINE ── */
        .tear-line {
            display: table;
            width: 100%;
            background: #f3f4f6;
        }

        .tear-line-left {
            display: table-cell;
            width: 16px;
        }

        .tear-line-right {
            display: table-cell;
            width: 16px;
        }

        .tear-line-notch {
            width: 16px;
            height: 16px;
            background: #fff;
            border-radius: 50%;
        }

        .tear-line-left .tear-line-notch {
            border-top-right-radius: 50%;
            border-bottom-right-radius: 50%;
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }

        .tear-line-right .tear-line-notch {
            border-top-left-radius: 50%;
            border-bottom-left-radius: 50%;
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        .tear-line-dash {
            display: table-cell;
            border-top: 1.5px dashed #9ca3af;
            vertical-align: middle;
        }

        /* ── BODY ── */
        .ticket-body {
            padding: 14px 20px 16px;
            background: #fff;
        }

        /* ── TWO-COLUMN GRID ── */
        .two-col {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        .col {
            width: 49.5%;
            vertical-align: top;
        }

        .col-divider {
            width: 1px;
            background: #e5e7eb;
        }

        /* ── SECTION ── */
        .section-label {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #667eea;
            border-bottom: 1.5px solid #667eea;
            padding-bottom: 4px;
            margin-bottom: 9px;
            text-align: {{ $isAr ? 'right' : 'left' }};
        }

        /* ── INFO ROWS ── */
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 4px 0;
            vertical-align: top;
        }

        .info-key {
            font-weight: 600;
            color: #6b7280;
            font-size: 10px;
            width: 38%;
            text-align: {{ $isAr ? 'right' : 'left' }};
            @if($isAr)
            padding-left: 8px;
            @else
            padding-right: 8px;
            @endif
        }

        .info-val {
            color: #1f2937;
            font-size: 11px;
            text-align: {{ $isAr ? 'right' : 'left' }};
        }

        /* ── SERVICES ── */
        .services-list {
            list-style: none;
        }

        .services-list li {
            padding: 5px 0;
            border-bottom: 1px dashed #e5e7eb;
            text-align: {{ $isAr ? 'right' : 'left' }};
            font-size: 10px;
        }

        .services-list li:last-child {
            border-bottom: none;
        }

        .svc-qty {
            color: #9ca3af;
            font-size: 9px;
        }

        /* ── QR ENTRY STRIP ── */
        .entry-strip {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px 16px;
            display: table;
            width: 100%;
            margin-top: 14px;
        }

        .entry-qr-cell {
            display: table-cell;
            vertical-align: middle;
            width: 90px;
            text-align: center;
        }

        .entry-qr-cell img {
            width: 72px;
            height: 72px;
            padding: 4px;
            background: #fff;
            border: 1px solid #d1d5db;
            border-radius: 6px;
        }

        .entry-info-cell {
            display: table-cell;
            vertical-align: middle;
            @if($isAr)
            padding-right: 14px;
            text-align: right;
            @else
            padding-left: 14px;
            text-align: left;
            @endif
        }

        .entry-label {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #9ca3af;
            margin-bottom: 4px;
        }

        .entry-ticket-num {
            font-family: 'Courier New', monospace;
            font-size: 15px;
            font-weight: bold;
            color: #14532d;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }

        .entry-note {
            font-size: 10px;
            color: #6b7280;
            line-height: 1.4;
        }

        /* ── FOOTER ── */
        .ticket-footer {
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            padding: 9px 20px;
            display: table;
            width: 100%;
        }

        .footer-ref {
            display: table-cell;
            vertical-align: middle;
            text-align: {{ $isAr ? 'right' : 'left' }};
        }

        .footer-copy {
            display: table-cell;
            vertical-align: middle;
            text-align: {{ $isAr ? 'left' : 'right' }};
        }

        .footer-ref, .footer-copy {
            font-size: 9px;
            color: #9ca3af;
        }

        .footer-ref strong {
            color: #6b7280;
        }
    </style>
</head>
<body>
<div class="ticket">

    {{-- ── HEADER ── --}}
    <div class="header" style="background-image: url('{{ $headerBg }}');">
        <div class="header-inner">
            @if($isAr)
            <div class="header-right">
                <img src="{{ $qrCode }}" alt="QR">
                <div class="qr-caption">{{ $t('scan_to_verify') }}</div>
            </div>
            <div class="header-left">
                <div class="header-kicker">{{ $t('event_ticket') }}</div>
                <div class="event-name">{{ $booking->event->getTranslation('title', 'ar') }}</div>
                <span class="ticket-badge" dir="ltr">{{ $attendee->ticket_number }}</span>
            </div>
            @else
            <div class="header-left">
                <div class="header-kicker">{{ $t('event_ticket') }}</div>
                <div class="event-name">{{ $booking->event->getTranslation('title', 'en') }}</div>
                <span class="ticket-badge">{{ $attendee->ticket_number }}</span>
            </div>
            <div class="header-right">
                <img src="{{ $qrCode }}" alt="QR">
                <div class="qr-caption">{{ $t('scan_to_verify') }}</div>
            </div>
            @endif
        </div>
    </div>

    {{-- ── TEAR LINE ── --}}
    <div class="tear-line">
        <div class="tear-line-left"><div class="tear-line-notch"></div></div>
        <div class="tear-line-dash"></div>
        <div class="tear-line-right"><div class="tear-line-notch"></div></div>
    </div>

    {{-- ── BODY ── --}}
    <div class="ticket-body">

        {{-- Two-column: Attendee | Event Details --}}
        {{-- A real <table> is used here (rather than CSS display:table/table-cell
             divs) because this mPDF version doesn't lay out table-cell divs
             side by side - it renders each as a full-width block and forces a
             page break between them. --}}
        <table class="two-col">
            <tr>
                <td class="col">
                    <div class="section-label">{{ $t('attendee') }}</div>
                    <table class="info-table">
                        <tr>
                            <td class="info-key">{{ $t('name') }}</td>
                            <td class="info-val">{{ $attendee->getFullName() }}</td>
                        </tr>
                        <tr>
                            <td class="info-key">{{ $t('email') }}</td>
                            <td class="info-val" style="word-break:break-all;">{{ $attendee->email }}</td>
                        </tr>
                        <tr>
                            <td class="info-key">{{ $t('ticket_type') }}</td>
                            <td class="info-val">{{ $booking->ticketType->getTranslation('name', $lang) }}</td>
                        </tr>
                    </table>
                </td>
                <td class="col-divider"></td>
                <td class="col" style="{{ $isAr ? 'padding-right:16px' : 'padding-left:16px' }}">
                    <div class="section-label">{{ $t('event_details') }}</div>
                    <table class="info-table">
                        <tr>
                            <td class="info-key">{{ $t('date') }}</td>
                            <td class="info-val">{{ $dateFormatted }}</td>
                        </tr>
                        <tr>
                            <td class="info-key">{{ $t('time') }}</td>
                            <td class="info-val">{{ $booking->timeSlot->getTimeRange() }}</td>
                        </tr>
                        <tr>
                            <td class="info-key">{{ $t('location') }}</td>
                            <td class="info-val">{{ $booking->event->getTranslation('location', $lang) }}</td>
                        </tr>
                        <tr>
                            <td class="info-key">{{ $t('organizer') }}</td>
                            <td class="info-val">{{ $booking->event->organizer }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- Extra Services (full-width, only if present) --}}
        @if($booking->extraServices->count() > 0)
        <div style="margin-bottom:14px;">
            <div class="section-label">{{ $t('extra_services') }}</div>
            <ul class="services-list">
                @foreach($booking->extraServices as $service)
                <li>
                    <strong>{{ $service->getTranslation('name', $lang) }}</strong>
                    &nbsp;<span class="svc-qty">× {{ $service->pivot->quantity }}</span>
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Entry Pass strip --}}
        <div class="entry-strip">
            @if($isAr)
            <div class="entry-info-cell">
                <div class="entry-label">{{ $t('entry_pass') }}</div>
                <div class="entry-ticket-num" dir="ltr">{{ $attendee->ticket_number }}</div>
                <div class="entry-note">{{ $t('present_qr') }}</div>
            </div>
            <div class="entry-qr-cell">
                <img src="{{ $qrCode }}" alt="QR">
            </div>
            @else
            <div class="entry-qr-cell">
                <img src="{{ $qrCode }}" alt="QR">
            </div>
            <div class="entry-info-cell">
                <div class="entry-label">{{ $t('entry_pass') }}</div>
                <div class="entry-ticket-num">{{ $attendee->ticket_number }}</div>
                <div class="entry-note">{{ $t('present_qr') }}</div>
            </div>
            @endif
        </div>

    </div>{{-- /ticket-body --}}

    {{-- ── FOOTER ── --}}
    <div class="ticket-footer">
        <div class="footer-ref">
            <strong>{{ $t('booking_reference') }}:</strong>
            <span dir="ltr"> {{ $booking->booking_reference }}</span>
            &nbsp;|&nbsp;
            <strong>{{ $t('booked_on') }}:</strong>
            <span dir="ltr"> {{ $booking->created_at->format('M d, Y H:i') }}</span>
        </div>
        <div class="footer-copy">
            &copy; {{ date('Y') }} {{ config('app.name') }}. {{ $t('all_rights') }}
        </div>
    </div>

</div>{{-- /ticket --}}
</body>
</html>
