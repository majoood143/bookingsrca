@php
    $favicon = \App\Models\BookingSetting::get('favicon');
    $locale = app()->getLocale();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale) }}" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    @include('partials.gtm-head')
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="{{ $favicon ? asset('storage/' . $favicon) : asset('favicon.ico') }}" sizes="any">
    <title>{{ __('shop.order_success.heading') }} — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    @include('partials.gtm-body')
    <div class="w-full max-w-lg">
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-8 py-10 text-center text-white">
                <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold">{{ __('shop.order_success.heading') }}</h1>
                <p class="mt-2 text-white/90 text-sm">
                    {{ __('shop.order_success.subheading', ['number' => $order->order_number, 'email' => $order->contact_email]) }}
                </p>
            </div>

            <div class="p-6 sm:p-8 space-y-4">
                <div class="flex items-center justify-between text-sm border-b border-gray-100 pb-3">
                    <span class="text-gray-500">{{ __('pos.order.fields.order_number') }}</span>
                    <span class="font-semibold text-gray-900">{{ $order->order_number }}</span>
                </div>
                <div class="flex items-center justify-between text-sm border-b border-gray-100 pb-3">
                    <span class="text-gray-500">{{ __('shop.order_success.status') }}</span>
                    <span class="font-semibold text-gray-900">{{ __('pos.order.fulfillment_status.' . $order->fulfillment_status) }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">{{ __('pos.order.fields.grand_total') }}</span>
                    <span class="font-semibold text-gray-900">@include('partials.currency-amount', ['amount' => (float) $order->grand_total])</span>
                </div>

                <a href="{{ route('shop.index') }}"
                    class="block text-center w-full mt-4 py-3 rounded-xl bg-brand text-white font-semibold hover:bg-brand-hover transition-colors">
                    {{ __('shop.order_success.back_to_shop') }}
                </a>
            </div>
        </div>
    </div>
</body>

</html>
