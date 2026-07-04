@php
    $locale = app()->getLocale();
    $isRtl = $locale === 'ar';
    $t = fn (string $key, array $replace = []) => trans("event_booking.ticket.$key", $replace, $locale);
    $dateFormatted = $isRtl
        ? $booking->event_date->locale('ar')->translatedFormat('l، j F Y')
        : $booking->event_date->format('l, F j, Y');
    $barcode = $booking->getBookingReferenceBarcodeBase64();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $t('event_ticket') }} - {{ $booking->booking_reference }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', 'Tahoma', 'Segoe UI', sans-serif;
            font-size: 13px;
            color: #1f2937;
            background: #e5e7eb;
            direction: {{ $isRtl ? 'rtl' : 'ltr' }};
        }

        .page {
            max-width: 720px;
            margin: 24px auto;
            padding: 0 16px;
        }

        .ticket {
            background: #fff;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(0,0,0,.08);
            margin-bottom: 28px;
            page-break-after: always;
            break-after: page;
        }

        .ticket:last-child {
            page-break-after: auto;
            break-after: auto;
            margin-bottom: 0;
        }

        .ticket-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 18px 24px;
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
        }

        .ticket-header img.logo {
            height: 40px;
            width: auto;
        }

        .ticket-badge {
            display: inline-block;
            background: #ecfdf5;
            border: 1px solid #14532d;
            color: #14532d;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: .5px;
        }

        .event-name {
            padding: 16px 24px 0;
            font-size: 19px;
            font-weight: bold;
            color: #14532d;
        }

        .ticket-body {
            padding: 16px 24px 20px;
        }

        .two-col {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .two-col td {
            width: 50%;
            vertical-align: top;
            padding-{{ $isRtl ? 'left' : 'right' }}: 16px;
        }

        .section-label {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #667eea;
            border-bottom: 1.5px solid #667eea;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            padding: 4px 0;
        }

        .info-key {
            font-weight: 600;
            color: #6b7280;
            font-size: 11px;
        }

        .info-val {
            color: #1f2937;
            font-size: 12px;
            text-align: {{ $isRtl ? 'left' : 'right' }};
            word-break: break-word;
        }

        .divider {
            border-top: 1.5px dashed #d1d5db;
            margin: 16px 0;
        }

        .barcode-strip {
            text-align: center;
            padding-top: 4px;
        }

        .barcode-strip img {
            max-width: 260px;
            height: 60px;
        }

        .barcode-ref {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 2px;
            color: #14532d;
            margin-top: 6px;
        }

        .barcode-note {
            font-size: 10px;
            color: #6b7280;
            margin-top: 4px;
        }

        .ticket-footer {
            display: flex;
            justify-content: space-between;
            padding: 9px 24px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            font-size: 9px;
            color: #9ca3af;
        }

        .no-print {
            text-align: center;
            margin: 0 0 20px;
        }

        @media print {
            body {
                background: #fff;
            }

            .page {
                max-width: 100%;
                margin: 0;
                padding: 0;
            }

            .ticket {
                margin-bottom: 0;
                border-radius: 0;
                box-shadow: none;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
<div class="page">
    <div class="no-print">
        <button onclick="window.print()" style="padding:10px 20px;font-size:14px;">Print</button>
    </div>

    @foreach ($booking->attendees as $attendee)
        <div class="ticket">
            <div class="ticket-header">
                <img class="logo" src="{{ asset('storage/images/horizontalLogo-02.svg') }}" alt="{{ config('app.name') }}">
                <span class="ticket-badge" dir="ltr">{{ $attendee->ticket_number }}</span>
            </div>

            <div class="event-name">{{ $booking->event->getTranslation('title', $locale) }}</div>

            <div class="ticket-body">
                <table class="two-col">
                    <tr>
                        <td>
                            <div class="section-label">{{ $t('attendee') }}</div>
                            <div class="info-row">
                                <span class="info-key">{{ $t('name') }}</span>
                                <span class="info-val">{{ $attendee->getFullName() }}</span>
                            </div>
                            @if($attendee->email)
                                <div class="info-row">
                                    <span class="info-key">{{ $t('email') }}</span>
                                    <span class="info-val">{{ $attendee->email }}</span>
                                </div>
                            @endif
                            @if($attendee->phone)
                                <div class="info-row">
                                    <span class="info-key">{{ $t('phone') }}</span>
                                    <span class="info-val" dir="ltr">{{ $attendee->phone }}</span>
                                </div>
                            @endif
                            <div class="info-row">
                                <span class="info-key">{{ $t('ticket_type') }}</span>
                                <span class="info-val">{{ $attendee->ticketType?->getTranslation('name', $locale) }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="section-label">{{ $t('event_details') }}</div>
                            <div class="info-row">
                                <span class="info-key">{{ $t('date') }}</span>
                                <span class="info-val">{{ $dateFormatted }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-key">{{ $t('time') }}</span>
                                <span class="info-val">{{ $booking->timeSlot->getTimeRange() }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-key">{{ $t('location') }}</span>
                                <span class="info-val">{{ $booking->event->getTranslation('location', $locale) }}</span>
                            </div>
                        </td>
                    </tr>
                </table>

                <div class="divider"></div>

                <div class="barcode-strip">
                    <img src="{{ $barcode }}" alt="{{ $t('booking_reference') }}">
                    <div class="barcode-ref" dir="ltr">{{ $booking->booking_reference }}</div>
                    <div class="barcode-note">{{ $t('present_barcode') }}</div>
                </div>
            </div>

            <div class="ticket-footer">
                <span>{{ $t('booking_reference') }}: <span dir="ltr">{{ $booking->booking_reference }}</span></span>
                <span>&copy; {{ date('Y') }} {{ config('app.name') }}</span>
            </div>
        </div>
    @endforeach
</div>

<script>
    window.addEventListener('load', function () {
        window.print();
    });
</script>
</body>
</html>
