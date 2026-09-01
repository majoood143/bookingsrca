@php
    $isRtl = $locale === 'ar';
    $currency = \App\Models\BookingSetting::get('currency_code') ?: 'OMR';
    $fmt = fn ($n) => number_format((float) $n, 3) . ' ' . $currency;
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
        padding: 24px;
    }
    .title { font-size: 18px; font-weight: 700; color: #111827; margin-bottom: 4px; }
    .subtitle { font-size: 11px; color: #6b7280; margin-bottom: 20px; }
    .meta-grid { display: flex; justify-content: space-between; margin-bottom: 20px; }
    .meta-block { font-size: 11px; line-height: 1.6; }
    .meta-block strong { display: block; font-size: 9px; text-transform: uppercase; color: #9ca3af; margin-bottom: 3px; }
    table { width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 16px; }
    th {
        background: #111827;
        color: #ffffff;
        padding: 7px 10px;
        text-align: {{ $isRtl ? 'right' : 'left' }};
        font-weight: 600;
    }
    td { padding: 6px 10px; border-bottom: 1px solid #e5e7eb; }
    td.num, th.num { text-align: {{ $isRtl ? 'left' : 'right' }}; }
    .totals { width: 260px; margin-{{ $isRtl ? 'right' : 'left' }}: auto; font-size: 11px; }
    .totals .row { display: flex; justify-content: space-between; padding: 4px 0; }
    .totals .grand { font-weight: 700; font-size: 13px; border-top: 2px solid #111827; margin-top: 4px; padding-top: 8px; }
</style>
</head>
<body>
    <div class="title">{{ $documentTitle ?? __('pos.order.receipt.document_title', [], $locale) }}</div>
    <div class="subtitle">{{ $order->order_number }} &middot; {{ $order->paid_at?->format('Y-m-d H:i') }}</div>

    <div class="meta-grid">
        <div class="meta-block">
            <strong>{{ __('pos.order.receipt.billed_to', [], $locale) }}</strong>
            {{ $order->contact_name }}<br>
            {{ $order->contact_email }}<br>
            {{ $order->contact_mobile }}
        </div>
        <div class="meta-block">
            <strong>{{ __('pos.order.fields.payment_method', [], $locale) }}</strong>
            {{ $order->payment_method }}<br>
            <strong style="margin-top:6px">{{ __('pos.order.fields.payment_reference', [], $locale) }}</strong>
            {{ $order->payment_reference }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>{{ __('pos.order.receipt.item', [], $locale) }}</th>
                <th class="num">{{ __('pos.order.receipt.qty', [], $locale) }}</th>
                <th class="num">{{ __('pos.order.receipt.unit_price', [], $locale) }}</th>
                <th class="num">{{ __('pos.order.receipt.line_total', [], $locale) }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td class="num">{{ $item->quantity }}</td>
                    <td class="num">{{ $fmt($item->unit_price) }}</td>
                    <td class="num">{{ $fmt($item->line_total) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div class="row"><span>{{ __('pos.order.receipt.subtotal', [], $locale) }}</span><span>{{ $fmt($order->subtotal) }}</span></div>
        @if ($order->discount_total > 0)
            <div class="row"><span>{{ __('pos.order.receipt.discount', [], $locale) }}</span><span>-{{ $fmt($order->discount_total) }}</span></div>
        @endif
        <div class="row"><span>{{ __('pos.order.receipt.tax', [], $locale) }}</span><span>{{ $fmt($order->tax_total) }}</span></div>
        <div class="row grand"><span>{{ __('pos.order.receipt.grand_total', [], $locale) }}</span><span>{{ $fmt($order->grand_total) }}</span></div>
    </div>
</body>
</html>
