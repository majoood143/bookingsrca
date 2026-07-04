<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('event_booking.email.tickets_heading') }}</title>
    <style>
        body {
            font-family: {{ $locale === 'ar' ? "'Segoe UI', Tahoma, Arial" : 'Arial' }}, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            direction: {{ $locale === 'ar' ? 'rtl' : 'ltr' }};
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }

        .content {
            background: white;
            padding: 30px;
            border: 1px solid #ddd;
            border-top: none;
        }

        .ticket-card {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .ticket-card table {
            width: 100%;
            border-collapse: collapse;
        }

        .ticket-card td {
            padding: 8px 0;
            vertical-align: top;
        }

        .ticket-card td.label {
            font-weight: bold;
            width: 40%;
            color: #555;
            {{ $locale === 'ar' ? 'padding-left: 12px;' : 'padding-right: 12px;' }}
        }

        .qr-section {
            text-align: center;
            margin: 20px 0;
        }

        .qr-section img {
            max-width: 120px;
        }

        .attachments-list {
            {{ $locale === 'ar' ? 'padding-right: 20px; padding-left: 0;' : 'padding-left: 20px; padding-right: 0;' }}
        }

        .footer {
            background: #f5f5f5;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-radius: 0 0 10px 10px;
        }

        .footer p {
            margin: 4px 0;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>{{ __('event_booking.email.tickets_heading') }}</h1>
        <p>{{ $booking->event->getTranslation('title', $locale) }}</p>
    </div>

    <div class="content">
        <h2>{{ __('event_booking.email.ticket_hello', ['name' => $primary?->first_name]) }}</h2>

        <p>{{ __('event_booking.email.tickets_intro', ['count' => $attendees->count()]) }}</p>

        <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
            <tr>
                <td class="label" style="font-weight: bold; width: 40%; color: #555;">{{ __('event_booking.email.event') }}:</td>
                <td>{{ $booking->event->getTranslation('title', $locale) }}</td>
            </tr>
            <tr>
                <td class="label" style="font-weight: bold; color: #555;">{{ __('event_booking.email.date') }}:</td>
                <td>{{ $booking->event_date->format('l, F j, Y') }}</td>
            </tr>
            <tr>
                <td class="label" style="font-weight: bold; color: #555;">{{ __('event_booking.email.time') }}:</td>
                <td>{{ $booking->timeSlot->getTimeRange() }}</td>
            </tr>
            <tr>
                <td class="label" style="font-weight: bold; color: #555;">{{ __('event_booking.email.location') }}:</td>
                <td>{{ $booking->event->getTranslation('location', $locale) }}</td>
            </tr>
        </table>

        @if ($booking->extraServices->count() > 0)
            <h3>{{ __('event_booking.email.ticket_extra_services') }}:</h3>
            <ul class="attachments-list">
                @foreach ($booking->extraServices as $service)
                    <li>{{ $service->getTranslation('name', $locale) }}</li>
                @endforeach
            </ul>
        @endif

        <h3>{{ __('event_booking.email.tickets_list_heading') }}</h3>

        @foreach ($attendees as $attendee)
            <div class="ticket-card">
                <table>
                    <tr>
                        <td class="label">{{ __('event_booking.email.ticket_number') }}:</td>
                        <td><strong>{{ $attendee->ticket_number }}</strong></td>
                    </tr>
                    <tr>
                        <td class="label">{{ __('event_booking.email.ticket_attendee') }}:</td>
                        <td>{{ $attendee->getFullName() }}</td>
                    </tr>
                    <tr>
                        <td class="label">{{ __('event_booking.email.ticket_type') }}:</td>
                        <td>{{ $booking->ticketType->getTranslation('name', $locale) }}</td>
                    </tr>
                </table>

                <div class="qr-section">
                    <img src="{{ $attendee->getQrCodeBase64() }}" alt="QR Code">
                </div>

                <ul class="attachments-list">
                    <li>{{ __('event_booking.email.ticket_pdf_label', ['number' => $attendee->ticket_number]) }}</li>
                    <li>{{ __('event_booking.email.ticket_qr_label', ['number' => $attendee->ticket_number]) }}</li>
                </ul>
            </div>
        @endforeach

        <p><strong>{{ __('event_booking.email.ticket_qr_important') }}:</strong>
            {{ __('event_booking.email.ticket_qr_notice') }}</p>

        <p>{{ __('event_booking.email.ticket_see_you') }}</p>

        <p style="margin-top: 30px;">
            <small><strong>{{ __('event_booking.email.ticket_reference') }}:</strong>
                {{ $booking->booking_reference }}</small>
        </p>
    </div>

    <div class="footer">
        <p>{{ __('event_booking.email.ticket_automated') }}</p>
        <p>{{ __('event_booking.email.ticket_support') }}</p>
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('event_booking.email.ticket_all_rights') }}</p>
    </div>
</body>

</html>
