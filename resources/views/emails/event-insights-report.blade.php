@php
    $title = $event->getTranslation('title', $locale);
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('event_insights.email.subject', ['event' => $title], $locale) }}</title>
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
            background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 { margin: 0 0 6px; font-size: 22px; }
        .header p { margin: 0; opacity: 0.9; font-size: 13px; }
        .content { padding: 30px 20px; }
        .stats-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 20px 0;
        }
        .stat-card {
            flex: 1;
            min-width: 40%;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 12px;
            text-align: center;
        }
        .stat-value { font-size: 18px; font-weight: 700; color: #1d4ed8; }
        .stat-label { font-size: 10px; color: #6b7280; text-transform: uppercase; margin-top: 4px; }
        .footer { padding: 16px 20px; font-size: 11px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ __('event_insights.email.heading', [], $locale) }}</h1>
            <p>{{ $title }} &middot; {{ $from->format('Y-m-d') }} &ndash; {{ $to->format('Y-m-d') }}</p>
        </div>
        <div class="content">
            <p>{{ __('event_insights.email.intro', [], $locale) }}</p>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value">{{ $reportData['totalBookings'] }}</div>
                    <div class="stat-label">{{ __('event_insights.stats.total_bookings', [], $locale) }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ $reportData['totalAttendees'] }}</div>
                    <div class="stat-label">{{ __('event_insights.stats.total_attendees', [], $locale) }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ number_format($reportData['totalRevenue'], 3) }}</div>
                    <div class="stat-label">{{ __('event_insights.stats.total_revenue', [], $locale) }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ $reportData['capacityPct'] !== null ? number_format($reportData['capacityPct'], 1) . '%' : __('event_insights.stats.unlimited', [], $locale) }}</div>
                    <div class="stat-label">{{ __('event_insights.stats.capacity', [], $locale) }}</div>
                </div>
            </div>
            <p>{{ __('event_insights.email.attachment_note', [], $locale) }}</p>
        </div>
        <div class="footer">{{ config('app.name') }}</div>
    </div>
</body>
</html>
