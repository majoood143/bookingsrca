@php
    $locale = app()->getLocale();
    $isRtl = $locale === 'ar';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $booking->booking_reference }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', 'Courier New', monospace;
            font-size: 13px;
            color: #111;
            width: 320px;
            margin: 0 auto;
            padding: 16px;
        }

        .center {
            text-align: center;
        }

        .title {
            font-size: 16px;
            font-weight: bold;
        }

        .muted {
            color: #555;
            font-size: 11px;
        }

        .divider {
            border-top: 1px dashed #999;
            margin: 10px 0;
        }

        .row {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            padding: 2px 0;
        }

        .row .label {
            color: #333;
        }

        .row .value {
            font-weight: bold;
            text-align: right;
        }

        .attendee {
            padding: 4px 0;
            border-bottom: 1px dotted #ccc;
        }

        .attendee:last-child {
            border-bottom: none;
        }

        .total-row {
            font-size: 16px;
            font-weight: bold;
            border-top: 1px solid #111;
            padding-top: 6px;
            margin-top: 6px;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 8px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            background: #e5e7eb;
        }

        .footer {
            margin-top: 16px;
            text-align: center;
            font-size: 11px;
            color: #555;
        }

        .no-print {
            text-align: center;
            margin-top: 16px;
        }

        @if ($isRtl)
            body {
                font-family: 'DejaVu Sans', 'Tahoma', sans-serif;
            }

            .row .value {
                text-align: left;
            }
        @endif

        @media print {
            .no-print {
                display: none;
            }

            body {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="center">
        <div class="title">{{ config('app.name') }}</div>
        <div class="muted">{{ $booking->event->getTranslation('location', $locale) ?: '' }}</div>
    </div>

    <div class="divider"></div>

    <div class="row"><span class="label">{{ __('booking.receipt.receipt_number') }}</span><span class="value">{{ $booking->booking_reference }}</span></div>
    <div class="row"><span class="label">{{ __('booking.receipt.date') }}</span><span class="value">{{ $booking->created_at->format('Y-m-d H:i') }}</span></div>
    <div class="row"><span class="label">{{ __('booking.receipt.status') }}</span><span class="value">{{ __('booking.options.status.' . $booking->status) }}</span></div>

    <div class="divider"></div>

    <div class="row"><span class="label">{{ __('booking.receipt.event') }}</span><span class="value">{{ $booking->event->getTranslation('title', $locale) }}</span></div>
    <div class="row"><span class="label">{{ __('booking.receipt.date') }}</span><span class="value">{{ $booking->event_date->format('Y-m-d') }}</span></div>
    <div class="row"><span class="label">{{ __('booking.receipt.time') }}</span><span class="value">{{ $booking->timeSlot->getTimeRange() }}</span></div>
    <div class="row"><span class="label">{{ __('booking.receipt.qty') }}</span><span class="value">{{ $booking->quantity }}</span></div>

    <div class="divider"></div>

    <div class="muted">{{ __('booking.receipt.attendees') }}</div>
    @foreach ($booking->attendees as $index => $attendee)
        <div class="attendee">
            {{ $index + 1 }}. {{ $attendee->getFullName() }}
            <div class="muted">{{ $attendee->ticket_number }}</div>
        </div>
    @endforeach

    @if ($booking->extraServices->count() > 0)
        <div class="divider"></div>
        <div class="muted">{{ __('booking.receipt.extra_services') }}</div>
        @foreach ($booking->extraServices as $service)
            <div class="row">
                <span class="label">{{ $service->getTranslation('name', $locale) }} &times; {{ $service->pivot->quantity }}</span>
                <span class="value">OMR {{ number_format($service->pivot->quantity * $service->pivot->price, 3) }}</span>
            </div>
        @endforeach
    @endif

    <div class="divider"></div>

    <div class="row"><span class="label">{{ __('booking.receipt.tickets') }}</span><span class="value">OMR {{ number_format($booking->ticket_price, 3) }}</span></div>
    @if ($booking->services_price > 0)
        <div class="row"><span class="label">{{ __('booking.receipt.services') }}</span><span class="value">OMR {{ number_format($booking->services_price, 3) }}</span></div>
    @endif
    <div class="row total-row"><span class="label">{{ __('booking.receipt.total') }}</span><span class="value">OMR {{ number_format($booking->total_price, 3) }}</span></div>

    @if ($booking->payments->count() > 0)
        <div class="divider"></div>
        <div class="muted">{{ __('booking.receipt.payments') }}</div>
        @foreach ($booking->payments as $payment)
            <div class="row">
                <span class="label">{{ __('booking.payments.methods.' . $payment->payment_method) }}</span>
                <span class="value">OMR {{ number_format($payment->amount, 3) }}</span>
            </div>
        @endforeach
        <div class="row"><span class="label">{{ __('booking.receipt.paid') }}</span><span class="value">OMR {{ number_format($booking->total_paid, 3) }}</span></div>
        <div class="row"><span class="label">{{ __('booking.receipt.balance_due') }}</span><span class="value">OMR {{ number_format($booking->balance_due, 3) }}</span></div>
    @endif

    <div class="footer">
        <p>{{ __('booking.receipt.thank_you') }}</p>
        <p>{{ __('booking.receipt.present_note') }}</p>
        <p>{{ now()->format('Y-m-d H:i') }}</p>
    </div>

    <div class="no-print">
        <button onclick="window.print()" style="padding:10px 20px;font-size:14px;">Print</button>
    </div>

    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
</body>
</html>
