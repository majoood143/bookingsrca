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
            font-family: 'DejaVu Sans', 'Courier New', monospace;
            font-size: 22px;
            color: #000;
            background: #fff;
            width: {{ $paperWidth }}px;
            padding: 20px 24px;
        }

        .center {
            text-align: center;
        }

        .title {
            font-size: 28px;
            font-weight: bold;
        }

        .muted {
            color: #333;
            font-size: 18px;
        }

        .divider {
            border-top: 2px dashed #000;
            margin: 14px 0;
        }

        .row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            padding: 4px 0;
            font-size: 20px;
        }

        .row .label {
            color: #000;
        }

        .row .value {
            font-weight: bold;
            text-align: {{ $isRtl ? 'left' : 'right' }};
        }

        .attendee {
            padding: 6px 0;
            border-bottom: 1px dotted #999;
            font-size: 20px;
        }

        .attendee:last-child {
            border-bottom: none;
        }

        .total-row {
            font-size: 24px;
            font-weight: bold;
            border-top: 2px solid #000;
            padding-top: 8px;
            margin-top: 8px;
        }

        .footer {
            margin-top: 16px;
            text-align: center;
            font-size: 16px;
        }

        @if ($isRtl)
            body {
                font-family: 'DejaVu Sans', 'Tahoma', sans-serif;
            }
        @endif
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
                <span class="value">@include('partials.currency-amount', ['amount' => $service->pivot->quantity * $service->pivot->price])</span>
            </div>
        @endforeach
    @endif

    <div class="divider"></div>

    <div class="row"><span class="label">{{ __('booking.receipt.tickets') }}</span><span class="value">@include('partials.currency-amount', ['amount' => $booking->ticket_price])</span></div>
    @if ($booking->services_price > 0)
        <div class="row"><span class="label">{{ __('booking.receipt.services') }}</span><span class="value">@include('partials.currency-amount', ['amount' => $booking->services_price])</span></div>
    @endif
    <div class="row total-row"><span class="label">{{ __('booking.receipt.total') }}</span><span class="value">@include('partials.currency-amount', ['amount' => $booking->total_price])</span></div>

    @if ($booking->payments->count() > 0)
        <div class="divider"></div>
        <div class="muted">{{ __('booking.receipt.payments') }}</div>
        @foreach ($booking->payments as $payment)
            <div class="row">
                <span class="label">{{ __('booking.payments.methods.' . $payment->payment_method) }}</span>
                <span class="value">@include('partials.currency-amount', ['amount' => $payment->amount])</span>
            </div>
        @endforeach
        <div class="row"><span class="label">{{ __('booking.receipt.paid') }}</span><span class="value">@include('partials.currency-amount', ['amount' => $booking->total_paid])</span></div>
        <div class="row"><span class="label">{{ __('booking.receipt.balance_due') }}</span><span class="value">@include('partials.currency-amount', ['amount' => $booking->balance_due])</span></div>
    @endif

    <div class="footer">
        <p>{{ __('booking.receipt.thank_you') }}</p>
        <p>{{ __('booking.receipt.present_note') }}</p>
        <p>{{ now()->format('Y-m-d H:i') }}</p>
    </div>
</body>
</html>
