<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Booking Confirmation') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
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
            color: black
            padding: 30px 20px;
            text-align: center;
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
        .qr-code {
            text-align: center;
            margin: 20px 0;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #eee;
            font-size: 14px;
            color: #666;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ __('Booking Confirmed!') }}</h1>
            <p>{{ __('Reference: :reference', ['reference' => $booking->booking_reference]) }}</p>
        </div>

        <div class="content">
            @php $primaryAttendee = $booking->attendees->first(); @endphp
            <h2>{{ __('Dear :name,', ['name' => $primaryAttendee ? $primaryAttendee->getFullName() : __('Valued Customer')]) }}</h2>

            <p>{{ __('Your booking has been confirmed. Please find the details below:') }}</p>

            <div class="booking-details">
                <h3>{{ __('Event Details') }}</h3>
                <div class="detail-row">
                    <strong>{{ __('event.navigation.label') }}:</strong>
                    <span>{{ $booking->event->getTranslation('title', app()->getLocale()) }}</span>
                </div>
                <div class="detail-row">
                    <strong>{{ __('Location') }}:</strong>
                    <span>{{ $booking->event->getTranslation('location', app()->getLocale()) }}</span>
                </div>
                <div class="detail-row">
                    <strong>{{ __('Date') }}:</strong>
                    <span>{{ $booking->event_date->format('l, F j, Y') }}</span>
                </div>
                <div class="detail-row">
                    <strong>{{ __('Time') }}:</strong>
                    <span>{{ $booking->timeSlot->getTimeRange() }}</span>
                </div>
                <div class="detail-row">
                    <strong>{{ __('Ticket Type') }}:</strong>
                    <span>{{ $booking->ticketType->getTranslation('name', app()->getLocale()) }}</span>
                </div>
                <div class="detail-row">
                    <strong>{{ __('Quantity') }}:</strong>
                    <span>{{ $booking->quantity }}</span>
                </div>
                @if($booking->extraServices->isNotEmpty())
                    <div class="detail-row">
                        <strong>{{ __('Extra Services') }}:</strong>
                        <span>
                            @foreach($booking->extraServices as $service)
                                {{ $service->getTranslation('name', app()->getLocale()) }}
                                @if(!$loop->last), @endif
                            @endforeach
                        </span>
                    </div>
                @endif
                <div class="detail-row">
                    <strong>{{ __('Total Amount') }}:</strong>
                    <span>OMR {{ number_format($booking->total_price, 3) }}</span>
                </div>
            </div>

            @if($primaryAttendee && $primaryAttendee->getQrCodeBase64())
                <div class="qr-code">
                    <h3>{{ __('Your Ticket QR Code') }}</h3>
                    <img src="{{ $primaryAttendee->getQrCodeBase64() }}" alt="QR Code" style="max-width: 200px;">
                    <p>{{ __('Please present this QR code at the event entrance.') }}</p>
                </div>
            @endif

            <p>{{ __('If you have any questions, please contact our support team.') }}</p>
        </div>

        <div class="footer">
            <p>{{ __('Thank you for booking with us!') }}</p>
            <p>{{ config('app.name') }}</p>
        </div>
    </div>
</body>
</html>
