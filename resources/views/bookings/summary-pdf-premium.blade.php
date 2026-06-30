<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Premium Booking - {{ $booking->booking_reference }}</title>
    <style>
        @page { margin: 0; }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Almarai', 'DejaVu Sans', sans-serif;
            font-size: 11px;
            line-height: 1.6;
            color: #1f2937;
            background: #ffffff;
        }
        
        /* Hero Header with Wave Pattern */
        .hero-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 50px 40px 80px 40px;
            position: relative;
            overflow: hidden;
        }
        
        .hero-header::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 60px;
            background: white;
            border-radius: 50% 50% 0 0 / 100% 100% 0 0;
        }
        
        .wave-pattern {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 100%;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(255, 255, 255, 0.15) 0%, transparent 30%),
                radial-gradient(circle at 90% 80%, rgba(255, 255, 255, 0.15) 0%, transparent 30%),
                radial-gradient(circle at 50% 50%, rgba(255, 255, 255, 0.08) 0%, transparent 40%);
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
        }
        
        .hero-title {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 8px;
            text-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .hero-subtitle {
            font-size: 18px;
            opacity: 0.95;
            margin-bottom: 25px;
            font-weight: 300;
        }
        
        .booking-badge {
            display: inline-block;
            background: rgba(255,255,255,0.25);
            backdrop-filter: blur(10px);
            padding: 15px 35px;
            border-radius: 30px;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 2px;
            border: 3px solid rgba(255,255,255,0.4);
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
        }
        
        /* Main Content Area */
        .main-content {
            padding: 0 40px 40px 40px;
            margin-top: -30px;
            position: relative;
            z-index: 3;
        }
        
        /* Premium Status Card */
        .status-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.12);
            text-align: center;
            border: 2px solid #f3f4f6;
        }
        
        .status-mega-badge {
            display: inline-block;
            padding: 15px 40px;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.15);
        }
        
        .status-confirmed .status-mega-badge { 
            background: linear-gradient(135deg, #10b981 0%, #059669 100%); 
            color: white;
        }
        .status-pending .status-mega-badge { 
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); 
            color: white;
        }
        .status-cancelled .status-mega-badge { 
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); 
            color: white;
        }
        .status-checked_in .status-mega-badge { 
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); 
            color: white;
        }
        
        .status-date {
            margin-top: 15px;
            font-size: 12px;
            color: #6b7280;
        }
        
        /* Premium Card Layout */
        .card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            border: 1px solid #e5e7eb;
        }
        
        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid transparent;
            border-image: linear-gradient(90deg, #667eea 0%, #764ba2 100%) 1;
        }
        
        .card-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            margin-right: 15px;
            box-shadow: 0 4px 10px rgba(102, 126, 234, 0.4);
        }
        
        .card-title {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
        }
        
        /* Info Row Modern Style */
        .info-modern {
            display: table;
            width: 100%;
            border-spacing: 0 8px;
        }
        
        .info-item {
            display: table-row;
        }
        
        .info-key {
            display: table-cell;
            font-size: 11px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 8px 20px 8px 0;
            width: 35%;
        }
        
        .info-val {
            display: table-cell;
            font-size: 12px;
            font-weight: 500;
            color: #1f2937;
            padding: 8px 0;
        }
        
        /* Premium Attendees Table */
        .premium-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        
        .premium-table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .premium-table th {
            padding: 18px 15px;
            color: white;
            text-align: left;
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .premium-table td {
            padding: 15px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 11px;
        }
        
        .premium-table tbody tr {
            background: white;
            transition: all 0.3s;
        }
        
        .premium-table tbody tr:nth-child(even) {
            background: #f9fafb;
        }
        
        .premium-table tbody tr:hover {
            background: #f3f4f6;
        }
        
        .attendee-avatar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 50%;
            font-weight: 700;
            font-size: 11px;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.4);
        }
        
        .ticket-chip {
            display: inline-block;
            font-family: 'Courier New', monospace;
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 9px;
            letter-spacing: 0.5px;
            border: 1px solid #d1d5db;
        }
        
        /* Mega Price Summary */
        .mega-price-box {
            background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
            border-radius: 15px;
            padding: 30px;
            border: 3px solid #e5e7eb;
            box-shadow: inset 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .price-line {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            font-size: 13px;
            color: #4b5563;
            border-bottom: 1px dashed #d1d5db;
        }
        
        .price-line:last-of-type {
            border-bottom: none;
        }
        
        .price-total-mega {
            display: flex;
            justify-content: space-between;
            padding: 20px 0 0 0;
            margin-top: 15px;
            border-top: 3px solid #059669;
            font-size: 22px;
            font-weight: 700;
            color: #059669;
        }
        
        /* Premium Footer */
        .premium-footer {
            margin-top: 50px;
            padding: 30px 40px;
            background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
            color: white;
            text-align: center;
        }
        
        .footer-brand {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 10px;
            letter-spacing: 2px;
        }
        
        .footer-separator {
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, transparent, white, transparent);
            margin: 20px auto;
        }
        
        .footer-info {
            font-size: 10px;
            line-height: 2;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <!-- Ultra Premium Design -->
    <div class="hero-header">
        <div class="wave-pattern"></div>
        <div class="hero-content">
            <div class="hero-title">🎫 BOOKING CONFIRMED</div>
            <div class="hero-subtitle">{{ $booking->event->getTranslation('title', 'en') }}</div>
            <div class="booking-badge">{{ $booking->booking_reference }}</div>
        </div>
    </div>

    <div class="main-content">
        <!-- Mega Status Card -->
        <div class="status-card status-{{ $booking->status }}">
            <div class="status-mega-badge">
                {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
            </div>
            <div class="status-date">
                Booked on {{ $booking->created_at->format('F j, Y \a\t H:i') }}
            </div>
        </div>

        <!-- Event Card -->
        <div class="card">
            <div class="card-header">
                <div class="card-icon">📅</div>
                <h2 class="card-title">Event Information</h2>
            </div>
            <div class="info-modern">
                <div class="info-item">
                    <div class="info-key">Event Name</div>
                    <div class="info-val">{{ $booking->event->getTranslation('title', 'en') }}</div>
                </div>
                <div class="info-item">
                    <div class="info-key">Date & Time</div>
                    <div class="info-val">
                        📆 {{ $booking->event_date->format('l, F j, Y') }} • 
                        🕒 {{ $booking->timeSlot->getTimeRange() }}
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-key">Venue</div>
                    <div class="info-val">📍 {{ $booking->event->getTranslation('location', 'en') }}</div>
                </div>
            </div>
        </div>

        <!-- Attendees Card -->
        <div class="card">
            <div class="card-header">
                <div class="card-icon">👥</div>
                <h2 class="card-title">Attendees ({{ $booking->attendees->count() }})</h2>
            </div>
            
            <table class="premium-table">
                <thead>
                    <tr>
                        <th style="width: 8%;">#</th>
                        <th style="width: 32%;">Name</th>
                        <th style="width: 30%;">Email</th>
                        <th style="width: 22%;">Ticket</th>
                        <th style="width: 8%;">✓</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($booking->attendees as $index => $attendee)
                    <tr>
                        <td>
                            <div class="attendee-avatar">{{ $index + 1 }}</div>
                        </td>
                        <td style="font-weight: 600;">{{ $attendee->getFullName() }}</td>
                        <td style="color: #6b7280;">{{ $attendee->email }}</td>
                        <td><span class="ticket-chip">{{ $attendee->ticket_number }}</span></td>
                        <td style="text-align: center;">
                            @if($attendee->checked_in)
                                <span style="color: #10b981; font-size: 16px;">✓</span>
                            @else
                                <span style="color: #d1d5db; font-size: 16px;">○</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($booking->extraServices->count() > 0)
        <!-- Services Card -->
        <div class="card">
            <div class="card-header">
                <div class="card-icon">➕</div>
                <h2 class="card-title">Extra Services</h2>
            </div>
            <div class="info-modern">
                @foreach($booking->extraServices as $service)
                <div class="info-item">
                    <div class="info-key">{{ $service->getTranslation('name', 'en') }}</div>
                    <div class="info-val" style="color: #059669; font-weight: 700;">
                        ${{ number_format($service->pivot->quantity * $service->pivot->price, 2) }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Payment Card -->
        <div class="card">
            <div class="card-header">
                <div class="card-icon">💳</div>
                <h2 class="card-title">Payment Summary</h2>
            </div>
            <div class="mega-price-box">
                <div class="price-line">
                    <div>Tickets ({{ $booking->quantity }} × ${{ number_format($booking->ticket_price / $booking->quantity, 2) }})</div>
                    <div style="font-weight: 600;">${{ number_format($booking->ticket_price, 2) }}</div>
                </div>
                @if($booking->services_price > 0)
                <div class="price-line">
                    <div>Extra Services</div>
                    <div style="font-weight: 600;">${{ number_format($booking->services_price, 2) }}</div>
                </div>
                @endif
                <div class="price-total-mega">
                    <div>TOTAL PAID</div>
                    <div>${{ number_format($booking->total_price, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Premium Footer -->
    <div class="premium-footer">
        <div class="footer-brand">{{ strtoupper(config('app.name', 'EVENT SYSTEM')) }}</div>
        <div class="footer-separator"></div>
        <div class="footer-info">
            Official Booking Confirmation<br>
            Generated {{ now()->format('F j, Y') }}<br>
            © {{ date('Y') }} All Rights Reserved
        </div>
    </div>
</body>
</html>