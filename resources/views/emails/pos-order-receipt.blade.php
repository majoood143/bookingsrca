<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('pos.order.receipt.subject', ['number' => $order->order_number], $locale) }}</title>
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
            background: linear-gradient(135deg, #111827 0%, #374151 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 { margin: 0 0 8px; font-size: 24px; }
        .header p { margin: 0; opacity: 0.9; }
        .content { padding: 30px 20px; }
        .order-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .detail-row:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #888; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ __('pos.order.receipt.email_heading', [], $locale) }}</h1>
            <p>{{ __('pos.order.receipt.email_subheading', ['name' => $order->contact_name], $locale) }}</p>
        </div>
        <div class="content">
            <p>{{ __('pos.order.receipt.email_intro', [], $locale) }}</p>
            <div class="order-details">
                <div class="detail-row">
                    <span>{{ __('pos.order.fields.order_number', [], $locale) }}</span>
                    <strong>{{ $order->order_number }}</strong>
                </div>
                <div class="detail-row">
                    <span>{{ __('pos.order.fields.grand_total', [], $locale) }}</span>
                    <strong>{{ number_format($order->grand_total, 3) }}</strong>
                </div>
                <div class="detail-row">
                    <span>{{ __('pos.order.fields.payment_method', [], $locale) }}</span>
                    <strong>{{ $order->payment_method }}</strong>
                </div>
            </div>
            <p>{{ __('pos.order.receipt.email_attachment_note', [], $locale) }}</p>
        </div>
        <div class="footer">{{ config('app.name') }}</div>
    </div>
</body>
</html>
