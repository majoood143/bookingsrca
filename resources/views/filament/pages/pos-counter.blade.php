<x-filament-panels::page>
    @php
        $terminal = $this->currentTerminal();
        $shift = $this->currentShift();
        $currency = \App\Models\BookingSetting::currencySymbol();
    @endphp

    @if (! $terminal)
        <x-filament::section>
            <p class="text-center text-gray-500 py-8">{{ __('No active terminal is configured.') }}</p>
        </x-filament::section>
    @elseif (! $shift)
        {{-- ── Shift not open: block all sales until opened ─────────────────── --}}
        <x-filament::section :heading="__('pos.screen.open_shift')">
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ __('pos.screen.no_open_shift') }} ({{ $terminal->name }})</p>

            <div class="flex items-end gap-4">
                <div class="flex-1 max-w-xs">
                    <label class="text-sm font-medium">{{ __('pos.screen.opening_float') }}</label>
                    <input type="number" step="0.001" min="0" wire:model="openingFloat"
                        class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-lg" />
                </div>
                <x-filament::button wire:click="openShift" size="lg">
                    {{ __('pos.screen.open_shift') }}
                </x-filament::button>
            </div>
        </x-filament::section>
    @else
        {{-- ── Shift open: full sales screen ─────────────────────────────────── --}}
        <div class="flex justify-end mb-4">
            <x-filament::button color="gray" icon="heroicon-o-clock" wire:click="openTransactionsModal">
                {{ __('pos.screen.recent_transactions') }}
            </x-filament::button>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2 space-y-6">
                {{-- Add product --}}
                <x-filament::section :heading="__('pos.screen.add_product')">
                    <div class="relative mb-4">
                        <span class="pointer-events-none absolute inset-y-0 left-0 rtl:left-auto rtl:right-0 flex items-center pl-3 rtl:pl-0 rtl:pr-3 text-gray-400">
                            <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                        </span>
                        <input type="text" wire:model.live.debounce.300ms="productSearch" wire:keydown.enter="scanBarcode"
                            placeholder="{{ __('pos.screen.search_product') }}"
                            class="block w-full rounded-lg border-2 border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-lg pl-10 rtl:pl-4 rtl:pr-10 py-2.5 focus:border-primary-500 focus:ring-primary-500" />
                        <span class="pointer-events-none absolute inset-y-0 right-0 rtl:right-auto rtl:left-0 flex items-center pr-3 rtl:pr-0 rtl:pl-3 text-gray-400">
                            <x-heroicon-o-qr-code class="w-5 h-5" />
                        </span>
                    </div>

                    <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-5 gap-2.5">
                        @foreach ($this->searchProducts() as $product)
                            @php $outOfStock = $product->stock_quantity <= 0; @endphp
                            <button type="button" wire:click="addProductToCart({{ $product->id }})"
                                @disabled($outOfStock)
                                class="text-left rtl:text-right rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden transition-colors {{ $outOfStock ? 'opacity-50 cursor-not-allowed' : 'hover:border-primary-500 hover:bg-primary-50 dark:hover:bg-primary-900/20' }}">
                                <div class="h-16 sm:h-20 w-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center overflow-hidden">
                                    @if ($product->image_url)
                                        <img src="{{ $product->image_url }}" alt="{{ $product->display_name }}" class="w-full h-full object-cover" />
                                    @else
                                        <x-heroicon-o-photo class="w-6 h-6 text-gray-300 dark:text-gray-600" />
                                    @endif
                                </div>
                                <div class="p-2">
                                    <span class="block text-sm font-semibold truncate">{{ $product->display_name }}</span>
                                    <span class="block text-xs text-gray-500">{{ $currency }} {{ number_format($product->price, 3) }}</span>
                                    @if ($outOfStock)
                                        <span class="inline-block mt-1 text-xs px-1.5 py-0.5 rounded-full bg-danger-100 text-danger-700 dark:bg-danger-900/40 dark:text-danger-300">{{ __('pos.screen.out_of_stock') }}</span>
                                    @else
                                        <span class="block text-xs text-gray-400">{{ __('pos.screen.in_stock', ['count' => $product->stock_quantity]) }}</span>
                                        @if ($product->isLowStock())
                                            <span class="inline-block mt-1 text-xs px-1.5 py-0.5 rounded-full bg-danger-100 text-danger-700 dark:bg-danger-900/40 dark:text-danger-300">{{ __('Low Stock') }}</span>
                                        @endif
                                    @endif
                                </div>
                            </button>
                        @endforeach

                        @if ($this->searchProducts()->isEmpty())
                            <p class="col-span-full text-center text-gray-400 py-6">{{ __('pos.screen.no_products_found') }}</p>
                        @endif
                    </div>
                </x-filament::section>
            </div>

            {{-- Customer + cart + payment --}}
            <div class="space-y-6">
                {{-- Customer --}}
                <x-filament::section :heading="__('pos.customer.label')">
                    @php $customer = $this->selectedCustomer(); @endphp

                    <div class="flex items-center justify-between gap-2 rounded-lg border border-gray-200 dark:border-gray-700 p-3">
                        <div class="min-w-0">
                            <p class="font-medium truncate">{{ $customer?->display_name ?? __('pos.customer.walk_in') }}</p>
                            @if ($customer && !$customer->is_walk_in && $customer->mobile)
                                <p class="text-xs text-gray-500">{{ $customer->mobile }}</p>
                            @elseif (!$customer || $customer->is_walk_in)
                                <p class="text-xs text-gray-500">{{ __('pos.customer.walk_in_hint') }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            @if ($customer && !$customer->is_walk_in)
                                <button type="button" wire:click="useWalkInCustomer" class="text-xs text-gray-500 hover:underline">{{ __('pos.customer.use_walk_in') }}</button>
                            @endif
                            <x-filament::button size="xs" color="gray" icon="heroicon-o-user-plus" wire:click="openCustomerModal">
                                {{ __('pos.customer.add_new') }}
                            </x-filament::button>
                        </div>
                    </div>

                    <div class="relative mt-3">
                        <input type="text" wire:model.live.debounce.300ms="customerSearch"
                            placeholder="{{ __('pos.customer.search_placeholder') }}"
                            class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800" />

                        @if ($customerSearch)
                            <ul class="absolute z-10 mt-1 w-full max-h-48 overflow-y-auto rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-lg">
                                @forelse ($this->searchCustomers() as $result)
                                    <li>
                                        <button type="button" wire:click="selectCustomer({{ $result->id }})"
                                            class="block w-full text-left rtl:text-right px-3 py-2 hover:bg-primary-50 dark:hover:bg-primary-900/20">
                                            <span class="block font-medium">{{ $result->display_name }}</span>
                                            <span class="block text-xs text-gray-500">{{ $result->mobile }}</span>
                                        </button>
                                    </li>
                                @empty
                                    <li class="px-3 py-2 text-sm text-gray-400">{{ __('pos.customer.no_results') }}</li>
                                @endforelse
                            </ul>
                        @endif
                    </div>
                </x-filament::section>

                <x-filament::section :heading="__('pos.screen.cart')">
                    @php $totals = $this->cartTotals(); @endphp

                    @if (empty($productCart))
                        <p class="text-center text-gray-400 py-6">{{ __('pos.screen.cart_empty') }}</p>
                    @else
                        <ul class="divide-y divide-gray-100 dark:divide-gray-800 mb-4">
                            @foreach ($productCart as $productId => $line)
                                <li class="py-2 flex items-start justify-between gap-2">
                                    <div>
                                        <p class="font-medium">{{ $line['name'] }} × {{ $line['quantity'] }}</p>
                                    </div>
                                    <div class="text-right rtl:text-left shrink-0">
                                        <p class="font-semibold">{{ $currency }} {{ number_format($line['quantity'] * $line['unit_price'], 3) }}</p>
                                        <button type="button" wire:click="removeProductLine({{ $productId }})" class="text-xs text-danger-600 hover:underline">{{ __('pos.screen.void_line') }}</button>
                                    </div>
                                </li>
                            @endforeach
                        </ul>

                        <div class="mb-3">
                            <label class="text-xs font-medium text-gray-500">{{ __('pos.discount.fields.code') }}</label>
                            <input type="text" wire:model.live.debounce.300ms="discountCode" class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800" />
                        </div>

                        <dl class="space-y-1 text-sm border-t border-gray-100 dark:border-gray-800 pt-3">
                            <div class="flex justify-between"><dt>{{ __('pos.screen.subtotal') }}</dt><dd>{{ $currency }} {{ number_format($totals['subtotal'], 3) }}</dd></div>
                            @if ($totals['discountAmount'] > 0)
                                <div class="flex justify-between text-success-600"><dt>{{ __('pos.screen.discount') }}</dt><dd>-{{ $currency }} {{ number_format($totals['discountAmount'], 3) }}</dd></div>
                            @endif
                            <div class="flex justify-between"><dt>{{ __('pos.screen.tax') }}</dt><dd>{{ $currency }} {{ number_format($totals['tax'], 3) }}</dd></div>
                            @if ($totals['orderTaxAmount'] > 0)
                                <div class="flex justify-between"><dt>{{ __('pos.screen.order_tax') }}</dt><dd>{{ $currency }} {{ number_format($totals['orderTaxAmount'], 3) }}</dd></div>
                            @endif
                            @if ($totals['shippingCharges'] > 0)
                                <div class="flex justify-between"><dt>{{ __('pos.screen.shipping') }}</dt><dd>{{ $currency }} {{ number_format($totals['shippingCharges'], 3) }}</dd></div>
                            @endif
                            <div class="flex justify-between text-lg font-bold border-t border-gray-100 dark:border-gray-800 pt-2"><dt>{{ __('pos.screen.total') }}</dt><dd>{{ $currency }} {{ number_format($totals['total'], 3) }}</dd></div>
                        </dl>

                        <div class="mt-4">
                            <x-filament::button wire:click="openPaymentModal" size="lg" class="w-full" icon="heroicon-o-credit-card">
                                {{ __('pos.screen.pay') }}
                            </x-filament::button>
                        </div>
                    @endif
                </x-filament::section>

                <x-filament::section :heading="__('pos.screen.close_shift')">
                    <p class="text-xs text-gray-500 mb-2">{{ __('pos.shift.fields.opening_float') }}: {{ $currency }} {{ number_format($shift->opening_float, 3) }}</p>
                    <div class="flex items-end gap-3">
                        <div class="flex-1">
                            <label class="text-sm font-medium">{{ __('pos.screen.counted_cash') }}</label>
                            <input type="number" step="0.001" min="0" wire:model="countedCash" class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800" />
                        </div>
                        <x-filament::button color="gray" wire:click="closeShift">
                            {{ __('pos.screen.close_shift') }}
                        </x-filament::button>
                    </div>
                </x-filament::section>
            </div>
        </div>

        {{-- Add customer modal --}}
        <div x-data="{ open: @entangle('showCustomerModal') }" x-show="open" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
            <div class="absolute inset-0 bg-gray-900/60" @click="open = false"></div>

            <div class="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-xl bg-white dark:bg-gray-900 shadow-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold">{{ __('pos.customer.add_new_heading') }}</h2>
                    <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600">
                        <x-heroicon-o-x-mark class="w-6 h-6" />
                    </button>
                </div>

                <div class="flex items-center gap-6 mb-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" wire:model.live="newCustomerType" value="individual" />
                        {{ __('pos.customer.type_individual') }}
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" wire:model.live="newCustomerType" value="business" />
                        {{ __('pos.customer.type_business') }}
                    </label>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium">{{ __('pos.customer.fields.name') }} *</label>
                        <input type="text" wire:model="newCustomerName" class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800" />
                        @error('newCustomerName') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    @if ($newCustomerType === 'business')
                        <div>
                            <label class="text-sm font-medium">{{ __('pos.customer.fields.business_name') }}</label>
                            <input type="text" wire:model="newCustomerBusinessName" class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800" />
                        </div>
                    @endif

                    <div>
                        <label class="text-sm font-medium">{{ __('pos.customer.fields.mobile') }} *</label>
                        <input type="text" wire:model="newCustomerMobile" class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800" />
                        @error('newCustomerMobile') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium">{{ __('pos.customer.fields.alternate_contact') }}</label>
                        <input type="text" wire:model="newCustomerAlternateContact" class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800" />
                    </div>

                    <div>
                        <label class="text-sm font-medium">{{ __('pos.customer.fields.email') }}</label>
                        <input type="email" wire:model="newCustomerEmail" class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800" />
                        @error('newCustomerEmail') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <button type="button" wire:click="$toggle('showCustomerMoreInfo')" class="mt-4 text-sm font-medium text-primary-600 flex items-center gap-1">
                    {{ __('pos.customer.more_information') }}
                    <x-heroicon-o-chevron-down class="w-4 h-4" />
                </button>

                @if ($showCustomerMoreInfo)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4 border-t border-gray-100 dark:border-gray-800 pt-4">
                        <div class="sm:col-span-2">
                            <label class="text-sm font-medium">{{ __('pos.customer.fields.address_line_1') }}</label>
                            <input type="text" wire:model="newCustomerAddressLine1" class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800" />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-sm font-medium">{{ __('pos.customer.fields.address_line_2') }}</label>
                            <input type="text" wire:model="newCustomerAddressLine2" class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800" />
                        </div>
                        <div>
                            <label class="text-sm font-medium">{{ __('pos.customer.fields.city') }}</label>
                            <input type="text" wire:model="newCustomerCity" class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800" />
                        </div>
                        <div>
                            <label class="text-sm font-medium">{{ __('pos.customer.fields.state') }}</label>
                            <input type="text" wire:model="newCustomerState" class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800" />
                        </div>
                        <div>
                            <label class="text-sm font-medium">{{ __('pos.customer.fields.country') }}</label>
                            <input type="text" wire:model="newCustomerCountry" class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800" />
                        </div>
                        <div>
                            <label class="text-sm font-medium">{{ __('pos.customer.fields.zip_code') }}</label>
                            <input type="text" wire:model="newCustomerZipCode" class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800" />
                        </div>
                    </div>
                @endif

                <div class="flex justify-end gap-3 mt-6 border-t border-gray-100 dark:border-gray-800 pt-4">
                    <x-filament::button color="gray" @click="open = false">
                        {{ __('Cancel') }}
                    </x-filament::button>
                    <x-filament::button wire:click="saveNewCustomer">
                        {{ __('pos.customer.save') }}
                    </x-filament::button>
                </div>
            </div>
        </div>

        {{-- Payment modal --}}
        <div x-data="{ open: @entangle('showPaymentModal') }" x-show="open" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
            <div class="absolute inset-0 bg-gray-900/60" wire:click="closePaymentModal"></div>

            <div class="relative w-full max-w-4xl max-h-[90vh] overflow-y-auto rounded-xl bg-white dark:bg-gray-900 shadow-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold">{{ __('pos.screen.payment_title') }}</h2>
                    <button type="button" wire:click="closePaymentModal" class="text-gray-400 hover:text-gray-600">
                        <x-heroicon-o-x-mark class="w-6 h-6" />
                    </button>
                </div>

                <div class="flex flex-wrap gap-2 mb-4">
                    <button type="button" wire:click="openDiscountModal"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 dark:border-gray-700 px-3 py-1.5 text-sm hover:border-primary-500 hover:text-primary-600">
                        <x-heroicon-o-receipt-percent class="w-4 h-4" />
                        {{ __('pos.discount.label') }}:
                        <span class="font-semibold">
                            @if ($adHocDiscountValue > 0)
                                {{ $adHocDiscountType === 'percentage' ? number_format($adHocDiscountValue, 2) . '%' : $currency . ' ' . number_format($adHocDiscountValue, 3) }}
                            @else
                                {{ __('pos.screen.none') }}
                            @endif
                        </span>
                    </button>

                    <button type="button" wire:click="openOrderTaxModal"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 dark:border-gray-700 px-3 py-1.5 text-sm hover:border-primary-500 hover:text-primary-600">
                        <x-heroicon-o-calculator class="w-4 h-4" />
                        {{ __('pos.screen.order_tax') }}:
                        <span class="font-semibold">{{ $this->selectedOrderTax()?->name ?? __('pos.screen.none') }}</span>
                    </button>

                    <button type="button" wire:click="openShippingModal"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 dark:border-gray-700 px-3 py-1.5 text-sm hover:border-primary-500 hover:text-primary-600">
                        <x-heroicon-o-truck class="w-4 h-4" />
                        {{ __('pos.screen.shipping') }}:
                        <span class="font-semibold">
                            @if ($shippingCharges > 0 || $shippingDetails !== '')
                                {{ $currency }} {{ number_format($shippingCharges, 3) }}
                            @else
                                {{ __('pos.screen.none') }}
                            @endif
                        </span>
                    </button>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 space-y-4">
                        @foreach ($paymentRows as $index => $row)
                            <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 space-y-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="grid grid-cols-2 gap-3 flex-1">
                                        <div>
                                            <label class="text-xs font-medium text-gray-500">{{ __('pos.screen.amount') }}*</label>
                                            <input type="number" step="0.001" min="0" wire:model.live="paymentRows.{{ $index }}.amount"
                                                class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800" />
                                        </div>
                                        <div>
                                            <label class="text-xs font-medium text-gray-500">{{ __('pos.screen.payment_method') }}*</label>
                                            <select wire:model.live="paymentRows.{{ $index }}.method"
                                                class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800">
                                                @foreach ($this->paymentMethodOptions() as $method => [$label, $icon])
                                                    <option value="{{ $method }}">{{ __($label) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    @if (count($paymentRows) > 1)
                                        <button type="button" wire:click="removePaymentRow({{ $index }})"
                                            title="{{ __('pos.screen.remove_payment_row') }}" class="mt-6 text-danger-500 hover:text-danger-600">
                                            <x-heroicon-o-trash class="w-5 h-5" />
                                        </button>
                                    @endif
                                </div>

                                @if ($row['method'] === 'card')
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 border-t border-gray-100 dark:border-gray-800 pt-3">
                                        <div>
                                            <label class="text-xs font-medium text-gray-500">{{ __('pos.screen.card_number') }}</label>
                                            <input type="text" maxlength="4" inputmode="numeric" placeholder="1234"
                                                wire:model="paymentRows.{{ $index }}.card_last4"
                                                class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800" />
                                        </div>
                                        <div>
                                            <label class="text-xs font-medium text-gray-500">{{ __('pos.screen.card_holder_name') }}</label>
                                            <input type="text" wire:model="paymentRows.{{ $index }}.card_holder_name"
                                                class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800" />
                                        </div>
                                        <div>
                                            <label class="text-xs font-medium text-gray-500">{{ __('pos.screen.card_transaction_no') }}*</label>
                                            <input type="text" wire:model="paymentRows.{{ $index }}.reference"
                                                class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800" />
                                        </div>
                                        <div>
                                            <label class="text-xs font-medium text-gray-500">{{ __('pos.screen.card_type') }}</label>
                                            <select wire:model="paymentRows.{{ $index }}.card_type"
                                                class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800">
                                                <option value="credit">{{ __('pos.screen.card_type_credit') }}</option>
                                                <option value="debit">{{ __('pos.screen.card_type_debit') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                @elseif ($row['method'] !== 'cash')
                                    <div class="border-t border-gray-100 dark:border-gray-800 pt-3">
                                        <label class="text-xs font-medium text-gray-500">{{ __('pos.screen.reference') }}</label>
                                        <input type="text" wire:model="paymentRows.{{ $index }}.reference"
                                            class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800" />
                                    </div>
                                @endif
                            </div>
                        @endforeach

                        <button type="button" wire:click="addPaymentRow"
                            class="w-full rounded-lg bg-primary-600 hover:bg-primary-700 text-white font-semibold py-2.5 transition-colors">
                            {{ __('pos.screen.add_payment_row') }}
                        </button>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-medium text-gray-500">{{ __('pos.screen.sell_note') }}</label>
                                <textarea wire:model="sellNote" rows="3"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800"></textarea>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500">{{ __('pos.screen.staff_note') }}</label>
                                <textarea wire:model="staffNote" rows="3"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg bg-primary-600 text-white p-5 space-y-4 h-fit">
                        <div>
                            <p class="text-xs uppercase tracking-wide opacity-80">{{ __('pos.screen.total_items') }}</p>
                            <p class="text-2xl font-bold">{{ number_format($this->cartItemCount(), 3) }}</p>
                        </div>
                        <div class="border-t border-white/20 pt-3">
                            <p class="text-xs uppercase tracking-wide opacity-80">{{ __('pos.screen.total_payable') }}</p>
                            <p class="text-2xl font-bold">{{ $currency }} {{ number_format($this->cartTotals()['total'], 3) }}</p>
                        </div>
                        <div class="border-t border-white/20 pt-3">
                            <p class="text-xs uppercase tracking-wide opacity-80">{{ __('pos.screen.total_paying') }}</p>
                            <p class="text-2xl font-bold">{{ $currency }} {{ number_format($this->paymentRowsTotal(), 3) }}</p>
                        </div>
                        <div class="border-t border-white/20 pt-3">
                            <p class="text-xs uppercase tracking-wide opacity-80">{{ __('pos.screen.change_return') }}</p>
                            <p class="text-2xl font-bold">{{ $currency }} {{ number_format($this->paymentChangeReturn(), 3) }}</p>
                        </div>
                        <div class="border-t border-white/20 pt-3">
                            <p class="text-xs uppercase tracking-wide opacity-80">{{ __('pos.screen.balance') }}</p>
                            <p class="text-2xl font-bold">{{ $currency }} {{ number_format($this->paymentBalance(), 3) }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6 border-t border-gray-100 dark:border-gray-800 pt-4">
                    <x-filament::button color="gray" wire:click="closePaymentModal">
                        {{ __('pos.screen.close') }}
                    </x-filament::button>
                    <x-filament::button wire:click="finalizePayment">
                        {{ __('pos.screen.finalize_payment') }}
                    </x-filament::button>
                </div>
            </div>
        </div>

        {{-- Discount sub-modal (opened from within the payment modal) --}}
        @if ($showDiscountModal)
            <div class="fixed inset-0 z-[70] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-gray-900/60" wire:click="cancelDiscountModal"></div>
                <div class="relative w-full max-w-md rounded-xl bg-white dark:bg-gray-900 shadow-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold">{{ __('pos.screen.discount_modal.title') }}</h2>
                        <button type="button" wire:click="cancelDiscountModal" class="text-gray-400 hover:text-gray-600">
                            <x-heroicon-o-x-mark class="w-6 h-6" />
                        </button>
                    </div>

                    <p class="text-sm font-medium mb-3">{{ __('pos.screen.discount_modal.heading') }}</p>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-medium text-gray-500">{{ __('pos.screen.discount_modal.type') }}*</label>
                            <select wire:model="adHocDiscountType"
                                class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800">
                                <option value="percentage">{{ __('pos.screen.discount_modal.percentage') }}</option>
                                <option value="fixed_amount">{{ __('pos.screen.discount_modal.fixed_amount') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-500">{{ __('pos.screen.discount_modal.amount') }}*</label>
                            <input type="number" step="0.001" min="0" wire:model="adHocDiscountValue"
                                class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800" />
                            @error('adHocDiscountValue') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-6 border-t border-gray-100 dark:border-gray-800 pt-4">
                        <x-filament::button wire:click="saveDiscountModal">{{ __('pos.screen.update') }}</x-filament::button>
                        <x-filament::button color="gray" wire:click="cancelDiscountModal">{{ __('pos.screen.cancel') }}</x-filament::button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Order tax sub-modal (opened from within the payment modal) --}}
        @if ($showOrderTaxModal)
            <div class="fixed inset-0 z-[70] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-gray-900/60" wire:click="cancelOrderTaxModal"></div>
                <div class="relative w-full max-w-md rounded-xl bg-white dark:bg-gray-900 shadow-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold">{{ __('pos.screen.tax_modal.title') }}</h2>
                        <button type="button" wire:click="cancelOrderTaxModal" class="text-gray-400 hover:text-gray-600">
                            <x-heroicon-o-x-mark class="w-6 h-6" />
                        </button>
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-500">{{ __('pos.screen.tax_modal.order_tax') }}*</label>
                        <select wire:model="orderTaxId"
                            class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800">
                            <option value="">{{ __('pos.screen.none') }}</option>
                            @foreach ($this->orderTaxOptions() as $tax)
                                <option value="{{ $tax->id }}">{{ $tax->name }} ({{ number_format($tax->rate, 2) }}%)</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex justify-end gap-3 mt-6 border-t border-gray-100 dark:border-gray-800 pt-4">
                        <x-filament::button wire:click="saveOrderTax">{{ __('pos.screen.update') }}</x-filament::button>
                        <x-filament::button color="gray" wire:click="cancelOrderTaxModal">{{ __('pos.screen.cancel') }}</x-filament::button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Shipping sub-modal (opened from within the payment modal) --}}
        @if ($showShippingModal)
            <div class="fixed inset-0 z-[70] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-gray-900/60" wire:click="cancelShippingModal"></div>
                <div class="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-xl bg-white dark:bg-gray-900 shadow-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold">{{ __('pos.screen.shipping_modal.title') }}</h2>
                        <button type="button" wire:click="cancelShippingModal" class="text-gray-400 hover:text-gray-600">
                            <x-heroicon-o-x-mark class="w-6 h-6" />
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-medium text-gray-500">{{ __('pos.screen.shipping_modal.details') }}*</label>
                            <textarea wire:model="shippingDetails" rows="3"
                                class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800"></textarea>
                            @error('shippingDetails') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-500">{{ __('pos.screen.shipping_modal.address') }}</label>
                            <textarea wire:model="shippingAddress" rows="3"
                                class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800"></textarea>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-500">{{ __('pos.screen.shipping_modal.charges') }}*</label>
                            <input type="number" step="0.001" min="0" wire:model="shippingCharges"
                                class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800" />
                            @error('shippingCharges') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-500">{{ __('pos.screen.shipping_modal.status') }}</label>
                            <select wire:model="shippingStatus"
                                class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800">
                                <option value="">{{ __('pos.screen.please_select') }}</option>
                                @foreach ($this::SHIPPING_STATUSES as $status)
                                    <option value="{{ $status }}">{{ __('pos.screen.shipping_modal.statuses.' . $status) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-500">{{ __('pos.screen.shipping_modal.delivered_to') }}</label>
                            <input type="text" wire:model="deliveredTo"
                                class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800" />
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-500">{{ __('pos.screen.shipping_modal.delivery_person') }}</label>
                            <select wire:model="deliveryPersonId"
                                class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800">
                                <option value="">{{ __('pos.screen.please_select') }}</option>
                                @foreach ($this->deliveryPersonOptions() as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-6 border-t border-gray-100 dark:border-gray-800 pt-4">
                        <x-filament::button wire:click="saveShippingModal">{{ __('pos.screen.update') }}</x-filament::button>
                        <x-filament::button color="gray" wire:click="cancelShippingModal">{{ __('pos.screen.cancel') }}</x-filament::button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Recent transactions modal --}}
        <div x-data="{ open: @entangle('showTransactionsModal') }" x-show="open" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
            <div class="absolute inset-0 bg-gray-900/60" wire:click="$set('showTransactionsModal', false)"></div>

            <div class="relative w-full max-w-4xl max-h-[90vh] overflow-y-auto rounded-xl bg-white dark:bg-gray-900 shadow-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold">{{ __('pos.screen.recent_transactions') }}</h2>
                    <button type="button" wire:click="$set('showTransactionsModal', false)" class="text-gray-400 hover:text-gray-600">
                        <x-heroicon-o-x-mark class="w-6 h-6" />
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left rtl:text-right text-xs text-gray-500 border-b border-gray-100 dark:border-gray-800">
                                <th class="py-2 pr-3 rtl:pr-0 rtl:pl-3">{{ __('pos.transaction.fields.transaction_number') }}</th>
                                <th class="py-2 pr-3 rtl:pr-0 rtl:pl-3">{{ __('pos.customer.label') }}</th>
                                <th class="py-2 pr-3 rtl:pr-0 rtl:pl-3">{{ __('pos.transaction.fields.grand_total') }}</th>
                                <th class="py-2 pr-3 rtl:pr-0 rtl:pl-3">{{ __('pos.transaction.fields.status') }}</th>
                                <th class="py-2 pr-3 rtl:pr-0 rtl:pl-3">{{ __('pos.transaction.fields.created_at') }}</th>
                                <th class="py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($this->recentTransactions() as $transaction)
                                <tr>
                                    <td class="py-2 pr-3 rtl:pr-0 rtl:pl-3 font-medium">{{ $transaction->transaction_number }}</td>
                                    <td class="py-2 pr-3 rtl:pr-0 rtl:pl-3">{{ $transaction->customer?->display_name ?? __('pos.customer.walk_in') }}</td>
                                    <td class="py-2 pr-3 rtl:pr-0 rtl:pl-3">{{ $currency }} {{ number_format($transaction->grand_total, 3) }}</td>
                                    <td class="py-2 pr-3 rtl:pr-0 rtl:pl-3">
                                        <span @class([
                                            'inline-block px-2 py-0.5 rounded-full text-xs font-medium',
                                            'bg-success-100 text-success-700 dark:bg-success-900/40 dark:text-success-300' => $transaction->status === 'completed',
                                            'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' => $transaction->status === 'voided',
                                            'bg-danger-100 text-danger-700 dark:bg-danger-900/40 dark:text-danger-300' => in_array($transaction->status, ['refunded', 'partially_refunded']),
                                        ])>{{ __('pos.transaction.status.' . $transaction->status) }}</span>
                                    </td>
                                    <td class="py-2 pr-3 rtl:pr-0 rtl:pl-3 text-gray-500">{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                                    <td class="py-2">
                                        <div class="flex items-center gap-2 justify-end rtl:justify-start">
                                            <button type="button" wire:click="openEditTransaction({{ $transaction->id }})"
                                                title="{{ __('pos.transaction.actions.edit') }}" class="text-gray-400 hover:text-primary-600">
                                                <x-heroicon-o-pencil-square class="w-5 h-5" />
                                            </button>
                                            <button type="button" wire:click="reprintTransaction({{ $transaction->id }})"
                                                title="{{ __('pos.transaction.actions.reprint') }}" class="text-gray-400 hover:text-primary-600">
                                                <x-heroicon-o-printer class="w-5 h-5" />
                                            </button>
                                            @if ($this->canVoidTransaction($transaction))
                                                <button type="button" wire:click="confirmVoidTransaction({{ $transaction->id }})"
                                                    title="{{ __('pos.transaction.actions.delete') }}" class="text-gray-400 hover:text-danger-600">
                                                    <x-heroicon-o-trash class="w-5 h-5" />
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-6 text-center text-gray-400">{{ __('pos.screen.no_transactions') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($this->editingTransaction())
                    @php $editingTxn = $this->editingTransaction(); @endphp
                    <div class="mt-6 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                        <h3 class="font-semibold mb-3">{{ __('pos.screen.edit_transaction') }} — {{ $editingTxn->transaction_number }}</h3>

                        <div class="mb-4">
                            <label class="text-xs font-medium text-gray-500">{{ __('pos.customer.label') }}</label>
                            <div class="flex items-center justify-between gap-2 rounded-lg border border-gray-200 dark:border-gray-700 p-2 mt-1">
                                <span class="text-sm">{{ $editCustomerId ? \App\Models\PosCustomer::find($editCustomerId)?->display_name : __('pos.customer.walk_in') }}</span>
                                @if ($editCustomerId)
                                    <button type="button" wire:click="$set('editCustomerId', null)" class="text-xs text-gray-500 hover:underline">{{ __('pos.customer.use_walk_in') }}</button>
                                @endif
                            </div>
                            <div class="relative mt-2">
                                <input type="text" wire:model.live.debounce.300ms="editCustomerSearch"
                                    placeholder="{{ __('pos.customer.search_placeholder') }}"
                                    class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800" />
                                @if ($editCustomerSearch)
                                    <ul class="absolute z-10 mt-1 w-full max-h-40 overflow-y-auto rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-lg">
                                        @forelse ($this->searchEditCustomers() as $result)
                                            <li>
                                                <button type="button" wire:click="selectEditCustomer({{ $result->id }})"
                                                    class="block w-full text-left rtl:text-right px-3 py-2 hover:bg-primary-50 dark:hover:bg-primary-900/20">
                                                    <span class="block font-medium">{{ $result->display_name }}</span>
                                                    <span class="block text-xs text-gray-500">{{ $result->mobile }}</span>
                                                </button>
                                            </li>
                                        @empty
                                            <li class="px-3 py-2 text-sm text-gray-400">{{ __('pos.customer.no_results') }}</li>
                                        @endforelse
                                    </ul>
                                @endif
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-medium text-gray-500">{{ __('pos.screen.sell_note') }}</label>
                                <textarea wire:model="editSellNote" rows="3"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800"></textarea>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500">{{ __('pos.screen.staff_note') }}</label>
                                <textarea wire:model="editStaffNote" rows="3"
                                    class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800"></textarea>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 mt-4">
                            <x-filament::button color="gray" wire:click="cancelEditTransaction">{{ __('pos.screen.cancel') }}</x-filament::button>
                            <x-filament::button wire:click="saveTransactionEdit">{{ __('pos.screen.save') }}</x-filament::button>
                        </div>
                    </div>
                @endif

                <div class="flex justify-end mt-6 border-t border-gray-100 dark:border-gray-800 pt-4">
                    <x-filament::button color="gray" wire:click="$set('showTransactionsModal', false)">
                        {{ __('pos.screen.close') }}
                    </x-filament::button>
                </div>
            </div>
        </div>

        {{-- Void ("delete") confirmation --}}
        @if ($confirmVoidTransactionId)
            <div class="fixed inset-0 z-[60] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-gray-900/60" wire:click="cancelVoidTransaction"></div>
                <div class="relative w-full max-w-md rounded-xl bg-white dark:bg-gray-900 shadow-xl p-6">
                    <h3 class="text-lg font-semibold mb-2">{{ __('pos.screen.confirm_void_title') }}</h3>
                    <p class="text-sm text-gray-500 mb-6">{{ __('pos.screen.confirm_void_body') }}</p>
                    <div class="flex justify-end gap-3">
                        <x-filament::button color="gray" wire:click="cancelVoidTransaction">{{ __('pos.screen.cancel') }}</x-filament::button>
                        <x-filament::button color="danger" wire:click="voidRecentTransaction">{{ __('pos.transaction.actions.delete') }}</x-filament::button>
                    </div>
                </div>
            </div>
        @endif
    @endif
</x-filament-panels::page>
