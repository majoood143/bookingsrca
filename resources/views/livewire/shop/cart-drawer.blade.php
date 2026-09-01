@php
    $locale = app()->getLocale();
    $isRtl = $locale === 'ar';
@endphp
<div x-data="{ open: @entangle('open') }" wire:key="shop-cart-drawer">
    <button type="button" @click="open = true"
        class="relative inline-flex items-center justify-center h-10 w-10 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-700 transition-colors"
        aria-label="{{ __('shop.nav.cart') }}">
        <x-heroicon-o-shopping-bag class="w-5 h-5" />
        @if ($items->count() > 0)
            <span
                class="absolute -top-1 {{ $isRtl ? '-left-1' : '-right-1' }} h-5 min-w-5 px-1 flex items-center justify-center rounded-full bg-brand text-white text-[11px] font-bold">
                {{ $items->sum('quantity') }}
            </span>
        @endif
    </button>

    {{-- Overlay --}}
    <div x-show="open" x-transition.opacity @click="open = false" class="fixed inset-0 bg-gray-900/50 z-40" style="display: none;"></div>

    {{-- Panel --}}
    <div x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="{{ $isRtl ? '-translate-x-full' : 'translate-x-full' }}"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="{{ $isRtl ? '-translate-x-full' : 'translate-x-full' }}"
        class="fixed top-0 {{ $isRtl ? 'left-0' : 'right-0' }} h-full w-full max-w-sm bg-white z-50 shadow-2xl flex flex-col"
        style="display: none;">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h2 class="font-bold text-gray-900">{{ __('shop.cart.title') }}</h2>
            <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600">
                <x-heroicon-o-x-mark class="w-5 h-5" />
            </button>
        </div>

        <div class="flex-1 overflow-y-auto px-5 py-4 space-y-4">
            @forelse ($items as $item)
                <div class="flex gap-3" wire:key="cart-item-{{ $item['product']->id }}">
                    <div class="h-16 w-16 rounded-lg bg-gray-100 overflow-hidden shrink-0">
                        @if ($item['product']->image_url)
                            <img src="{{ $item['product']->image_url }}" alt="{{ $item['product']->display_name }}" class="w-full h-full object-cover">
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate">{{ $item['product']->display_name }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">@include('partials.currency-amount', ['amount' => (float) $item['product']->price])</p>
                        <div class="flex items-center gap-2 mt-2">
                            <button type="button" wire:click="updateQuantity({{ $item['product']->id }}, {{ $item['quantity'] - 1 }})"
                                class="h-6 w-6 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 text-gray-600">−</button>
                            <span class="text-sm w-5 text-center">{{ $item['quantity'] }}</span>
                            <button type="button" wire:click="updateQuantity({{ $item['product']->id }}, {{ $item['quantity'] + 1 }})"
                                class="h-6 w-6 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 text-gray-600">+</button>
                            <button type="button" wire:click="removeItem({{ $item['product']->id }})"
                                class="ms-auto text-xs text-gray-400 hover:text-danger-600">{{ __('shop.nav.remove') }}</button>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-400 text-center py-10">{{ __('shop.nav.cart_empty') }}</p>
            @endforelse
        </div>

        @if ($items->count() > 0)
            <div class="border-t border-gray-100 px-5 py-4 space-y-3">
                <div class="flex items-center justify-between text-sm font-semibold text-gray-900">
                    <span>{{ __('shop.nav.subtotal') }}</span>
                    <span>@include('partials.currency-amount', ['amount' => $subtotal])</span>
                </div>
                <a href="{{ route('shop.checkout') }}"
                    class="block text-center w-full py-2.5 rounded-xl bg-brand text-white text-sm font-semibold hover:bg-brand-hover transition-colors">
                    {{ __('shop.nav.checkout') }}
                </a>
            </div>
        @endif
    </div>
</div>
