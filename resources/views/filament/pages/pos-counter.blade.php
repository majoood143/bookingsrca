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
                        class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-lg" />
                </div>
                <x-filament::button wire:click="openShift" size="lg">
                    {{ __('pos.screen.open_shift') }}
                </x-filament::button>
            </div>
        </x-filament::section>
    @else
        {{-- ── Shift open: full sales screen ─────────────────────────────────── --}}
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2 space-y-6">
                {{-- Add ticket --}}
                <x-filament::section :heading="__('pos.screen.add_ticket')">
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
                        <div>
                            <label class="text-sm font-medium">Event</label>
                            <select wire:model.live="selectedEventId" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800">
                                <option value="">—</option>
                                @foreach ($this->activeEvents() as $event)
                                    <option value="{{ $event->id }}">{{ $event->getTranslation('title', app()->getLocale()) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-medium">{{ __('Slot') }}</label>
                            <select wire:model="selectedTimeSlotId" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800">
                                <option value="">—</option>
                                @foreach ($this->availableTimeSlots() as $slot)
                                    <option value="{{ $slot->id }}">{{ $slot->getTimeRange() }} — {{ $slot->date->format('Y-m-d') }} ({{ $slot->getRemainingCapacity() }} left)</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-medium">{{ __('Ticket Type') }}</label>
                            <select wire:model="selectedTicketTypeId" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800">
                                <option value="">—</option>
                                @foreach ($this->availableTicketTypes() as $type)
                                    <option value="{{ $type->id }}">{{ $type->getTranslation('name', app()->getLocale()) }} — {{ $currency }} {{ number_format($type->price, 3) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <input type="number" min="1" wire:model="ticketQuantity" class="w-20 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-lg text-center" />
                            <x-filament::button wire:click="addTicketToCart" class="flex-1">
                                {{ __('pos.screen.add_ticket') }}
                            </x-filament::button>
                        </div>
                    </div>
                </x-filament::section>

                {{-- Add product --}}
                <x-filament::section :heading="__('pos.screen.add_product')">
                    <input type="text" wire:model.live.debounce.300ms="productSearch" placeholder="{{ __('pos.screen.search_product') }}"
                        class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-lg mb-3" />

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach ($this->searchProducts() as $product)
                            <button type="button" wire:click="addProductToCart({{ $product->id }})"
                                class="text-left rounded-xl border border-gray-200 dark:border-gray-700 p-3 hover:border-primary-500 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors">
                                <span class="block font-semibold">{{ $product->name }}</span>
                                <span class="block text-sm text-gray-500">{{ $currency }} {{ number_format($product->price, 3) }}</span>
                                @if ($product->isLowStock())
                                    <span class="inline-block mt-1 text-xs px-2 py-0.5 rounded-full bg-danger-100 text-danger-700 dark:bg-danger-900/40 dark:text-danger-300">{{ __('Low Stock') }}</span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </x-filament::section>
            </div>

            {{-- Cart + payment --}}
            <div class="space-y-6">
                <x-filament::section :heading="__('pos.screen.cart')">
                    @php $totals = $this->cartTotals(); @endphp

                    @if (empty($ticketCart) && empty($productCart))
                        <p class="text-center text-gray-400 py-6">{{ __('pos.screen.cart_empty') }}</p>
                    @else
                        <ul class="divide-y divide-gray-100 dark:divide-gray-800 mb-4">
                            @foreach ($ticketCart as $key => $line)
                                <li class="py-2 flex items-start justify-between gap-2">
                                    <div>
                                        <p class="font-medium">{{ $line['ticket_name'] }} × {{ $line['quantity'] }}</p>
                                        <p class="text-xs text-gray-500">{{ $line['event_name'] }} — {{ $line['slot_label'] }}</p>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <p class="font-semibold">{{ $currency }} {{ number_format($line['quantity'] * $line['unit_price'], 3) }}</p>
                                        <button type="button" wire:click="removeTicketLine('{{ $key }}')" class="text-xs text-danger-600 hover:underline">{{ __('pos.screen.void_line') }}</button>
                                    </div>
                                </li>
                            @endforeach

                            @foreach ($productCart as $productId => $line)
                                <li class="py-2 flex items-start justify-between gap-2">
                                    <div>
                                        <p class="font-medium">{{ $line['name'] }} × {{ $line['quantity'] }}</p>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <p class="font-semibold">{{ $currency }} {{ number_format($line['quantity'] * $line['unit_price'], 3) }}</p>
                                        <button type="button" wire:click="removeProductLine({{ $productId }})" class="text-xs text-danger-600 hover:underline">{{ __('pos.screen.void_line') }}</button>
                                    </div>
                                </li>
                            @endforeach
                        </ul>

                        <div class="mb-3">
                            <label class="text-xs font-medium text-gray-500">{{ __('pos.discount.fields.code') }}</label>
                            <input type="text" wire:model.live.debounce.300ms="discountCode" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800" />
                        </div>

                        <dl class="space-y-1 text-sm border-t border-gray-100 dark:border-gray-800 pt-3">
                            <div class="flex justify-between"><dt>{{ __('pos.screen.subtotal') }}</dt><dd>{{ $currency }} {{ number_format($totals['subtotal'], 3) }}</dd></div>
                            @if ($totals['discountAmount'] > 0)
                                <div class="flex justify-between text-success-600"><dt>{{ __('pos.screen.discount') }}</dt><dd>-{{ $currency }} {{ number_format($totals['discountAmount'], 3) }}</dd></div>
                            @endif
                            <div class="flex justify-between"><dt>{{ __('pos.screen.tax') }}</dt><dd>{{ $currency }} {{ number_format($totals['tax'], 3) }}</dd></div>
                            <div class="flex justify-between text-lg font-bold border-t border-gray-100 dark:border-gray-800 pt-2"><dt>{{ __('pos.screen.total') }}</dt><dd>{{ $currency }} {{ number_format($totals['total'], 3) }}</dd></div>
                        </dl>

                        <div class="mt-4 space-y-3">
                            <div class="grid grid-cols-3 gap-2">
                                @foreach (['cash' => 'pos.screen.cash', 'card' => 'pos.screen.card', 'mobile_wallet' => 'pos.screen.mobile_wallet'] as $method => $label)
                                    <button type="button" wire:click="$set('paymentMethod', '{{ $method }}')"
                                        class="rounded-lg py-2 text-sm font-semibold border {{ $paymentMethod === $method ? 'bg-primary-600 text-white border-primary-600' : 'border-gray-300 dark:border-gray-600' }}">
                                        {{ __($label) }}
                                    </button>
                                @endforeach
                            </div>

                            @if ($paymentMethod === 'cash')
                                <div>
                                    <label class="text-xs font-medium text-gray-500">{{ __('pos.screen.amount_tendered') }}</label>
                                    <input type="number" step="0.001" min="0" wire:model.live="amountTendered" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-lg" />
                                    <p class="text-sm text-gray-500 mt-1">{{ __('pos.screen.change_due') }}: {{ $currency }} {{ number_format($this->changeDue(), 3) }}</p>
                                </div>
                            @endif

                            <x-filament::button wire:click="completeSale" size="lg" class="w-full">
                                {{ __('pos.screen.complete_sale') }}
                            </x-filament::button>
                        </div>
                    @endif
                </x-filament::section>

                <x-filament::section :heading="__('pos.screen.close_shift')">
                    <p class="text-xs text-gray-500 mb-2">{{ __('pos.shift.fields.opening_float') }}: {{ $currency }} {{ number_format($shift->opening_float, 3) }}</p>
                    <div class="flex items-end gap-3">
                        <div class="flex-1">
                            <label class="text-sm font-medium">{{ __('pos.screen.counted_cash') }}</label>
                            <input type="number" step="0.001" min="0" wire:model="countedCash" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800" />
                        </div>
                        <x-filament::button color="gray" wire:click="closeShift">
                            {{ __('pos.screen.close_shift') }}
                        </x-filament::button>
                    </div>
                </x-filament::section>
            </div>
        </div>
    @endif
</x-filament-panels::page>
