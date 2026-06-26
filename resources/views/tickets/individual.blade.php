<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket - {{ $attendee->ticket_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        .ticket {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .ticket-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .ticket-header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        .ticket-number {
            background: rgba(255,255,255,0.2);
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            margin-top: 10px;
        }
        .ticket-body {
            padding: 40px;
        }
        .section {
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 12px;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 8px;
            font-weight: bold;
        }
        .section-content {
            font-size: 18px;
            color: #333;
            font-weight: 500;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
            margin-top: 30px;
        }
        .qr-code {
            text-align: center;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 10px;
            margin-top: 30px;
        }
        .qr-code img {
            max-width: 200px;
            margin-bottom: 10px;
        }
        .qr-code p {
            font-size: 12px;
            color: #666;
        }
        .footer {
            background: #f9f9f9;
            padding: 20px;
            text-align: center;
            border-top: 2px dashed #ddd;
        }
        .footer p {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        .divider {
            border-bottom: 2px dashed #ddd;
            margin: 30px 0;
        }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="ticket-header">
            <h1>{{ $booking->event->getTranslation('title', 'en') }}</h1>
            <p>Event Ticket</p>
            <div class="ticket-number">{{ $attendee->ticket_number }}</div>
        </div>
        
        <div class="ticket-body">
            <div class="section">
                <div class="section-title">Attendee</div>
                <div class="section-content">{{ $attendee->getFullName() }}</div>
            </div>
            
            <div class="divider"></div>
            
            <div class="info-grid">
                <div class="section">
                    <div class="section-title">Date</div>
                    <div class="section-content">{{ $booking->event_date->format('l, F j, Y') }}</div>
                </div>
                
                <div class="section">
                    <div class="section-title">Time</div>
                    <div class="section-content">{{ $booking->timeSlot->getTimeRange() }}</div>
                </div>
                
                <div class="section">
                    <div class="section-title">Location</div>
                    <div class="section-content">{{ $booking->event->getTranslation('location', 'en') }}</div>
                </div>
                
                <div class="section">
                    <div class="section-title">Ticket Type</div>
                    <div class="section-content">{{ $booking->ticketType->getTranslation('name', 'en') }}</div>
                </div>
            </div>
            
            @if($booking->extraServices->count() > 0)
                <div class="divider"></div>
                
                <div class="section">
                    <div class="section-title">Extra Services</div>
                    <div class="section-content">
                        @foreach($booking->extraServices as $service)
                            • {{ $service->getTranslation('name', 'en') }}<br>
                        @endforeach
                    </div>
                </div>
            @endif
            
            <div class="qr-code">
                <img src="{{ $qrCode ?? public_path('storage/' . $attendee->qr_code) }}" alt="QR Code">
                <p>Present this QR code at the entrance</p>
            </div>
        </div>
        
        <div class="footer">
            <p><strong>Booking Reference:</strong> {{ $booking->booking_reference }}</p>
            <p><strong>Booked on:</strong> {{ $booking->created_at->format('M d, Y H:i') }}</p>
            <p>For questions, please contact event support</p>
        </div>
    </div>
</body>
</html>