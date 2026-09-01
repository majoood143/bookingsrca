@php
    use App\Models\BookingSetting;

    $locale = app()->getLocale();
    $isRtl = $locale === 'ar';
    $primaryColor = BookingSetting::get('primary_color', '#05602b');
    $secondaryColor = BookingSetting::get('secondary_color', '#0da74c');

    $gatewayLabels = [
        'thawani' => 'Thawani',
        'nbo' => 'NBO / CyberSource',
        'ccavenue' => 'CCAvenue',
    ];
@endphp
<div dir="{{ $isRtl ? 'rtl' : 'ltr' }}" style="--color-brand: {{ $primaryColor }}; --color-brand-hover: {{ $secondaryColor }};">
    @include('shop.partials.header')

    <main class="max-w-4xl mx-auto px-4 sm:px-6 py-10">
        <h1 class="text-2xl font-bold text-gray-900 mb-8">{{ __('shop.checkout.heading') }}</h1>

        @if (session('error'))
            <div class="mb-6 rounded-xl bg-danger-50 border border-danger-200 text-danger-700 text-sm px-4 py-3">
                {{ session('error') }}
            </div>
        @endif

        @if ($items->isEmpty())
            <div class="text-center py-16">
                <p class="text-gray-500">{{ __('shop.checkout.cart_empty') }}</p>
                <a href="{{ route('shop.index') }}" class="inline-block mt-4 px-5 py-2.5 rounded-xl bg-brand text-white text-sm font-semibold hover:bg-brand-hover">
                    {{ __('shop.cart.continue_shopping') }}
                </a>
            </div>
        @else
            <form wire:submit="placeOrder" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-6">
                    <section class="bg-white rounded-2xl border border-gray-100 p-6">
                        <h2 class="font-semibold text-gray-900 mb-4">{{ __('shop.checkout.contact_details') }}</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <label class="text-xs font-semibold text-gray-500">{{ __('shop.checkout.name') }}</label>
                                <input type="text" wire:model="contactName" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                                @error('contactName') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-500">{{ __('shop.checkout.email') }}</label>
                                <input type="email" wire:model="contactEmail" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                                @error('contactEmail') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-500">{{ __('shop.checkout.mobile') }}</label>
                                <input type="tel" wire:model="contactMobile" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                                @error('contactMobile') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </section>

                    <section class="bg-white rounded-2xl border border-gray-100 p-6">
                        <h2 class="font-semibold text-gray-900 mb-4">{{ __('shop.checkout.address') }}</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <label class="text-xs font-semibold text-gray-500">{{ __('shop.checkout.address_line_1') }}</label>
                                <input type="text" wire:model="addressLine1" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="text-xs font-semibold text-gray-500">{{ __('shop.checkout.address_line_2') }}</label>
                                <input type="text" wire:model="addressLine2" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-500">{{ __('shop.checkout.city') }}</label>
                                <input type="text" wire:model="city" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-500">{{ __('shop.checkout.state') }}</label>
                                <input type="text" wire:model="state" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-500">{{ __('shop.checkout.country') }}</label>
                                <input type="text" wire:model="country" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-500">{{ __('shop.checkout.zip_code') }}</label>
                                <input type="text" wire:model="zipCode" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                            </div>
                        </div>
                    </section>

                    <section class="bg-white rounded-2xl border border-gray-100 p-6">
                        <label class="text-xs font-semibold text-gray-500">{{ __('shop.checkout.note') }}</label>
                        <textarea wire:model="customerNote" rows="2" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm"></textarea>
                    </section>

                    @if (count($enabledGateways) > 1)
                        <section class="bg-white rounded-2xl border border-gray-100 p-6">
                            <h2 class="font-semibold text-gray-900 mb-4">{{ __('shop.checkout.payment_method') }}</h2>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                @foreach ($enabledGateways as $gateway)
                                    <button type="button" wire:click="selectGateway('{{ $gateway }}')"
                                        class="rounded-xl border-2 px-4 py-3 text-sm font-semibold text-start transition-colors {{ $selectedPaymentMethod === $gateway ? 'border-brand text-brand bg-brand/5' : 'border-gray-200 text-gray-600' }}">
                                        {{ $gatewayLabels[$gateway] ?? ucfirst($gateway) }}
                                    </button>
                                @endforeach
                            </div>
                        </section>
                    @endif
                </div>

                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl border border-gray-100 p-6 sticky top-24">
                        <h2 class="font-semibold text-gray-900 mb-4">{{ __('shop.checkout.order_summary') }}</h2>

                        <div class="space-y-3 max-h-64 overflow-y-auto">
                            @foreach ($items as $item)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 truncate pe-2">{{ $item['product']->display_name }} × {{ $item['quantity'] }}</span>
                                    <span class="text-gray-900 font-medium shrink-0">@include('partials.currency-amount', ['amount' => (float) $item['product']->price * $item['quantity']])</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="border-t border-gray-100 mt-4 pt-4 space-y-2 text-sm">
                            <div class="flex justify-between text-gray-500">
                                <span>{{ __('shop.checkout.subtotal') }}</span>
                                <span>@include('partials.currency-amount', ['amount' => $subtotal])</span>
                            </div>
                            <div class="flex justify-between text-gray-500">
                                <span>{{ __('shop.checkout.tax') }}</span>
                                <span>@include('partials.currency-amount', ['amount' => $taxTotal])</span>
                            </div>
                            <div class="flex justify-between font-bold text-gray-900 text-base pt-2 border-t border-gray-100">
                                <span>{{ __('shop.checkout.total') }}</span>
                                <span>@include('partials.currency-amount', ['amount' => $grandTotal])</span>
                            </div>
                        </div>

                        <button type="submit"
                            class="mt-6 w-full py-3 rounded-xl bg-brand text-white font-semibold hover:bg-brand-hover transition-colors disabled:opacity-60"
                            wire:loading.attr="disabled" wire:target="placeOrder">
                            <span wire:loading.remove wire:target="placeOrder">{{ __('shop.checkout.place_order') }}</span>
                            <span wire:loading wire:target="placeOrder">…</span>
                        </button>
                    </div>
                </div>
            </form>
        @endif
    </main>

    @include('shop.partials.footer')
</div>
