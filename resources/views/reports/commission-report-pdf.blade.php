@php
    $isRtl = $locale === 'ar';
    $chartJs = file_get_contents(public_path('vendor/chartjs/chart.umd.js'));

    $trendLabels = $commissionTrend->pluck('date')->values();
    $trendCommissionData = $commissionTrend->pluck('commission')->values();
    $trendRevenueData = $commissionTrend->pluck('revenue')->values();

    $eventLabels = $byEvent->pluck('label')->values();
    $eventCommissionData = $byEvent->pluck('commission')->values();

    $fmt = fn ($n) => view('partials.currency-amount', ['amount' => (float) $n])->render();
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
        color: #5b21b6;
        border-bottom: 2px solid #5b21b6;
        padding-bottom: 6px;
        margin-bottom: 14px;
    }
    .stats-grid {
        display: flex;
        gap: 10px;
        margin-bottom: 8px;
    }
    .stat-card {
        flex: 1;
        background: #f5f3ff;
        border: 1px solid #ddd6fe;
        border-radius: 8px;
        padding: 12px;
        text-align: center;
    }
    .stat-value { font-size: 17px; font-weight: 700; color: #6d28d9; }
    .stat-label { font-size: 9px; color: #6b7280; text-transform: uppercase; margin-top: 4px; }

    .chart-wrap {
        width: 100%;
        height: 230px;
        margin-bottom: 14px;
    }

    table { width: 100%; border-collapse: collapse; font-size: 11px; }
    th {
        background: #5b21b6;
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
        <div class="section-title">{{ __('commission_report.sections.summary', [], $locale) }}</div>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value">{!! $fmt($totalRevenue) !!}</div>
                <div class="stat-label">{{ __('commission_report.stats.total_revenue', [], $locale) }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{!! $fmt($totalCommission) !!}</div>
                <div class="stat-label">{{ __('commission_report.stats.total_commission', [], $locale) }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $bookingCount }}</div>
                <div class="stat-label">{{ __('commission_report.stats.booking_count', [], $locale) }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ number_format($avgRate, 2) }}%</div>
                <div class="stat-label">{{ __('commission_report.stats.avg_rate', [], $locale) }}</div>
            </div>
        </div>
    </div>

    @if ($bookingCount === 0)
        <p>{{ __('commission_report.no_data', [], $locale) }}</p>
    @else

    <div class="section">
        <div class="section-title">{{ __('commission_report.sections.trend', [], $locale) }}</div>
        <div class="chart-wrap"><canvas id="trendChart"></canvas></div>
    </div>

    <div class="section page-break">
        <div class="section-title">{{ __('commission_report.sections.by_event', [], $locale) }}</div>
        <div class="chart-wrap"><canvas id="eventChart"></canvas></div>
        <table>
            <thead>
                <tr>
                    <th>{{ __('commission_report.columns.event', [], $locale) }}</th>
                    <th class="text-center">{{ __('commission_report.columns.count', [], $locale) }}</th>
                    <th class="text-center">{{ __('commission_report.columns.rate', [], $locale) }}</th>
                    <th class="text-end">{{ __('commission_report.columns.revenue', [], $locale) }}</th>
                    <th class="text-end">{{ __('commission_report.columns.commission', [], $locale) }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($byEvent as $row)
                    <tr>
                        <td>{{ $row['label'] }}</td>
                        <td class="text-center">{{ $row['count'] }}</td>
                        <td class="text-center">{{ $row['rate'] }}</td>
                        <td class="text-end">{!! $fmt($row['revenue']) !!}</td>
                        <td class="text-end">{!! $fmt($row['commission']) !!}</td>
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

    @if ($bookingCount > 0)
    new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode($trendLabels) !!},
            datasets: [
                {
                    label: '{{ __('commission_report.columns.revenue', [], $locale) }}',
                    data: {!! json_encode($trendRevenueData) !!},
                    borderColor: '#9ca3af',
                    backgroundColor: 'rgba(156, 163, 175, 0.15)',
                    fill: true,
                    tension: 0.25,
                },
                {
                    label: '{{ __('commission_report.columns.commission', [], $locale) }}',
                    data: {!! json_encode($trendCommissionData) !!},
                    borderColor: '#6d28d9',
                    backgroundColor: 'rgba(109, 40, 217, 0.15)',
                    fill: true,
                    tension: 0.25,
                },
            ]
        },
        options: { plugins: { legend: { display: true, position: 'bottom' } } }
    });

    new Chart(document.getElementById('eventChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($eventLabels) !!},
            datasets: [{ label: '{{ __('commission_report.columns.commission', [], $locale) }}', data: {!! json_encode($eventCommissionData) !!}, backgroundColor: '#6d28d9' }]
        },
        options: { indexAxis: 'y', plugins: { legend: { display: false } } }
    });
    @endif

    setTimeout(function () { window.pdfReady = true; }, 250);
</script>

</body>
</html>
