        {{-- Error Flash --}}
        @if (session()->has('error'))
            <div class="flex items-start gap-3 p-4 mb-6 text-red-700 border border-red-200 bg-red-50 rounded-xl">
                <svg class="w-5 h-5 mt-0.5 shrink-0 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                        clip-rule="evenodd" />
                </svg>
                <p>{{ session('error') }}</p>
            </div>
        @endif

        {{-- ════════════════════════════
             STEP 1 — Select Date
        ════════════════════════════ --}}
        @if ($step === 1)
            <div wire:loading.class="opacity-50 pointer-events-none">
                <h2 class="mb-1 text-2xl font-bold text-gray-900">{{ __('event_booking.step1.heading') }}</h2>
                <p class="mb-6 text-gray-500">{{ __('event_booking.step1.subheading') }}</p>

                @if ($availableDates->isEmpty())
                    <div class="py-16 text-center bg-white border border-gray-200 rounded-2xl">
                        <div class="mb-4 text-5xl">📅</div>
                        <h3 class="text-lg font-semibold text-gray-700">{{ __('event_booking.step1.no_dates') }}</h3>
                        <p class="mt-1 text-gray-400">{{ __('event_booking.step1.no_dates_body') }}</p>
                    </div>
                @else
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                        @foreach ($availableDates as $date)
                            @php
                                $d = \Carbon\Carbon::parse($date)->locale(app()->getLocale());
                                $isSoldOut = $soldOutDates->contains($date);
                            @endphp
                            <button wire:key="date-{{ $date }}"
                                wire:click="{{ $isSoldOut ? '' : "selectDate('{$date}')" }}"
                                {{ $isSoldOut ? 'disabled' : '' }}
                                class="group relative p-4 border-2 rounded-2xl text-center transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-brand-hover overflow-hidden touch-manipulation
                                    {{ $isSoldOut
                                        ? 'border-gray-100 bg-gray-50 cursor-not-allowed'
                                        : ($selectedDate === $date
                                            ? 'border-green-600 bg-white shadow-md shadow-green-600'
                                            : 'border-gray-200 bg-white hover:border-green-300 hover:shadow-sm') }}">
                                <div
                                    class="text-xs font-bold uppercase tracking-widest
                                    {{ $isSoldOut ? 'text-gray-300' : ($selectedDate === $date ? 'text-gray-400' : 'text-gray-400') }}">
                                    {{ $d->translatedFormat('l') }}
                                </div>
                                <div
                                    class="text-4xl font-black my-1
                                    {{ $isSoldOut ? 'text-gray-300' : ($selectedDate === $date ? 'text-gray-800' : 'text-gray-800') }}">
                                    {{ $d->format('d') }}
                                </div>
                                <div
                                    class="text-sm font-semibold
                                    {{ $isSoldOut ? 'text-gray-300' : ($selectedDate === $date ? 'text-gray-500' : 'text-gray-500') }}">
                                    {{ $d->translatedFormat('M Y') }}
                                </div>
                                @if ($selectedDate === $date && !$isSoldOut)
                                    <div class="mt-2">
                                        <span class="inline-block w-2 h-2 rounded-full bg-brand-hover"></span>
                                    </div>
                                @endif
                                @if ($isSoldOut)
                                    <div
                                        class="absolute inset-0 flex items-center justify-center pointer-events-none bg-gray-50/70">
                                        <span
                                            class="-rotate-12 text-[10px] sm:text-xs font-black uppercase tracking-wider text-red-500/90 border-2 border-red-400/80 rounded-md px-2 py-0.5 bg-white/80">
                                            {{ __('event_booking.step1.sold_out') }}
                                        </span>
                                    </div>
                                @endif
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div wire:loading wire:target="selectDate"
                class="fixed inset-0 z-50 flex items-center justify-center bg-white/60">
                <svg class="w-8 h-8 animate-spin text-brand-hover" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
            </div>
        @endif

        {{-- ════════════════════════════
             STEP 2 — Choose Time Slot
        ════════════════════════════ --}}
        @if ($step === 2)
            <div>
                <h2 class="mb-1 text-2xl font-bold text-gray-900">{{ __('event_booking.step2.heading') }}</h2>
                <p class="mb-6 text-gray-500">
                    {{ __('event_booking.step2.subheading') }}
                    <strong
                        class="text-gray-700">{{ \Carbon\Carbon::parse($selectedDate)->locale(app()->getLocale())->translatedFormat('l, F j, Y') }}</strong>
                </p>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" wire:loading.class="opacity-50 pointer-events-none">
                    @php $capacityOk = $enforceCapacity ?? true; @endphp
                    @foreach ($timeSlots as $slot)
                        {{-- Kiosk doesn't enforce capacity (walk-ins are never turned away —
                             see KioskBooking::enforcesCapacity()), so remaining-spot counts,
                             the fill bar, and the Full/Almost Full badges below would just be
                             noise there: every slot is always clickable regardless of what
                             they'd say. Only the public web flow, which does enforce capacity,
                             renders any of it. --}}
                        @php
                            $clickable = $capacityOk ? $slot->isAvailable() : true;
                            $showCapacity = $capacityOk && $slot->showsCapacityOnline();
                        @endphp
                        <button wire:click="{{ $clickable ? 'selectSlot(' . $slot->id . ')' : '' }}"
                            {{ !$clickable ? 'disabled' : '' }}
                            class="w-full p-5 border-2 rounded-2xl text-left transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-brand-hover touch-manipulation
                                {{ $selectedSlot == $slot->id
                                    ? 'border-brand-hover bg-brand-hover/10 shadow-md shadow-brand-hover/20'
                                    : ($clickable
                                        ? 'border-gray-200 bg-white hover:border-brand-hover/45 hover:shadow-sm cursor-pointer'
                                        : 'border-gray-100 bg-gray-50 cursor-not-allowed opacity-50') }}">
                            <div class="{{ $showCapacity ? 'flex justify-between items-start mb-3' : '' }}">
                                <span>
                                    <span
                                        class="text-xl font-bold text-gray-900">{{ $showSlotEndTime ? $slot->getTimeRange() : \App\Models\BookingSetting::formatTime($slot->start_time) }}</span>
                                    @if ($kioskMode ?? false ? $slot->showsLabelAtKiosk() : $slot->showsLabelOnline())
                                        <span
                                            class="block text-sm font-medium text-gray-500">{{ $slot->getTranslation('label', app()->getLocale()) }}</span>
                                    @endif
                                </span>
                                @if ($showCapacity)
                                    @php
                                        $remaining = $slot->getRemainingCapacity();
                                        $capacity = $slot->capacity ?? ($slot->max_capacity ?? 0);
                                        $pct =
                                            $capacity > 0
                                                ? min(100, round((($capacity - $remaining) / $capacity) * 100))
                                                : 0;
                                    @endphp
                                    @if (!$clickable)
                                        <span
                                            class="text-xs bg-red-100 text-red-600 px-2.5 py-1 rounded-full font-semibold">{{ __('event_booking.step2.full') }}</span>
                                    @elseif ($remaining <= 5)
                                        <span
                                            class="text-xs bg-amber-100 text-amber-700 px-2.5 py-1 rounded-full font-semibold">{{ __('event_booking.step2.almost_full') }}</span>
                                    @else
                                        <span
                                            class="text-xs bg-green-100 text-green-700 px-2.5 py-1 rounded-full font-semibold">{{ __('event_booking.step2.available') }}</span>
                                    @endif
                                @endif
                            </div>
                            @if ($showCapacity)
                                <p class="mb-2 text-sm text-gray-500">
                                    {{ $remaining }} {{ __('event_booking.step2.spots_remaining') }}
                                </p>
                                <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all
                                        {{ $pct > 80 ? 'bg-red-400' : ($pct > 50 ? 'bg-amber-400' : 'bg-green-400') }}"
                                        style="width: {{ $pct }}%"></div>
                                </div>
                            @endif
                        </button>
                    @endforeach
                </div>
                <div class="flex items-center gap-4 mt-6">
                    <button wire:click="previousStep"
                        class="touch-manipulation inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-gray-600 hover:text-gray-900 font-medium text-sm transition-colors border border-gray-200 rounded-xl hover:bg-gray-50 sm:w-auto">
                        ← {{ __('event_booking.back') }}
                    </button>
                </div>
            </div>
        @endif

        {{-- ════════════════════════════
             STEP 3 — Ticket Types & Quantities
        ════════════════════════════ --}}
        @if ($step === 3)
            <div>
                <h2 class="mb-1 text-2xl font-bold text-gray-900">{{ __('event_booking.step3.heading') }}</h2>
                <p class="mb-6 text-gray-500">{{ __('event_booking.step3.subheading') }}</p>

                @php $capacityOk = $enforceCapacity ?? true; @endphp
                <div class="space-y-4" wire:loading.class="opacity-50 pointer-events-none">
                    @foreach ($ticketTypes as $ticketType)
                        @php
                            $qty = $ticketQuantities[$ticketType->id] ?? 0;
                            $realAvailable = $ticketType->isAvailable();
                            $typeAvailable = $capacityOk ? $realAvailable : true;
                            $typeRemaining = $ticketTypeRemaining[$ticketType->id] ?? 0;
                            $atTypeLimit = $capacityOk && $qty >= $typeRemaining;
                            $dependencyIds = $ticketType->dependsOnMany->pluck('id')->all();
                            $isBlocked =
                                !empty($dependencyIds) &&
                                collect($dependencyIds)->every(fn($id) => ($ticketQuantities[$id] ?? 0) <= 0);
                            $parentName = !empty($dependencyIds)
                                ? $ticketTypes
                                    ->whereIn('id', $dependencyIds)
                                    ->map(fn($t) => $t->getTranslation('name', app()->getLocale()))
                                    ->implode(', ')
                                : null;
                        @endphp
                        <div
                            class="p-5 border-2 rounded-2xl transition-all duration-150
                            {{ $qty > 0 ? 'border-brand-hover bg-brand-hover/10 shadow-md shadow-brand-hover/20' : 'border-gray-200 bg-white' }}
                            {{ !$typeAvailable || $isBlocked ? 'opacity-60' : '' }}">

                            <div class="flex flex-wrap items-center gap-4">

                                {{-- Info --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="font-bold text-gray-900">
                                            {{ $ticketType->getTranslation('name', app()->getLocale()) }}
                                        </h3>
                                        @if ($qty > 0)
                                            <span
                                                class="text-xs bg-brand-hover text-white px-2 py-0.5 rounded-full font-medium">
                                                {{ __('event_booking.step3.n_selected', ['n' => $qty]) }}
                                            </span>
                                        @endif
                                        @if (!$realAvailable)
                                            <span
                                                class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full font-medium">
                                                {{ __('event_booking.step3.sold_out') }}
                                            </span>
                                            @if (!$capacityOk)
                                                <span
                                                    class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full font-medium">
                                                    {{ __('event_booking.step3.overbooking') }}
                                                </span>
                                            @endif
                                        @elseif ($typeRemaining <= 5)
                                            <span
                                                class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full font-medium">
                                                {{ $typeRemaining }} {{ __('event_booking.step3.tickets_available') }}
                                            </span>
                                        @endif
                                        @if ($parentName)
                                            <span
                                                class="text-xs px-2 py-0.5 rounded-full font-medium
                                                {{ $isBlocked ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700' }}">
                                                {{ __('event_booking.step3.requires_parent', ['parent' => $parentName]) }}
                                            </span>
                                        @endif
                                    </div>
                                    @if ($ticketType->getTranslation('description', app()->getLocale()))
                                        <p class="text-sm text-gray-500 mt-0.5">
                                            {{ $ticketType->getTranslation('description', app()->getLocale()) }}
                                        </p>
                                    @endif
                                </div>

                                {{-- Price --}}
                                <div class="text-right shrink-0">
                                    @if ($ticketType->price <= 0)
                                        <div class="text-2xl font-black text-green-600">
                                            {{ __('event_booking.step3.free') }}</div>
                                    @else
                                        <div class="text-2xl font-black text-gray-900">
                                            @include('partials.currency-amount', [
                                                'amount' => $ticketType->price,
                                            ])</div>
                                        <div class="text-xs text-gray-400">{{ __('event_booking.step3.per_ticket') }}
                                        </div>
                                    @endif
                                </div>

                                {{-- Quantity stepper --}}
                                @if ($typeAvailable && !$isBlocked)
                                    <div class="flex items-center gap-3 shrink-0">
                                        <button type="button" wire:click="decrementQuantity({{ $ticketType->id }})"
                                            {{ $qty <= 0 ? 'disabled' : '' }}
                                            class="kiosk-qty-btn touch-manipulation w-10 h-10 rounded-full border-2 flex items-center justify-center text-xl font-bold transition-colors
                                                {{ $qty <= 0 ? 'border-gray-200 text-gray-300 cursor-not-allowed' : 'border-gray-300 text-gray-600 hover:border-brand-hover hover:text-brand-hover' }}">
                                            −
                                        </button>
                                        <span wire:key="qty-{{ $ticketType->id }}-{{ $qty }}"
                                            @if ($kioskMode ?? false) x-data x-init="window.gsap && gsap.from($el, { scale: 1.5, duration: 0.3, ease: 'back.out(3)' })" @endif
                                            class="w-8 text-2xl font-black text-center text-gray-900 kiosk-qty-value tabular-nums">{{ $qty }}</span>
                                        @php
                                            $atMax =
                                                $qty >= $maxTickets ||
                                                array_sum($ticketQuantities) >= $maxTickets ||
                                                ($capacityOk &&
                                                    array_sum($ticketQuantities) >= $slotRemainingCapacity) ||
                                                $atTypeLimit;
                                        @endphp
                                        <button type="button" wire:click="incrementQuantity({{ $ticketType->id }})"
                                            {{ $atMax ? 'disabled' : '' }}
                                            class="kiosk-qty-btn touch-manipulation w-10 h-10 rounded-full border-2 flex items-center justify-center text-xl font-bold transition-colors
                                                {{ $atMax ? 'border-gray-200 text-gray-300 cursor-not-allowed' : 'border-gray-300 text-gray-600 hover:border-brand-hover hover:text-brand-hover' }}">
                                            +
                                        </button>
                                    </div>

                                    {{-- Row subtotal --}}
                                    @if ($qty > 0)
                                        <div class="text-right shrink-0 min-w-[80px]">
                                            <div class="text-sm font-semibold text-green-700">
                                                @if ($ticketType->price > 0)
                                                    @include('partials.currency-amount', [
                                                        'amount' => $ticketType->price * $qty,
                                                    ])@else{{ __('event_booking.step3.free') }}
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-400">
                                                {{ __('event_booking.step3.subtotal') }}</div>
                                        </div>
                                    @endif
                                @elseif ($typeAvailable && $isBlocked)
                                    {{-- Locked: parent ticket not yet selected --}}
                                    <div class="flex items-center gap-2 text-amber-600 shrink-0">
                                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                        <span class="text-xs font-medium">
                                            {{ __('event_booking.step3.add_parent_first', ['parent' => $parentName]) }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                @error('ticketQuantities')
                    <p class="mt-3 text-sm text-red-500">{{ $message }}</p>
                @enderror

                @if ($capacityOk && array_sum($ticketQuantities) >= $slotRemainingCapacity)
                    <p class="mt-3 text-sm text-amber-600">{{ __('event_booking.step3.slot_limit_reached') }}</p>
                @endif

                {{-- Running total --}}
                @php $totalQty = array_sum($ticketQuantities); @endphp
                @if ($totalQty > 0)
                    <div class="flex items-center justify-between p-4 mt-6 text-white bg-gray-900 sm:p-5 rounded-2xl">
                        <div>
                            <span
                                class="font-medium text-gray-300">{{ __('event_booking.step3.running_total') }}</span>
                            <span
                                class="text-xs text-gray-500 ms-2">{{ __('event_booking.step3.n_tickets', ['n' => $totalQty]) }}</span>
                        </div>
                        <span wire:key="running-total-{{ $totalPrice }}"
                            @if ($kioskMode ?? false) x-data x-init="window.gsap && gsap.from($el, { scale: 1.15, duration: 0.35, ease: 'back.out(2)' })" @endif
                            class="text-2xl font-black">@include('partials.currency-amount', ['amount' => $totalPrice])</span>
                    </div>
                @endif

                <div class="mt-6 flex items-center gap-4 {{ $kioskMode ?? false ? 'justify-between' : '' }}">
                    <button wire:click="previousStep"
                        class="touch-manipulation inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-gray-600 hover:text-gray-900 font-medium text-sm transition-colors border border-gray-200 rounded-xl hover:bg-gray-50 sm:w-auto">
                        ← {{ __('event_booking.back') }}
                    </button>
                    @if ($totalQty >= $minTickets)
                        <button wire:click="nextStep"
                            class="touch-manipulation px-7 py-2.5 bg-brand text-white font-semibold rounded-xl hover:bg-brand-hover transition-colors shadow-sm">
                            {{ __('event_booking.continue') }} →
                        </button>
                    @else
                        <p class="text-sm text-gray-400">
                            {{ __('event_booking.step3.min_tickets', ['n' => $minTickets]) }}
                        </p>
                    @endif
                </div>
            </div>
        @endif

        {{-- ════════════════════════════
             STEP 4 — Extra Services (per ticket type)
        ════════════════════════════ --}}
        @if ($step === 4)
            <div>
                <h2 class="mb-1 text-2xl font-bold text-gray-900">{{ __('event_booking.step4.heading') }}</h2>
                <p class="mb-6 text-gray-500">{{ __('event_booking.step4.subheading') }}</p>

                @if ($extraServices->isEmpty())
                    <div class="mb-6 text-center bg-white border border-gray-200 py-14 rounded-2xl">
                        <div class="mb-3 text-5xl">✨</div>
                        <p class="text-gray-500">{{ __('event_booking.step4.no_extras') }}</p>
                    </div>
                @else
                    <div class="space-y-8" wire:loading.class="opacity-50 pointer-events-none">
                        @foreach ($ticketTypes as $ticketType)
                            @php $qty = $ticketQuantities[$ticketType->id] ?? 0; @endphp
                            @if ($qty > 0)
                                <div>
                                    {{-- Ticket type section header --}}
                                    <div class="flex items-center gap-3 mb-3">
                                        <div class="flex-1 h-px bg-gray-200"></div>
                                        <span
                                            class="px-3 py-1 text-sm font-bold text-gray-700 bg-gray-100 rounded-full">
                                            {{ $ticketType->getTranslation('name', app()->getLocale()) }}
                                            <span class="font-normal text-gray-400 ms-1">× {{ $qty }}</span>
                                        </span>
                                        <div class="flex-1 h-px bg-gray-200"></div>
                                    </div>

                                    <div class="space-y-3">
                                        @foreach ($extraServices as $service)
                                            @php $selectedCount = $ticketTypeServices[$ticketType->id][$service->id] ?? 0; @endphp
                                            <div wire:key="service-{{ $ticketType->id }}-{{ $service->id }}-{{ $selectedCount > 0 ? 'on' : 'off' }}"
                                                @if ($kioskMode ?? false) x-data x-init="window.gsap && gsap.from($el, { scale: 0.97, duration: 0.25, ease: 'power2.out' })" @endif
                                                class="p-5 border-2 rounded-2xl transition-all duration-150
                                                    {{ $selectedCount > 0 ? 'border-brand-hover bg-brand-hover/10 shadow-md shadow-brand-hover/20' : 'border-gray-200 bg-white' }}">
                                                <div class="flex items-start gap-4">
                                                    <div class="flex-1 min-w-0">
                                                        <h3 class="font-bold text-gray-900">
                                                            {{ $service->getTranslation('name', app()->getLocale()) }}
                                                        </h3>
                                                        <p class="text-sm text-gray-500 mt-0.5">
                                                            {{ $service->getTranslation('description', app()->getLocale()) }}
                                                        </p>
                                                        <p class="mt-1 text-xs text-gray-400">
                                                            @include('partials.currency-amount', [
                                                                'amount' => $service->price,
                                                            ])
                                                            {{ __('event_booking.step4.per_ticket') }}
                                                            @if ($service->quantity_available)
                                                                · {{ $service->getRemainingQuantity() }}
                                                                {{ __('event_booking.step4.available') }}
                                                            @endif
                                                        </p>
                                                    </div>

                                                    {{-- Stepper: how many of this ticket type's tickets include the service --}}
                                                    <div class="shrink-0 flex flex-col items-end gap-1.5">
                                                        <div class="flex items-center gap-2">
                                                            <button type="button"
                                                                wire:click="decrementServiceQty({{ $ticketType->id }}, {{ $service->id }})"
                                                                {{ $selectedCount <= 0 ? 'disabled' : '' }}
                                                                class="touch-manipulation w-10 h-10 rounded-full border-2 flex items-center justify-center text-base font-bold transition-colors
                                                                    {{ $selectedCount <= 0 ? 'border-gray-200 text-gray-300 cursor-not-allowed' : 'border-gray-300 text-gray-600 hover:border-brand-hover hover:text-brand-hover' }}">
                                                                −
                                                            </button>
                                                            <span
                                                                class="w-12 text-base font-black text-center text-gray-900 tabular-nums">
                                                                {{ $selectedCount }}/{{ $qty }}
                                                            </span>
                                                            <button type="button"
                                                                wire:click="incrementServiceQty({{ $ticketType->id }}, {{ $service->id }})"
                                                                {{ $selectedCount >= $qty ? 'disabled' : '' }}
                                                                class="touch-manipulation w-10 h-10 rounded-full border-2 flex items-center justify-center text-base font-bold transition-colors
                                                                    {{ $selectedCount >= $qty ? 'border-gray-200 text-gray-300 cursor-not-allowed' : 'border-gray-300 text-gray-600 hover:border-brand-hover hover:text-brand-hover' }}">
                                                                +
                                                            </button>
                                                        </div>
                                                        @if ($selectedCount > 0)
                                                            <div class="text-xs font-medium text-brand-hover">
                                                                = @include('partials.currency-amount', [
                                                                    'amount' => $service->price * $selectedCount,
                                                                ])
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif

                {{-- Running Total --}}
                <div class="flex items-center justify-between p-4 mt-6 text-white bg-gray-900 sm:p-5 rounded-2xl">
                    <span class="font-medium text-gray-300">{{ __('event_booking.step3.running_total') }}</span>
                    <span wire:key="running-total-4-{{ $totalPrice }}"
                        @if ($kioskMode ?? false) x-data x-init="window.gsap && gsap.from($el, { scale: 1.15, duration: 0.35, ease: 'back.out(2)' })" @endif
                        class="text-2xl font-black">@include('partials.currency-amount', ['amount' => $totalPrice])</span>
                </div>

                <div class="mt-6 flex items-center gap-4 {{ $kioskMode ?? false ? 'justify-between' : '' }}">
                    <button wire:click="previousStep"
                        class="touch-manipulation inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-gray-600 hover:text-gray-900 font-medium text-sm transition-colors border border-gray-200 rounded-xl hover:bg-gray-50 sm:w-auto">
                        ← {{ __('event_booking.back') }}
                    </button>
                    <button wire:click="nextStep"
                        class="touch-manipulation px-7 py-2.5 bg-brand text-white font-semibold rounded-xl hover:bg-brand-hover transition-colors shadow-sm">
                        {{ __('event_booking.continue') }} →
                    </button>
                </div>
            </div>
        @endif

        {{-- ════════════════════════════
             STEP 5 — Attendee Details
        ════════════════════════════ --}}
        @if ($step === 5)
            @if ($progressiveAttendees ?? false)
                @include('livewire.partials.attendee-flow-progressive')
            @else
                <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">

                    {{-- Form (2/3 width) --}}
                    <div class="lg:col-span-2">
                        <h2 class="mb-1 text-2xl font-bold text-gray-900">{{ __('event_booking.step5.heading') }}</h2>
                        <p class="mb-6 text-gray-500">{{ __('event_booking.step5.subheading') }}</p>

                        {{-- Copy-contact banner (shown only when > 1 attendee) --}}
                        @if (count($attendees) > 1 && ($showEmail || $showPhone))
                            <div
                                class="flex items-start gap-3 p-4 mb-6 border border-indigo-200 bg-indigo-50 rounded-xl">
                                <input type="checkbox" wire:model.live="copyContactToAll" id="copyContactToAll"
                                    class="mt-0.5 h-4 w-4 rounded border-indigo-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer shrink-0">
                                <label for="copyContactToAll"
                                    class="text-sm leading-snug text-indigo-800 cursor-pointer">
                                    <span class="font-semibold">{{ __('event_booking.step5.copy_contact') }}</span>
                                    <span class="block text-indigo-500 text-xs mt-0.5">
                                        {{ __('event_booking.step5.email_label') }}{{ $showPhone ? ' & ' . __('event_booking.step5.phone_label') : '' }}
                                        {{ __('event_booking.step5.copy_contact_hint') }}
                                    </span>
                                </label>
                            </div>
                        @endif

                        <form wire:submit.prevent="goToPaymentStep" class="space-y-6" novalidate>

                            @foreach ($attendees as $i => $attendee)
                                @php $attendeeTicketType = $ticketTypes->find($attendee['ticket_type_id'] ?? null); @endphp

                                <div
                                    class="border-2 rounded-2xl
                                {{ $i === 0 ? 'border-brand-hover/30' : 'border-gray-200' }}">

                                    {{-- Attendee header --}}
                                    <div
                                        class="px-5 py-3 flex items-center gap-3 rounded-t-2xl
                                    {{ $i === 0 ? 'bg-brand-hover/10 border-b border-brand-hover/30' : 'bg-gray-50 border-b border-gray-200' }}">
                                        <div
                                            class="w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold shrink-0
                                        {{ $i === 0 ? 'bg-brand-hover text-white' : 'bg-gray-400 text-white' }}">
                                            {{ $i + 1 }}
                                        </div>
                                        <span class="font-semibold text-gray-800">
                                            {{ __('event_booking.step5.attendee_n', ['n' => $i + 1]) }}
                                            @if ($i === 0)
                                                <span
                                                    class="text-xs font-normal text-brand-hover ms-1">({{ __('event_booking.step5.primary') }})</span>
                                            @endif
                                        </span>
                                        @if ($attendeeTicketType)
                                            <span
                                                class="text-xs bg-white border border-gray-300 text-gray-600 px-2 py-0.5 rounded-full ms-auto">
                                                {{ $attendeeTicketType->getTranslation('name', app()->getLocale()) }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="p-5 space-y-4">
                                        @include('livewire.partials.attendee-fields', [
                                            'i' => $i,
                                            'attendee' => $attendee,
                                        ])
                                    </div>
                                </div>
                            @endforeach

                            {{-- Terms and Conditions --}}
                            @if ($termsEn || $termsAr)
                                <div class="overflow-hidden border border-gray-200 rounded-xl"
                                    x-data="{ open: false }">
                                    <button type="button"
                                        class="flex items-center justify-between w-full px-4 py-3 text-left bg-gray-50 focus:outline-none"
                                        @click="open = !open" :aria-expanded="open">
                                        <h4 class="text-sm font-semibold text-gray-800">
                                            {{ __('event_booking.step5.terms_heading') }}</h4>
                                        <svg class="w-4 h-4 text-gray-500 transition-transform duration-200"
                                            :class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <div x-show="open" x-transition:enter="transition-all duration-200 ease-out"
                                        x-transition:enter-start="opacity-0 max-h-0"
                                        x-transition:enter-end="opacity-100 max-h-96"
                                        x-transition:leave="transition-all duration-150 ease-in"
                                        x-transition:leave-start="opacity-100 max-h-96"
                                        x-transition:leave-end="opacity-0 max-h-0"
                                        class="overflow-hidden border-t border-gray-200">
                                        @if (app()->getLocale() === 'ar' && $termsAr)
                                            <div class="px-4 py-3">
                                                <div class="overflow-y-auto text-sm leading-relaxed prose-sm prose text-gray-600 max-w-none max-h-48"
                                                    dir="rtl">
                                                    {!! $termsAr !!}
                                                </div>
                                            </div>
                                        @elseif ($termsEn)
                                            <div class="px-4 py-3">
                                                <div class="overflow-y-auto text-sm leading-relaxed prose-sm prose text-gray-600 max-w-none max-h-48"
                                                    dir="ltr">
                                                    {!! $termsEn !!}
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex items-start gap-3">
                                    <input type="checkbox" wire:model="agreedToTerms" id="agreedToTerms"
                                        class="mt-0.5 h-4 w-4 rounded border-gray-300 text-brand-hover focus:ring-brand-hover cursor-pointer shrink-0
                                        {{ $errors->has('agreedToTerms') ? 'border-red-400 ring-1 ring-red-300' : '' }}">
                                    <label for="agreedToTerms"
                                        class="text-sm leading-snug text-gray-700 cursor-pointer">
                                        {{ __('event_booking.step5.terms_agree') }}
                                        @php
                                            $termsUrl = app()->getLocale() === 'ar' ? $termsUrlAr : $termsUrlEn;
                                        @endphp
                                        @if ($termsUrl)
                                            <a href="{{ $termsUrl }}" target="_blank" rel="noopener noreferrer"
                                                class="font-semibold text-gray-900 underline hover:text-brand">{{ __('event_booking.step5.terms_heading') }}</a>
                                        @else
                                            <span
                                                class="font-semibold text-gray-900">{{ __('event_booking.step5.terms_heading') }}</span>
                                        @endif
                                        <span class="text-red-500 ms-0.5">*</span>
                                    </label>
                                </div>
                                @error('agreedToTerms')
                                    <p class="-mt-3 text-sm text-red-500">{{ __('event_booking.step5.terms_required') }}
                                    </p>
                                @enderror
                            @endif

                            {{-- Nav + Continue to Payment --}}
                            <div class="flex flex-col items-stretch gap-3 pt-2 sm:flex-row sm:items-center">
                                <button type="button" wire:click="previousStep"
                                    class="touch-manipulation inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-gray-600 hover:text-gray-900 font-medium text-sm transition-colors border border-gray-200 rounded-xl hover:bg-gray-50 sm:w-auto">
                                    ← {{ __('event_booking.back') }}
                                </button>
                                <button type="submit"
                                    class="flex-1 px-8 py-3 text-base font-bold text-white transition-colors shadow-md touch-manipulation sm:flex-none bg-brand rounded-xl hover:bg-brand-hover shadow-brand/30 disabled:opacity-60 disabled:cursor-not-allowed"
                                    wire:loading.attr="disabled" wire:target="goToPaymentStep">
                                    <span wire:loading.remove wire:target="goToPaymentStep">
                                        {{ __('event_booking.step5.continue_to_payment') }} →
                                    </span>
                                    <span wire:loading wire:target="goToPaymentStep"
                                        class="inline-flex items-center gap-2">
                                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                stroke="currentColor" stroke-width="4" />
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                        </svg>
                                        {{ __('event_booking.step5.validating') }}
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Sticky Summary Sidebar (1/3 width) --}}
                    <div class="lg:col-span-1">
                        <div
                            class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-2xl lg:sticky lg:top-24">
                            <div class="px-5 py-4 text-white bg-gradient-to-r from-brand to-brand-hover">
                                <h3 class="text-base font-bold">{{ __('event_booking.step5.booking_summary') }}</h3>
                            </div>
                            <div class="p-5 space-y-4 text-sm">

                                <div>
                                    <p class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-0.5">
                                        {{ __('event_booking.summary.event') }}</p>
                                    <p class="font-semibold leading-tight text-gray-900">
                                        {{ $event->getTranslation('title', app()->getLocale()) }}</p>
                                </div>

                                <div class="grid grid-cols-2 gap-3 pt-3 border-t border-gray-100">
                                    <div>
                                        <p class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-0.5">
                                            {{ __('event_booking.summary.date') }}</p>
                                        <p class="font-medium text-gray-800">
                                            {{ \Carbon\Carbon::parse($selectedDate)->locale(app()->getLocale())->translatedFormat('M d, Y') }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-0.5">
                                            {{ __('event_booking.summary.time') }}</p>
                                        <p class="font-medium text-gray-800">
                                            {{ $showSlotEndTime ? $timeSlots->find($selectedSlot)?->getTimeRange() : \App\Models\BookingSetting::formatTime($timeSlots->find($selectedSlot)?->start_time) }}
                                        </p>
                                        @php $summarySlot = $timeSlots->find($selectedSlot); @endphp
                                        @if ($summarySlot && ($kioskMode ?? false ? $summarySlot->showsLabelAtKiosk() : $summarySlot->showsLabelOnline()))
                                            <p class="text-sm text-gray-500">
                                                {{ $summarySlot->getTranslation('label', app()->getLocale()) }}</p>
                                        @endif
                                    </div>
                                </div>



                                {{-- Tickets breakdown --}}
                                <div class="pt-3 border-t border-gray-100">
                                    <p class="mb-2 text-xs font-bold tracking-wider text-gray-400 uppercase">
                                        {{ __('event_booking.summary.tickets') }}</p>
                                    @foreach ($ticketTypes as $ticketType)
                                        @php $qty = $ticketQuantities[$ticketType->id] ?? 0; @endphp
                                        @if ($qty > 0)
                                            <div class="flex items-center justify-between mb-1 text-gray-700">
                                                <span
                                                    class="truncate pe-2">{{ $ticketType->getTranslation('name', app()->getLocale()) }}</span>
                                                <span class="text-xs text-gray-400 shrink-0">×
                                                    {{ $qty }}</span>
                                            </div>
                                            <div class="flex justify-between mb-2 text-xs text-gray-500">
                                                @if ($ticketType->price > 0)
                                                    <span>@include('partials.currency-amount', [
                                                        'amount' => $ticketType->price,
                                                    ]) ×
                                                        {{ $qty }}</span>
                                                    <span>@include('partials.currency-amount', [
                                                        'amount' => $ticketType->price * $qty,
                                                    ])</span>
                                                @else
                                                    <span>{{ __('event_booking.step3.free') }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    @endforeach
                                </div>

                                {{-- Attendees breakdown --}}
                                @if (count($attendees))
                                    <div class="pt-3 border-t border-gray-100">
                                        <p class="mb-2 text-xs font-bold tracking-wider text-gray-400 uppercase">
                                            {{ __('event_booking.summary.attendees') }}</p>
                                        @foreach ($attendees as $attendee)
                                            @php $attendeeTicketType = $ticketTypes->find($attendee['ticket_type_id'] ?? null); @endphp
                                            <div class="flex items-center justify-between mb-1 text-gray-700">
                                                <span
                                                    class="truncate pe-2">{{ trim(($attendee['first_name'] ?? '') . ' ' . ($attendee['last_name'] ?? '')) ?: '—' }}</span>
                                                <span
                                                    class="shrink-0 text-gray-400 text-xs truncate max-w-[40%]">{{ $attendeeTicketType?->getTranslation('name', app()->getLocale()) }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Services breakdown --}}
                                @php
                                    $hasServices = collect($ticketTypeServices)->flatten()->sum() > 0;
                                @endphp
                                @if ($hasServices)
                                    <div class="pt-3 border-t border-gray-100">
                                        <p class="mb-2 text-xs font-bold tracking-wider text-gray-400 uppercase">
                                            {{ __('event_booking.summary.extra_services') }}</p>
                                        @foreach ($ticketTypes as $ticketType)
                                            @php
                                                $qty = $ticketQuantities[$ticketType->id] ?? 0;
                                                $serviceCounts = $ticketTypeServices[$ticketType->id] ?? [];
                                            @endphp
                                            @if ($qty > 0 && !empty($serviceCounts))
                                                <p class="mb-1 text-xs text-gray-400">
                                                    {{ $ticketType->getTranslation('name', app()->getLocale()) }}</p>
                                                @foreach ($serviceCounts as $serviceId => $count)
                                                    @php $svc = $count > 0 ? $extraServices->find($serviceId) : null; @endphp
                                                    @if ($svc)
                                                        <div class="flex justify-between mb-1 text-xs text-gray-600">
                                                            <span
                                                                class="truncate pe-2">{{ $svc->getTranslation('name', app()->getLocale()) }}
                                                                × {{ $count }}</span>
                                                            <span class="shrink-0">@include('partials.currency-amount', [
                                                                'amount' => $svc->price * $count,
                                                            ])</span>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            @endif
                                        @endforeach
                                    </div>
                                @endif

                                <div class="flex items-baseline justify-between pt-3 border-t-2 border-gray-200">
                                    <span
                                        class="text-base font-bold text-gray-900">{{ __('event_booking.summary.total') }}</span>
                                    <span
                                        class="text-2xl font-black text-green-700">@include('partials.currency-amount', ['amount' => $totalPrice])</span>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>
            @endif
        @endif
