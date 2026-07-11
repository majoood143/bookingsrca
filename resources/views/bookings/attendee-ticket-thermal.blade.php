<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', 'Tahoma', 'Segoe UI', sans-serif;
            font-size: 22px;
            color: #000;
            background: #fff;
            width: {{ $paperWidth }}px;
            padding: 20px 24px;
            direction: {{ $isRtl ? 'rtl' : 'ltr' }};
        }

        .center {
            text-align: center;
        }

        .logo {
            height: 70px;
            width: auto;
            margin-bottom: 8px;
        }

        .badge {
            display: inline-block;
            border: 2px solid #000;
            padding: 6px 18px;
            border-radius: 30px;
            font-size: 22px;
            font-weight: bold;
            letter-spacing: 1px;
            margin-top: 6px;
        }

        .event-name {
            text-align: center;
            font-size: 30px;
            font-weight: bold;
            margin-top: 14px;
        }

        .divider {
            border-top: 3px dashed #000;
            margin: 18px 0;
        }

        .section-label {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 2px solid #000;
            padding-bottom: 6px;
            margin-bottom: 10px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 5px 0;
            font-size: 22px;
        }

        .info-key {
            font-weight: 600;
        }

        .info-val {
            text-align: {{ $isRtl ? 'left' : 'right' }};
            word-break: break-word;
        }

        .barcode-strip {
            text-align: center;
            padding-top: 6px;
        }

        .barcode-strip img {
            max-width: 100%;
            height: 90px;
        }

        .barcode-ref {
            font-family: 'Courier New', monospace;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 2px;
            margin-top: 8px;
        }

        .barcode-note {
            font-size: 16px;
            margin-top: 6px;
        }

        .footer {
            text-align: center;
            font-size: 16px;
            margin-top: 18px;
        }
    </style>
</head>
<body>
    <div class="center">
        <img class="logo" src="{{ asset('storage/images/horizontalLogo-02.svg') }}" alt="{{ config('app.name') }}">
        <div><span class="badge" dir="ltr">{{ $attendee->ticket_number }}</span></div>
    </div>

    <div class="event-name">{{ $booking->event->getTranslation('title', $locale) }}</div>

    <div class="divider"></div>

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

    <div class="divider"></div>

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

    <div class="divider"></div>

    <div class="barcode-strip">
        <img src="{{ $barcode }}" alt="{{ $t('booking_reference') }}">
        <div class="barcode-ref" dir="ltr">{{ $booking->booking_reference }}</div>
        <div class="barcode-note">{{ $t('present_barcode') }}</div>
    </div>

    <div class="footer">
        <div>{{ config('app.name') }}</div>
        <div>&copy; {{ date('Y') }}</div>
    </div>
</body>
</html>
