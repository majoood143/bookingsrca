<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', 'Courier New', monospace;
            font-size: 22px;
            color: #000;
            background: #fff;
            width: {{ $paperWidth }}px;
            padding: 20px 24px;
        }

        .center {
            text-align: center;
        }

        .title {
            font-size: 28px;
            font-weight: bold;
        }

        .muted {
            color: #333;
            font-size: 18px;
        }

        .divider {
            border-top: 2px dashed #000;
            margin: 14px 0;
        }

        .row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            padding: 4px 0;
            font-size: 20px;
        }

        .row .label {
            color: #000;
        }

        .row .value {
            font-weight: bold;
            text-align: {{ $isRtl ? 'left' : 'right' }};
        }

        .item {
            padding: 6px 0;
            border-bottom: 1px dotted #999;
            font-size: 20px;
        }

        .item:last-child {
            border-bottom: none;
        }

        .total-row {
            font-size: 24px;
            font-weight: bold;
            border-top: 2px solid #000;
            padding-top: 8px;
            margin-top: 8px;
        }

        .footer {
            margin-top: 16px;
            text-align: center;
            font-size: 16px;
        }

        @if ($isRtl)
            body {
                font-family: 'DejaVu Sans', 'Tahoma', sans-serif;
            }
        @endif
    </style>
</head>
<body>
    <div class="center">
        <div class="title">{{ config('app.name') }}</div>
        @if ($vatRegistrationNumber)
            <div class="muted">{{ __('VAT Reg.') }}: {{ $vatRegistrationNumber }}</div>
        @endif
    </div>

    <div class="divider"></div>

    <div class="row"><span class="label">{{ __('pos.transaction.fields.transaction_number') }}</span><span class="value">{{ $transaction->transaction_number }}</span></div>
    <div class="row"><span class="label">{{ __('pos.transaction.fields.created_at') }}</span><span class="value">{{ $transaction->created_at->format('Y-m-d H:i') }}</span></div>
    <div class="row"><span class="label">{{ __('pos.transaction.fields.terminal') }}</span><span class="value">{{ $transaction->terminal->name }}</span></div>
    <div class="row"><span class="label">{{ __('pos.transaction.fields.cashier') }}</span><span class="value">{{ $transaction->cashier->name }}</span></div>

    <div class="divider"></div>

    @foreach ($transaction->items as $item)
        <div class="item">
            <div class="row">
                <span class="label">{{ $item->isTicket() ? ($item->itemable?->ticketType?->getTranslation('name', $locale) ?? '') : ($item->itemable?->name ?? '') }} &times; {{ $item->quantity }}</span>
                <span class="value">@include('partials.currency-amount', ['amount' => $item->line_total])</span>
            </div>
            @if ($item->isTicket())
                <div class="muted">{{ $item->itemable?->getFullName() }} — {{ $item->itemable?->ticket_number }}</div>
            @endif
        </div>
    @endforeach

    <div class="divider"></div>

    <div class="row"><span class="label">{{ __('pos.screen.subtotal') }}</span><span class="value">@include('partials.currency-amount', ['amount' => $transaction->subtotal])</span></div>
    @if ($transaction->discount_total > 0)
        <div class="row"><span class="label">{{ __('pos.screen.discount') }}</span><span class="value">-@include('partials.currency-amount', ['amount' => $transaction->discount_total])</span></div>
    @endif
    <div class="row"><span class="label">{{ __('pos.screen.tax') }}</span><span class="value">@include('partials.currency-amount', ['amount' => $transaction->tax_total])</span></div>
    <div class="row total-row"><span class="label">{{ __('pos.screen.total') }}</span><span class="value">@include('partials.currency-amount', ['amount' => $transaction->grand_total])</span></div>

    <div class="divider"></div>

    @foreach ($transaction->payments as $payment)
        <div class="row">
            <span class="label">{{ __('pos.screen.' . $payment->method) }}</span>
            <span class="value">@include('partials.currency-amount', ['amount' => $payment->amount])</span>
        </div>
    @endforeach

    <div class="footer">
        <p>{{ __('Thank you!') }}</p>
        <p>{{ now()->format('Y-m-d H:i') }}</p>
    </div>
</body>
</html>
