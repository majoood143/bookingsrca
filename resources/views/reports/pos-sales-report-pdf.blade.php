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
        font-size: 14px; font-weight: 700; color: #065f46;
        border-bottom: 2px solid #065f46; padding-bottom: 6px; margin-bottom: 12px;
    }
    .stats-grid { display: flex; gap: 10px; margin-bottom: 18px; }
    .stat-card { flex: 1; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 12px; text-align: center; }
    .stat-value { font-size: 16px; font-weight: 700; color: #065f46; }
    .stat-label { font-size: 9px; color: #6b7280; text-transform: uppercase; margin-top: 4px; }
    table { width: 100%; border-collapse: collapse; font-size: 11px; }
    th { background: #065f46; color: #fff; padding: 7px 10px; text-align: {{ $isRtl ? 'right' : 'left' }}; font-weight: 600; }
    td { padding: 6px 10px; border-bottom: 1px solid #e5e7eb; }
    td.num, th.num { text-align: {{ $isRtl ? 'left' : 'right' }}; }
    tr:nth-child(even) td { background: #f9fafb; }
</style>
</head>
<body>
    <div class="stats-grid">
        <div class="stat-card"><div class="stat-value">{{ $fmt($totalRevenue) }}</div><div class="stat-label">{{ __('pos_reports.sales.stats.total_revenue', [], $locale) }}</div></div>
        <div class="stat-card"><div class="stat-value">{{ $totalQuantity }}</div><div class="stat-label">{{ __('pos_reports.sales.stats.total_quantity', [], $locale) }}</div></div>
        <div class="stat-card"><div class="stat-value">{{ $orderCount }}</div><div class="stat-label">{{ __('pos_reports.sales.stats.order_count', [], $locale) }}</div></div>
        <div class="stat-card"><div class="stat-value">{{ $fmt($avgOrderValue) }}</div><div class="stat-label">{{ __('pos_reports.sales.stats.avg_order_value', [], $locale) }}</div></div>
    </div>

    @foreach ([
        ['byProduct', 'pos_reports.sales.sections.by_product', 'pos_reports.sales.columns.product'],
        ['byCategory', 'pos_reports.sales.sections.by_category', 'pos_reports.sales.columns.category'],
        ['byBrand', 'pos_reports.sales.sections.by_brand', 'pos_reports.sales.columns.brand'],
        ['byChannel', 'pos_reports.sales.sections.by_channel', 'pos_reports.sales.columns.channel'],
    ] as [$key, $sectionKey, $labelKey])
        <div class="section">
            <div class="section-title">{{ __($sectionKey, [], $locale) }}</div>
            @if (($$key)->isEmpty())
                <p>{{ __('pos_reports.sales.no_data', [], $locale) }}</p>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>{{ __($labelKey, [], $locale) }}</th>
                            <th class="num">{{ __('pos_reports.sales.columns.quantity', [], $locale) }}</th>
                            <th class="num">{{ __('pos_reports.sales.columns.revenue', [], $locale) }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($$key as $row)
                            <tr>
                                <td>{{ $row['label'] }}</td>
                                <td class="num">{{ $row['quantity'] }}</td>
                                <td class="num">{{ $fmt($row['revenue']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    @endforeach

    <div class="section">
        <div class="section-title">{{ __('pos_reports.sales.sections.trend', [], $locale) }}</div>
        @if ($trend->isEmpty())
            <p>{{ __('pos_reports.sales.no_data', [], $locale) }}</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>{{ __('pos_reports.sales.columns.date', [], $locale) }}</th>
                        <th class="num">{{ __('pos_reports.sales.columns.quantity', [], $locale) }}</th>
                        <th class="num">{{ __('pos_reports.sales.columns.revenue', [], $locale) }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($trend as $row)
                        <tr>
                            <td>{{ $row['date'] }}</td>
                            <td class="num">{{ $row['quantity'] }}</td>
                            <td class="num">{{ $fmt($row['revenue']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</body>
</html>
