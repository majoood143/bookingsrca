@php
    $isRtl = $locale === 'ar';
    $currency = \App\Models\BookingSetting::get('currency_code') ?: __('pos_reports.currency', [], $locale);
    $fmt = fn ($n) => number_format((float) $n, 3) . ' ' . $currency;
@endphp
<!DOCTYPE html>
<html dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
<meta charset="UTF-8">
@include('reports.partials.pdf-fonts')
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Almarai', 'Segoe UI', Tahoma, Arial, sans-serif; font-size: 12px; color: #1f2937; }
    .section { margin-bottom: 22px; }
    .section-title {
        font-size: 14px; font-weight: 700; color: #1e3a8a;
        border-bottom: 2px solid #1e3a8a; padding-bottom: 6px; margin-bottom: 12px;
    }
    .stats-grid { display: flex; gap: 10px; margin-bottom: 18px; }
    .stat-card { flex: 1; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 12px; text-align: center; }
    .stat-value { font-size: 16px; font-weight: 700; color: #1e3a8a; }
    .stat-label { font-size: 9px; color: #6b7280; text-transform: uppercase; margin-top: 4px; }
    table { width: 100%; border-collapse: collapse; font-size: 10.5px; }
    th { background: #1e3a8a; color: #fff; padding: 6px 8px; text-align: {{ $isRtl ? 'right' : 'left' }}; font-weight: 600; }
    td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; }
    td.num, th.num { text-align: {{ $isRtl ? 'left' : 'right' }}; }
    tr:nth-child(even) td { background: #f9fafb; }
</style>
</head>
<body>
    <div class="stats-grid">
        <div class="stat-card"><div class="stat-value">{{ $fmt($totalAmount) }}</div><div class="stat-label">{{ __('pos_reports.transactions.stats.total_amount', [], $locale) }}</div></div>
        <div class="stat-card"><div class="stat-value">{{ $transactionCount }}</div><div class="stat-label">{{ __('pos_reports.transactions.stats.transaction_count', [], $locale) }}</div></div>
    </div>

    <div class="section">
        <div class="section-title">{{ __('pos_reports.transactions.sections.by_method', [], $locale) }}</div>
        <table>
            <thead><tr><th>{{ __('pos_reports.transactions.columns.method', [], $locale) }}</th><th class="num">{{ __('pos_reports.transactions.columns.count', [], $locale) }}</th><th class="num">{{ __('pos_reports.transactions.columns.amount', [], $locale) }}</th></tr></thead>
            <tbody>
                @foreach ($byMethod as $row)
                    <tr><td>{{ $row['label'] }}</td><td class="num">{{ $row['count'] }}</td><td class="num">{{ $fmt($row['amount']) }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">{{ __('pos_reports.transactions.sections.by_status', [], $locale) }}</div>
        <table>
            <thead><tr><th>{{ __('pos_reports.transactions.columns.status', [], $locale) }}</th><th class="num">{{ __('pos_reports.transactions.columns.count', [], $locale) }}</th><th class="num">{{ __('pos_reports.transactions.columns.amount', [], $locale) }}</th></tr></thead>
            <tbody>
                @foreach ($byStatus as $row)
                    <tr><td>{{ $row['label'] }}</td><td class="num">{{ $row['count'] }}</td><td class="num">{{ $fmt($row['amount']) }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">{{ __('pos_reports.transactions.sections.list', [], $locale) }}</div>
        @if ($list->isEmpty())
            <p>{{ __('pos_reports.transactions.no_data', [], $locale) }}</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>{{ __('pos_reports.transactions.columns.date', [], $locale) }}</th>
                        <th>{{ __('pos_reports.transactions.columns.method', [], $locale) }}</th>
                        <th>{{ __('pos_reports.transactions.columns.source', [], $locale) }}</th>
                        <th>{{ __('pos_reports.transactions.columns.status', [], $locale) }}</th>
                        <th>{{ __('pos_reports.transactions.columns.reference', [], $locale) }}</th>
                        <th class="num">{{ __('pos_reports.transactions.columns.amount', [], $locale) }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($list as $row)
                        <tr>
                            <td>{{ $row['date'] }}</td>
                            <td>{{ $row['method'] }}</td>
                            <td>{{ $row['source'] }}</td>
                            <td>{{ $row['status'] }}</td>
                            <td>{{ $row['reference'] ?: '—' }}</td>
                            <td class="num">{{ $fmt($row['amount']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</body>
</html>
