@php
    $isRtl = $locale === 'ar';
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
    .meta-grid { display: flex; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 16px; }
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
    .signatures { display: flex; justify-content: space-between; margin-top: 60px; }
    .signature-block { width: 45%; font-size: 11px; }
    .signature-line { border-top: 1px solid #9ca3af; margin-top: 40px; padding-top: 6px; color: #6b7280; }
</style>
</head>
<body>
    <div class="title">{{ $documentTitle }}</div>
    <div class="subtitle">{{ $number }} &middot; {{ $date?->format('Y-m-d H:i') }}</div>

    <div class="meta-grid">
        <div class="meta-block">
            <strong>{{ __('pos.delivery_note.ship_to', [], $locale) }}</strong>
            {{ $customerName }}<br>
            @if ($customerPhone)
                {{ $customerPhone }}<br>
            @endif
            {{ $address }}
        </div>
        @if ($deliveryPerson || $status)
            <div class="meta-block">
                @if ($deliveryPerson)
                    <strong>{{ __('pos.screen.shipping_modal.delivery_person', [], $locale) }}</strong>
                    {{ $deliveryPerson }}<br>
                @endif
                @if ($status)
                    <strong style="margin-top:6px">{{ __('pos.screen.shipping_modal.status', [], $locale) }}</strong>
                    {{ $status }}
                @endif
            </div>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>{{ __('pos.order.receipt.item', [], $locale) }}</th>
                <th class="num">{{ __('pos.order.receipt.qty', [], $locale) }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $item)
                <tr>
                    <td>{{ $item['name'] }}</td>
                    <td class="num">{{ $item['qty'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="signatures">
        <div class="signature-block">
            <div class="signature-line">{{ __('pos.delivery_note.received_by', [], $locale) }}</div>
        </div>
        <div class="signature-block">
            <div class="signature-line">{{ __('pos.delivery_note.signature_date', [], $locale) }}</div>
        </div>
    </div>
</body>
</html>
