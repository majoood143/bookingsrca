<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('event_booking.email.subject', ['reference' => $booking->booking_reference]) }}</title>
    <style>
        body {
            font-family: {{ $locale === 'ar' ? "'Segoe UI', Tahoma, Arial" : "Arial" }}, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
            direction: {{ $locale === 'ar' ? 'rtl' : 'ltr' }};
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0 0 8px;
            font-size: 24px;
        }
        .header p {
            margin: 0;
            opacity: 0.9;
        }
        .content {
            padding: 30px 20px;
        }
        .booking-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .booking-details h3 {
            margin: 0 0 16px;
            font-size: 16px;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .detail-label {
            color: #666;
            {{ $locale === 'ar' ? 'margin-left: 16px;' : 'margin-right: 16px;' }}
        }
        .detail-value {
            font-weight: 600;
            text-align: {{ $locale === 'ar' ? 'left' : 'right' }};
        }
        .qr-code {
            text-align: center;
            margin: 24px 0;
        }
        .qr-code h3 {
            margin: 0 0 12px;
            color: #444;
        }
        .qr-code p {
            margin: 10px 0 0;
            color: #666;
            font-size: 14px;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #eee;
            font-size: 14px;
            color: #666;
        }
        .footer p {
            margin: 4px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ __('event_booking.email.confirmed') }}</h1>
            <p>{{ __('event_booking.email.reference', ['reference' => $booking->booking_reference]) }}</p>
        </div>

        <div class="content">
            @php $primaryAttendee = $booking->attendees->first(); @endphp
            <h2>{{ __('event_booking.email.dear', ['name' => $primaryAttendee ? $primaryAttendee->getFullName() : __('event_booking.email.valued_customer')]) }}</h2>

            <p>{{ __('event_booking.email.details_below') }}</p>

            <div class="booking-details">
                <h3>{{ __('event_booking.email.event_details') }}</h3>

                <div class="detail-row">
                    <span class="detail-label">{{ __('event_booking.email.event') }}</span>
                    <span class="detail-value">{{ $booking->event->getTranslation('title', $locale) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('event_booking.email.location') }}</span>
                    <span class="detail-value">{{ $booking->event->getTranslation('location', $locale) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('event_booking.email.date') }}</span>
                    <span class="detail-value">{{ $booking->event_date->format('l, F j, Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('event_booking.email.time') }}</span>
                    <span class="detail-value">{{ $booking->timeSlot->getTimeRange() }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('event_booking.email.ticket_type') }}</span>
                    <span class="detail-value">{{ $booking->ticketType->getTranslation('name', $locale) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('event_booking.email.quantity') }}</span>
                    <span class="detail-value">{{ $booking->quantity }}</span>
                </div>
                @if($booking->extraServices->isNotEmpty())
                    <div class="detail-row">
                        <span class="detail-label">{{ __('event_booking.email.extra_services') }}</span>
                        <span class="detail-value">
                            @foreach($booking->extraServices as $service)
                                {{ $service->getTranslation('name', $locale) }}@if(!$loop->last), @endif
                            @endforeach
                        </span>
                    </div>
                @endif
                <div class="detail-row">
                    <span class="detail-label">{{ __('event_booking.email.total_amount') }}</span>
                    <span class="detail-value">OMR {{ number_format($booking->total_price, 3) }}</span>
                </div>
            </div>

            @if($primaryAttendee && $primaryAttendee->getQrCodeBase64())
                <div class="qr-code">
                    <h3>{{ __('event_booking.email.qr_heading') }}</h3>
                    <img src="{{ $primaryAttendee->getQrCodeBase64() }}" alt="QR Code" style="max-width: 200px;">
                    <p>{{ __('event_booking.email.qr_notice') }}</p>
                </div>
            @endif

            <p>{{ __('event_booking.email.support') }}</p>
        </div>

        <div class="footer">
            <p>{{ __('event_booking.email.thank_you') }}</p>
            <p>{{ config('app.name') }}</p>
        </div>
    </div>
</body>
</html>
