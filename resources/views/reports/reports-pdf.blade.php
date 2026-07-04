@php
    $isRtl = $locale === 'ar';
    $chartJs = file_get_contents(public_path('vendor/chartjs/chart.umd.js'));
    $fmt = fn($n) => 'OMR ' . number_format((float) $n, 3);

    $eventLabels = $byEvent->pluck('event')->values();
    $eventData = $byEvent->pluck('revenue')->values();

    $ticketLabels = $byTicket->pluck('ticket_type')->values();
    $ticketData = $byTicket->pluck('revenue')->values();
@endphp
<!DOCTYPE html>
<html dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
<meta charset="UTF-8">
@include('reports.partials.pdf-fonts')
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'Almarai', 'Segoe UI', Tahoma, Arial, sans-serif;
        font-size: 12px;
        color: #1f2937;
    }
    .section { margin-bottom: 22px; }
    .section-title {
        font-size: 15px;
        font-weight: 700;
        color: #1e3a8a;
        border-bottom: 2px solid #1e3a8a;
        padding-bottom: 6px;
        margin-bottom: 14px;
    }
    .stats-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 8px;
    }
    .stat-card {
        flex: 1;
        min-width: 18%;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 8px;
        padding: 12px;
        text-align: center;
    }
    .stat-value { font-size: 17px; font-weight: 700; color: #1d4ed8; }
    .stat-label { font-size: 9px; color: #6b7280; text-transform: uppercase; margin-top: 4px; }

    .chart-wrap {
        width: 100%;
        height: 230px;
        margin-bottom: 14px;
    }

    table { width: 100%; border-collapse: collapse; font-size: 11px; }
    th {
        background: #1e3a8a;
        color: #ffffff;
        padding: 7px 10px;
        text-align: {{ $isRtl ? 'right' : 'left' }};
        font-weight: 600;
    }
    td {
        padding: 6px 10px;
        border-bottom: 1px solid #e5e7eb;
    }
    tr:nth-child(even) td { background: #f9fafb; }
    .text-center { text-align: center; }
    .text-end { text-align: {{ $isRtl ? 'left' : 'right' }}; }
    .page-break { page-break-before: always; }
</style>
</head>
<body>

    <div class="section">
        <div class="section-title">{{ __('reports.sections.summary', [], $locale) }}</div>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value">{{ $totalBookings }}</div>
                <div class="stat-label">{{ __('reports.stats.total_bookings', [], $locale) }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $confirmedCount }}</div>
                <div class="stat-label">{{ __('reports.stats.confirmed', [], $locale) }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $pendingCount }}</div>
                <div class="stat-label">{{ __('reports.stats.pending', [], $locale) }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $cancelledCount }}</div>
                <div class="stat-label">{{ __('reports.stats.cancelled', [], $locale) }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $checkedInCount }}</div>
                <div class="stat-label">{{ __('reports.stats.checked_in', [], $locale) }}</div>
            </div>
        </div>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value">{{ $totalAttendees }}</div>
                <div class="stat-label">{{ __('reports.stats.total_attendees', [], $locale) }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $fmt($totalRevenue) }}</div>
                <div class="stat-label">{{ __('reports.stats.total_revenue', [], $locale) }}</div>
            </div>
        </div>
    </div>

    @if ($totalBookings === 0)
        <p>{{ __('reports.no_data', [], $locale) }}</p>
    @else

    <div class="section">
        <div class="section-title">{{ __('reports.sections.by_event', [], $locale) }}</div>
        <div class="chart-wrap"><canvas id="eventChart"></canvas></div>
        <table>
            <thead>
                <tr>
                    <th>{{ __('reports.columns.event', [], $locale) }}</th>
                    <th class="text-center">{{ __('reports.columns.total', [], $locale) }}</th>
                    <th class="text-center">{{ __('reports.columns.confirmed', [], $locale) }}</th>
                    <th class="text-center">{{ __('reports.columns.pending', [], $locale) }}</th>
                    <th class="text-center">{{ __('reports.columns.cancelled', [], $locale) }}</th>
                    <th class="text-center">{{ __('reports.columns.checked_in', [], $locale) }}</th>
                    <th class="text-center">{{ __('reports.columns.attendees', [], $locale) }}</th>
                    <th class="text-end">{{ __('reports.columns.revenue', [], $locale) }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($byEvent as $row)
                    <tr>
                        <td>{{ $row['event'] }}</td>
                        <td class="text-center">{{ $row['total'] }}</td>
                        <td class="text-center">{{ $row['confirmed'] }}</td>
                        <td class="text-center">{{ $row['pending'] }}</td>
                        <td class="text-center">{{ $row['cancelled'] }}</td>
                        <td class="text-center">{{ $row['checked_in'] }}</td>
                        <td class="text-center">{{ $row['attendees'] }}</td>
                        <td class="text-end">{{ $fmt($row['revenue']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section page-break">
        <div class="section-title">{{ __('reports.sections.by_ticket', [], $locale) }}</div>
        <div class="chart-wrap"><canvas id="ticketChart"></canvas></div>
        <table>
            <thead>
                <tr>
                    <th>{{ __('reports.columns.ticket_type', [], $locale) }}</th>
                    <th class="text-center">{{ __('reports.columns.bookings', [], $locale) }}</th>
                    <th class="text-center">{{ __('reports.columns.attendees', [], $locale) }}</th>
                    <th class="text-end">{{ __('reports.columns.revenue', [], $locale) }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($byTicket as $row)
                    <tr>
                        <td>{{ $row['ticket_type'] }}</td>
                        <td class="text-center">{{ $row['bookings'] }}</td>
                        <td class="text-center">{{ $row['attendees'] }}</td>
                        <td class="text-end">{{ $fmt($row['revenue']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @endif

<script>{!! $chartJs !!}</script>
<script>
    Chart.defaults.animation = false;
    Chart.defaults.font.size = 11;

    @if ($totalBookings > 0)
    new Chart(document.getElementById('eventChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($eventLabels) !!},
            datasets: [{ label: '{{ __('reports.columns.revenue', [], $locale) }}', data: {!! json_encode($eventData) !!}, backgroundColor: '#1d4ed8' }]
        },
        options: { indexAxis: 'y', plugins: { legend: { display: false } } }
    });

    new Chart(document.getElementById('ticketChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($ticketLabels) !!},
            datasets: [{ label: '{{ __('reports.columns.revenue', [], $locale) }}', data: {!! json_encode($ticketData) !!}, backgroundColor: '#0891b2' }]
        },
        options: { indexAxis: 'y', plugins: { legend: { display: false } } }
    });
    @endif

    setTimeout(function () { window.pdfReady = true; }, 250);
</script>

</body>
</html>
