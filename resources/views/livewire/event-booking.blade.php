<div class="min-h-screen bg-gray-50" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

    {{-- ── Event Hero ── --}}
    <div class="bg-gradient-to-r from-brand to-brand-hover text-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-8 sm:py-10">
            {{-- Language Switcher --}}
            <div class="flex justify-end mb-4">
                @if (app()->getLocale() === 'ar')
                    <a href="{{ route('lang.switch', 'en') }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-white/20 hover:bg-white/30 text-white rounded-full transition-colors">
                        🌐 {{ __('event_booking.switch_to_english') }}
                    </a>
                @else
                    <a href="{{ route('lang.switch', 'ar') }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-white/20 hover:bg-white/30 text-white rounded-full transition-colors">
                        🌐 {{ __('event_booking.switch_to_arabic') }}
                    </a>
                @endif
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold leading-tight">
                {{ $event->getTranslation('title', app()->getLocale()) }}
            </h1>
            <p class="mt-2 text-blue-100 text-base sm:text-lg line-clamp-2">
                {{ $event->getTranslation('description', app()->getLocale()) }}
            </p>
            <div class="mt-3 flex flex-wrap gap-x-5 gap-y-1 text-sm text-blue-200">
                <span>📍 {{ $event->getTranslation('location', app()->getLocale()) }}</span>
                <span>📅 {{ $event->start_date->format('M d') }} – {{ $event->end_date->format('M d, Y') }}</span>
                @if ($event->organizer)
                    <span>👤 {{ $event->organizer }}</span>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Sticky Progress Stepper ── --}}
    <div class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-20">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-4">
            <div class="flex items-center">
                @php
                    $steps = [1 => __('event_booking.steps.date'), 2 => __('event_booking.steps.time'), 3 => __('event_booking.steps.tickets')];
                    if ($extraServices->isNotEmpty()) {
                        $steps[4] = __('event_booking.steps.extras');
                    }
                    $steps[5] = __('event_booking.steps.details');
                    $steps[6] = __('event_booking.steps.payment');
                @endphp
                @foreach ($steps as $num => $label)
                    <div class="flex items-center {{ $num < 6 ? 'flex-1' : '' }}">
                        <div class="flex items-center gap-2 shrink-0">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-colors
                                {{ $step > $num  ? 'bg-green-500 text-white'
                                : ($step === $num ? 'bg-blue-600 text-white ring-4 ring-blue-100'
                                                  : 'bg-gray-200 text-gray-500') }}">
                                @if ($step > $num)
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                    </svg>
                                @else
                                    {{ $num }}
                                @endif
                            </div>
                            <span class="hidden sm:block text-sm font-medium
                                {{ $step >= $num ? 'text-gray-800' : 'text-gray-400' }}">
                                {{ $label }}
                            </span>
                        </div>
                        @if ($num < 6)
                            <div class="flex-1 h-0.5 mx-2 sm:mx-3 transition-colors
                                {{ $step > $num ? 'bg-green-400' : 'bg-gray-200' }}"></div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── Main Content ── --}}
    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-8">

        {{-- Error Flash --}}
        @if (session()->has('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-start gap-3">
                <svg class="w-5 h-5 mt-0.5 shrink-0 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <p>{{ session('error') }}</p>
            </div>
        @endif

        {{-- ════════════════════════════
             STEP 1 — Select Date
        ════════════════════════════ --}}
        @if ($step === 1)
            <div wire:loading.class="opacity-50 pointer-events-none">
                <h2 class="text-2xl font-bold text-gray-900 mb-1">{{ __('event_booking.step1.heading') }}</h2>
                <p class="text-gray-500 mb-6">{{ __('event_booking.step1.subheading') }}</p>

                @if ($availableDates->isEmpty())
                    <div class="text-center py-16 bg-white rounded-2xl border border-gray-200">
                        <div class="text-5xl mb-4">📅</div>
                        <h3 class="text-lg font-semibold text-gray-700">{{ __('event_booking.step1.no_dates') }}</h3>
                        <p class="text-gray-400 mt-1">{{ __('event_booking.step1.no_dates_body') }}</p>
                    </div>
                @else
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                        @foreach ($availableDates as $date)
                            @php $d = \Carbon\Carbon::parse($date); @endphp
                            <button wire:click="selectDate('{{ $date }}')"
                                class="group p-4 border-2 rounded-2xl text-center transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-blue-500
                                    {{ $selectedDate === $date
                                        ? 'border-blue-500 bg-blue-50 shadow-md shadow-blue-100'
                                        : 'border-gray-200 bg-white hover:border-blue-300 hover:shadow-sm' }}">
                                <div class="text-xs font-bold uppercase tracking-widest
                                    {{ $selectedDate === $date ? 'text-blue-400' : 'text-gray-400' }}">
                                    {{ $d->format('D') }}
                                </div>
                                <div class="text-4xl font-black my-1
                                    {{ $selectedDate === $date ? 'text-blue-600' : 'text-gray-800' }}">
                                    {{ $d->format('d') }}
                                </div>
                                <div class="text-sm font-semibold
                                    {{ $selectedDate === $date ? 'text-blue-500' : 'text-gray-500' }}">
                                    {{ $d->format('M Y') }}
                                </div>
                                @if ($selectedDate === $date)
                                    <div class="mt-2">
                                        <span class="inline-block w-2 h-2 rounded-full bg-blue-500"></span>
                                    </div>
                                @endif
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div wire:loading wire:target="selectDate" class="fixed inset-0 bg-white/60 flex items-center justify-center z-50">
                <svg class="animate-spin w-8 h-8 text-blue-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
            </div>
        @endif

        {{-- ════════════════════════════
             STEP 2 — Choose Time Slot
        ════════════════════════════ --}}
        @if ($step === 2)
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-1">{{ __('event_booking.step2.heading') }}</h2>
                <p class="text-gray-500 mb-6">
                    {{ __('event_booking.step2.subheading') }}
                    <strong class="text-gray-700">{{ \Carbon\Carbon::parse($selectedDate)->format('l, F j, Y') }}</strong>
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" wire:loading.class="opacity-50 pointer-events-none">
                    @foreach ($timeSlots as $slot)
                        @php
                            $slotAvailable = $slot->isAvailable();
                            $remaining     = $slot->getRemainingCapacity();
                            $capacity      = $slot->capacity ?? ($slot->max_capacity ?? 0);
                            $pct           = $capacity > 0 ? min(100, round((($capacity - $remaining) / $capacity) * 100)) : 0;
                        @endphp
                        <button
                            wire:click="{{ $slotAvailable ? 'selectSlot(' . $slot->id . ')' : '' }}"
                            {{ !$slotAvailable ? 'disabled' : '' }}
                            class="w-full p-5 border-2 rounded-2xl text-left transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-blue-500
                                {{ $selectedSlot == $slot->id
                                    ? 'border-blue-500 bg-blue-50 shadow-md shadow-blue-100'
                                    : ($slotAvailable ? 'border-gray-200 bg-white hover:border-blue-300 hover:shadow-sm cursor-pointer' : 'border-gray-100 bg-gray-50 cursor-not-allowed opacity-50') }}">
                            <div class="flex justify-between items-start mb-3">
                                <span class="text-xl font-bold text-gray-900">{{ $slot->getTimeRange() }}</span>
                                @if (!$slotAvailable)
                                    <span class="text-xs bg-red-100 text-red-600 px-2.5 py-1 rounded-full font-semibold">{{ __('event_booking.step2.full') }}</span>
                                @elseif ($remaining <= 5)
                                    <span class="text-xs bg-amber-100 text-amber-700 px-2.5 py-1 rounded-full font-semibold">{{ __('event_booking.step2.almost_full') }}</span>
                                @else
                                    <span class="text-xs bg-green-100 text-green-700 px-2.5 py-1 rounded-full font-semibold">{{ __('event_booking.step2.available') }}</span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-500 mb-2">
                                {{ $remaining }} {{ __('event_booking.step2.spots_remaining') }}
                            </p>
                            <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all
                                    {{ $pct > 80 ? 'bg-red-400' : ($pct > 50 ? 'bg-amber-400' : 'bg-green-400') }}"
                                    style="width: {{ $pct }}%"></div>
                            </div>
                        </button>
                    @endforeach
                </div>

                <button wire:click="previousStep"
                    class="mt-6 inline-flex items-center gap-1.5 text-gray-500 hover:text-gray-800 font-medium text-sm transition-colors">
                    ← {{ __('event_booking.back') }}
                </button>
            </div>
        @endif

        {{-- ════════════════════════════
             STEP 3 — Ticket Types & Quantities
        ════════════════════════════ --}}
        @if ($step === 3)
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-1">{{ __('event_booking.step3.heading') }}</h2>
                <p class="text-gray-500 mb-6">{{ __('event_booking.step3.subheading') }}</p>

                <div class="space-y-4" wire:loading.class="opacity-50 pointer-events-none">
                    @foreach ($ticketTypes as $ticketType)
                        @php
                            $qty           = $ticketQuantities[$ticketType->id] ?? 0;
                            $typeAvailable = $ticketType->isAvailable();
                        @endphp
                        <div class="p-5 border-2 rounded-2xl transition-all duration-150
                            {{ $qty > 0 ? 'border-blue-500 bg-blue-50 shadow-md shadow-blue-100' : 'border-gray-200 bg-white' }}
                            {{ !$typeAvailable ? 'opacity-50' : '' }}">

                            <div class="flex flex-wrap items-center gap-4">

                                {{-- Info --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h3 class="font-bold text-gray-900">
                                            {{ $ticketType->getTranslation('name', app()->getLocale()) }}
                                        </h3>
                                        @if ($qty > 0)
                                            <span class="text-xs bg-blue-600 text-white px-2 py-0.5 rounded-full font-medium">
                                                {{ __('event_booking.step3.n_selected', ['n' => $qty]) }}
                                            </span>
                                        @endif
                                        @if (!$typeAvailable)
                                            <span class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full font-medium">{{ __('event_booking.step3.sold_out') }}</span>
                                        @endif
                                    </div>
                                    @if ($ticketType->getTranslation('description', app()->getLocale()))
                                        <p class="text-sm text-gray-500 mt-0.5">
                                            {{ $ticketType->getTranslation('description', app()->getLocale()) }}
                                        </p>
                                    @endif
                                    <p class="text-xs text-gray-400 mt-1">
                                        {{ $ticketType->getRemainingQuantity() }} {{ __('event_booking.step3.tickets_available') }}
                                    </p>
                                </div>

                                {{-- Price --}}
                                <div class="text-right shrink-0">
                                    <div class="text-2xl font-black text-gray-900">OMR{{ number_format($ticketType->price, 3) }}</div>
                                    <div class="text-xs text-gray-400">{{ __('event_booking.step3.per_ticket') }}</div>
                                </div>

                                {{-- Quantity stepper --}}
                                @if ($typeAvailable)
                                    <div class="flex items-center gap-3 shrink-0">
                                        <button type="button"
                                            wire:click="decrementQuantity({{ $ticketType->id }})"
                                            {{ $qty <= 0 ? 'disabled' : '' }}
                                            class="w-10 h-10 rounded-full border-2 flex items-center justify-center text-xl font-bold transition-colors
                                                {{ $qty <= 0 ? 'border-gray-200 text-gray-300 cursor-not-allowed' : 'border-gray-300 text-gray-600 hover:border-blue-500 hover:text-blue-600' }}">
                                            −
                                        </button>
                                        <span class="text-2xl font-black text-gray-900 w-8 text-center tabular-nums">{{ $qty }}</span>
                                        <button type="button"
                                            wire:click="incrementQuantity({{ $ticketType->id }})"
                                            {{ ($ticketQuantities[$ticketType->id] ?? 0) >= $maxTickets || array_sum($ticketQuantities) >= $maxTickets ? 'disabled' : '' }}
                                            class="w-10 h-10 rounded-full border-2 flex items-center justify-center text-xl font-bold transition-colors
                                                {{ ($ticketQuantities[$ticketType->id] ?? 0) >= $maxTickets || array_sum($ticketQuantities) >= $maxTickets ? 'border-gray-200 text-gray-300 cursor-not-allowed' : 'border-gray-300 text-gray-600 hover:border-blue-500 hover:text-blue-600' }}">
                                            +
                                        </button>
                                    </div>

                                    {{-- Row subtotal --}}
                                    @if ($qty > 0)
                                        <div class="text-right shrink-0 min-w-[80px]">
                                            <div class="text-sm font-semibold text-blue-700">
                                                OMR{{ number_format($ticketType->price * $qty, 3) }}
                                            </div>
                                            <div class="text-xs text-gray-400">{{ __('event_booking.step3.subtotal') }}</div>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                @error('ticketQuantities')
                    <p class="mt-3 text-red-500 text-sm">{{ $message }}</p>
                @enderror

                {{-- Running total --}}
                @php $totalQty = array_sum($ticketQuantities); @endphp
                @if ($totalQty > 0)
                    <div class="mt-6 p-4 sm:p-5 bg-gray-900 text-white rounded-2xl flex justify-between items-center">
                        <div>
                            <span class="font-medium text-gray-300">{{ __('event_booking.step3.running_total') }}</span>
                            <span class="ml-2 text-xs text-gray-500">{{ __('event_booking.step3.n_tickets', ['n' => $totalQty]) }}</span>
                        </div>
                        <span class="text-2xl font-black">OMR{{ number_format($totalPrice, 3) }}</span>
                    </div>
                @endif

                <div class="mt-6 flex items-center gap-4">
                    <button wire:click="previousStep"
                        class="inline-flex items-center gap-1.5 text-gray-500 hover:text-gray-800 font-medium text-sm transition-colors">
                        ← {{ __('event_booking.back') }}
                    </button>
                    @if ($totalQty >= $minTickets)
                        <button wire:click="nextStep"
                            class="px-7 py-2.5 bg-brand text-white font-semibold rounded-xl hover:bg-brand-hover transition-colors shadow-sm">
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
                <h2 class="text-2xl font-bold text-gray-900 mb-1">{{ __('event_booking.step4.heading') }}</h2>
                <p class="text-gray-500 mb-6">{{ __('event_booking.step4.subheading') }}</p>

                @if ($extraServices->isEmpty())
                    <div class="text-center py-14 bg-white rounded-2xl border border-gray-200 mb-6">
                        <div class="text-5xl mb-3">✨</div>
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
                                        <div class="h-px flex-1 bg-gray-200"></div>
                                        <span class="text-sm font-bold text-gray-700 px-3 py-1 bg-gray-100 rounded-full">
                                            {{ $ticketType->getTranslation('name', app()->getLocale()) }}
                                            <span class="text-gray-400 font-normal ml-1">× {{ $qty }}</span>
                                        </span>
                                        <div class="h-px flex-1 bg-gray-200"></div>
                                    </div>

                                    <div class="space-y-3">
                                        @foreach ($extraServices as $service)
                                            @php $isSelected = in_array($service->id, $ticketTypeServices[$ticketType->id] ?? []); @endphp
                                            <div wire:click="toggleService({{ $ticketType->id }}, {{ $service->id }})"
                                                class="p-5 border-2 rounded-2xl cursor-pointer transition-all duration-150
                                                    {{ $isSelected
                                                        ? 'border-blue-500 bg-blue-50 shadow-md shadow-blue-100'
                                                        : 'border-gray-200 bg-white hover:border-blue-300 hover:shadow-sm' }}">
                                                <div class="flex items-start gap-4">
                                                    {{-- Custom checkbox --}}
                                                    <div class="mt-0.5 w-5 h-5 rounded border-2 flex items-center justify-center shrink-0 transition-colors
                                                        {{ $isSelected ? 'border-blue-500 bg-blue-500' : 'border-gray-300 bg-white' }}">
                                                        @if ($isSelected)
                                                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M5 13l4 4L19 7"/>
                                                            </svg>
                                                        @endif
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <h3 class="font-bold text-gray-900">
                                                            {{ $service->getTranslation('name', app()->getLocale()) }}
                                                        </h3>
                                                        <p class="text-sm text-gray-500 mt-0.5">
                                                            {{ $service->getTranslation('description', app()->getLocale()) }}
                                                        </p>
                                                        @if ($service->quantity_available)
                                                            <p class="text-xs text-gray-400 mt-1">{{ $service->getRemainingQuantity() }} {{ __('event_booking.step4.available') }}</p>
                                                        @endif
                                                    </div>
                                                    <div class="shrink-0 text-right">
                                                        <div class="text-xl font-bold text-gray-900">+OMR {{ number_format($service->price, 3) }}</div>
                                                        <div class="text-xs text-gray-400">{{ __('event_booking.step4.per_ticket') }}</div>
                                                        @if ($isSelected)
                                                            <div class="text-xs text-blue-600 font-medium mt-0.5">
                                                                = OMR {{ number_format($service->price * $qty, 3) }}
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
                <div class="mt-6 p-4 sm:p-5 bg-gray-900 text-white rounded-2xl flex justify-between items-center">
                    <span class="font-medium text-gray-300">{{ __('event_booking.step3.running_total') }}</span>
                    <span class="text-2xl font-black">OMR{{ number_format($totalPrice, 3) }}</span>
                </div>

                <div class="mt-6 flex items-center gap-4">
                    <button wire:click="previousStep"
                        class="inline-flex items-center gap-1.5 text-gray-500 hover:text-gray-800 font-medium text-sm transition-colors">
                        ← {{ __('event_booking.back') }}
                    </button>
                    <button wire:click="nextStep"
                        class="px-7 py-2.5 bg-brand text-white font-semibold rounded-xl hover:bg-brand-hover transition-colors shadow-sm">
                        {{ __('event_booking.continue') }} →
                    </button>
                </div>
            </div>
        @endif

        {{-- ════════════════════════════
             STEP 5 — Attendee Details
        ════════════════════════════ --}}
        @if ($step === 5)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                {{-- Form (2/3 width) --}}
                <div class="lg:col-span-2">
                    <h2 class="text-2xl font-bold text-gray-900 mb-1">{{ __('event_booking.step5.heading') }}</h2>
                    <p class="text-gray-500 mb-6">{{ __('event_booking.step5.subheading') }}</p>

                    {{-- Copy-contact banner (shown only when > 1 attendee) --}}
                    @if (count($attendees) > 1 && ($showEmail || $showPhone))
                        <div class="mb-6 p-4 bg-indigo-50 border border-indigo-200 rounded-xl flex items-start gap-3">
                            <input type="checkbox"
                                wire:model.live="copyContactToAll"
                                id="copyContactToAll"
                                class="mt-0.5 h-4 w-4 rounded border-indigo-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer shrink-0">
                            <label for="copyContactToAll" class="text-sm text-indigo-800 cursor-pointer leading-snug">
                                <span class="font-semibold">{{ __('event_booking.step5.copy_contact') }}</span>
                                <span class="block text-indigo-500 text-xs mt-0.5">
                                    {{ __('event_booking.step5.email_label') }}{{ $showPhone ? ' & ' . __('event_booking.step5.phone_label') : '' }} {{ __('event_booking.step5.copy_contact_hint') }}
                                </span>
                            </label>
                        </div>
                    @endif

                    <form wire:submit.prevent="goToPaymentStep" class="space-y-6">

                        @foreach ($attendees as $i => $attendee)
                            @php $attendeeTicketType = $ticketTypes->find($attendee['ticket_type_id'] ?? null); @endphp

                            <div class="border-2 rounded-2xl
                                {{ $i === 0 ? 'border-blue-200' : 'border-gray-200' }}">

                                {{-- Attendee header --}}
                                <div class="px-5 py-3 flex items-center gap-3 rounded-t-2xl
                                    {{ $i === 0 ? 'bg-blue-50 border-b border-blue-200' : 'bg-gray-50 border-b border-gray-200' }}">
                                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold shrink-0
                                        {{ $i === 0 ? 'bg-blue-600 text-white' : 'bg-gray-400 text-white' }}">
                                        {{ $i + 1 }}
                                    </div>
                                    <span class="font-semibold text-gray-800">
                                        {{ __('event_booking.step5.attendee_n', ['n' => $i + 1]) }}
                                        @if ($i === 0)
                                            <span class="text-xs text-blue-500 font-normal ml-1">({{ __('event_booking.step5.primary') }})</span>
                                        @endif
                                    </span>
                                    @if ($attendeeTicketType)
                                        <span class="text-xs bg-white border border-gray-300 text-gray-600 px-2 py-0.5 rounded-full ml-auto">
                                            {{ $attendeeTicketType->getTranslation('name', app()->getLocale()) }}
                                        </span>
                                    @endif
                                </div>

                                <div class="p-5 space-y-4">

                                    {{-- Name --}}
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                                                {{ __('event_booking.step5.first_name') }} <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text"
                                                wire:model="attendees.{{ $i }}.first_name"
                                                placeholder="{{ __('event_booking.step5.first_name_placeholder') }}"
                                                class="w-full px-4 py-2.5 border rounded-xl text-gray-900 placeholder-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition
                                                    {{ $errors->has("attendees.$i.first_name") ? 'border-red-400 bg-red-50 ring-1 ring-red-300' : 'border-gray-300' }}">
                                            @error("attendees.$i.first_name")
                                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                                                {{ __('event_booking.step5.last_name') }} <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text"
                                                wire:model="attendees.{{ $i }}.last_name"
                                                placeholder="{{ __('event_booking.step5.last_name_placeholder') }}"
                                                class="w-full px-4 py-2.5 border rounded-xl text-gray-900 placeholder-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition
                                                    {{ $errors->has("attendees.$i.last_name") ? 'border-red-400 bg-red-50 ring-1 ring-red-300' : 'border-gray-300' }}">
                                            @error("attendees.$i.last_name")
                                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>

                                    {{-- Email --}}
                                    @if ($showEmail)
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                                                {{ __('event_booking.step5.email_address') }} <span class="text-red-500">*</span>
                                            </label>
                                            <input type="email"
                                                wire:model.live="attendees.{{ $i }}.email"
                                                placeholder="john@example.com"
                                                {{ $copyContactToAll && $i > 0 ? 'readonly' : '' }}
                                                class="w-full px-4 py-2.5 border rounded-xl text-gray-900 placeholder-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition
                                                    {{ $copyContactToAll && $i > 0 ? 'bg-gray-50 text-gray-500 cursor-not-allowed' : '' }}
                                                    {{ $errors->has("attendees.$i.email") ? 'border-red-400 bg-red-50 ring-1 ring-red-300' : 'border-gray-300' }}">
                                            @if ($i === 0)
                                                <p class="text-xs text-gray-400 mt-1.5">{{ __('event_booking.step5.email_hint') }}</p>
                                            @endif
                                            @error("attendees.$i.email")
                                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    @endif

                                    {{-- Phone --}}
                                    @if ($showPhone)
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('event_booking.step5.phone_number') }}</label>
                                            <input type="tel"
                                                wire:model.live="attendees.{{ $i }}.phone"
                                                placeholder="+968 00000000"
                                                {{ $copyContactToAll && $i > 0 ? 'readonly' : '' }}
                                                class="w-full px-4 py-2.5 border rounded-xl text-gray-900 placeholder-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition
                                                    {{ $copyContactToAll && $i > 0 ? 'bg-gray-50 text-gray-500 cursor-not-allowed' : '' }}
                                                    {{ $errors->has("attendees.$i.phone") ? 'border-red-400 bg-red-50 ring-1 ring-red-300' : 'border-gray-300' }}">
                                            @error("attendees.$i.phone")
                                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    @endif

                                    {{-- Optional fields --}}
                                    @if ($showDateOfBirth || $showGender || $showNationality)
                                        @php $optCount = collect([$showDateOfBirth, $showGender, $showNationality])->filter()->count(); @endphp
                                        <div class="grid grid-cols-1 sm:grid-cols-{{ $optCount }} gap-4">
                                            @if ($showDateOfBirth)
                                                <div>
                                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('event_booking.step5.date_of_birth') }}</label>
                                                    <input type="date"
                                                        wire:model="attendees.{{ $i }}.date_of_birth"
                                                        min="{{ $minBirthDate }}"
                                                        max="{{ $maxBirthDate }}"
                                                        class="w-full px-4 py-2.5 border rounded-xl text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition
                                                            {{ $errors->has("attendees.$i.date_of_birth") ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                                                    @error("attendees.$i.date_of_birth")
                                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                            @endif
                                            @if ($showGender)
                                                <div>
                                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('event_booking.step5.gender') }}</label>
                                                    <select wire:model="attendees.{{ $i }}.gender"
                                                        class="w-full px-4 py-2.5 border rounded-xl text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition
                                                            {{ $errors->has("attendees.$i.gender") ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                                                        <option value="">{{ __('event_booking.step5.select_gender') }}</option>
                                                        <option value="male">{{ __('event_booking.step5.male') }}</option>
                                                        <option value="female">{{ __('event_booking.step5.female') }}</option>
                                                    </select>
                                                    @error("attendees.$i.gender")
                                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                            @endif
                                            @if ($showNationality)
                                                <div class="relative"
                                                    x-data="{
                                                        open: false,
                                                        search: '',
                                                        options: @js(__('booking.options.nationality')),
                                                        get filtered() {
                                                            if (!this.search.trim()) return this.options;
                                                            const term = this.search.toLowerCase();
                                                            return Object.fromEntries(Object.entries(this.options).filter(([code, label]) => label.toLowerCase().includes(term)));
                                                        },
                                                        choose(code, label) {
                                                            this.search = label;
                                                            this.open = false;
                                                            $wire.set('attendees.{{ $i }}.nationality', code);
                                                        },
                                                    }"
                                                    x-init="search = options[$wire.attendees[{{ $i }}].nationality] ?? ''">
                                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('event_booking.step5.nationality') }}</label>
                                                    <input type="text"
                                                        x-model="search"
                                                        @focus="open = true"
                                                        @input="open = true"
                                                        @keydown.escape="open = false"
                                                        @click.outside="open = false"
                                                        autocomplete="off"
                                                        placeholder="{{ __('booking.placeholders.nationality') }}"
                                                        class="w-full px-4 py-2.5 border rounded-xl text-gray-900 placeholder-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition
                                                            {{ $errors->has("attendees.$i.nationality") ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                                                    <div x-show="open" x-transition style="display: none;"
                                                        class="absolute z-30 mt-1 w-full max-h-56 overflow-y-auto bg-white border border-gray-200 rounded-xl shadow-lg">
                                                        <template x-for="[code, label] in Object.entries(filtered)" :key="code">
                                                            <div @click="choose(code, label)"
                                                                class="px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 cursor-pointer"
                                                                x-text="label"></div>
                                                        </template>
                                                        <div x-show="Object.keys(filtered).length === 0" class="px-4 py-2 text-sm text-gray-400">
                                                            {{ __('event_booking.step5.nationality_no_results') }}
                                                        </div>
                                                    </div>
                                                    @error("attendees.$i.nationality")
                                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                </div>
                            </div>
                        @endforeach

                        {{-- Terms and Conditions --}}
                        @if ($termsEn || $termsAr)
                            <div class="rounded-xl border border-gray-200 overflow-hidden">
                                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                                    <h4 class="font-semibold text-gray-800 text-sm">{{ __('event_booking.step5.terms_heading') }}</h4>
                                </div>
                                @if ($termsEn)
                                    <div class="px-4 py-3 {{ $termsAr ? 'border-b border-gray-100' : '' }}">
                                        <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">English</p>
                                        <div class="prose prose-sm max-w-none text-gray-600 max-h-48 overflow-y-auto text-sm leading-relaxed" dir="ltr">
                                            {!! $termsEn !!}
                                        </div>
                                    </div>
                                @endif
                                @if ($termsAr)
                                    <div class="px-4 py-3">
                                        <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">عربي</p>
                                        <div class="prose prose-sm max-w-none text-gray-600 max-h-48 overflow-y-auto text-sm leading-relaxed" dir="rtl">
                                            {!! $termsAr !!}
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="flex items-start gap-3">
                                <input type="checkbox" wire:model="agreedToTerms" id="agreedToTerms"
                                    class="mt-0.5 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer shrink-0
                                        {{ $errors->has('agreedToTerms') ? 'border-red-400 ring-1 ring-red-300' : '' }}">
                                <label for="agreedToTerms" class="text-sm text-gray-700 cursor-pointer leading-snug">
                                    {{ __('event_booking.step5.terms_agree') }}
                                    <span class="font-semibold text-gray-900">{{ __('event_booking.step5.terms_heading') }}</span>
                                    <span class="text-red-500 ml-0.5">*</span>
                                </label>
                            </div>
                            @error('agreedToTerms')
                                <p class="text-red-500 text-sm -mt-3">{{ __('event_booking.step5.terms_required') }}</p>
                            @enderror
                        @endif

                        {{-- Nav + Continue to Payment --}}
                        <div class="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                            <button type="button" wire:click="previousStep"
                                class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-gray-600 hover:text-gray-900 font-medium text-sm transition-colors border border-gray-200 rounded-xl hover:bg-gray-50 sm:w-auto">
                                ← {{ __('event_booking.back') }}
                            </button>
                            <button type="submit"
                                class="flex-1 sm:flex-none px-8 py-3 bg-brand text-white font-bold text-base rounded-xl hover:bg-brand-hover transition-colors shadow-md shadow-brand/30 disabled:opacity-60 disabled:cursor-not-allowed"
                                wire:loading.attr="disabled" wire:target="goToPaymentStep">
                                <span wire:loading.remove wire:target="goToPaymentStep">
                                    {{ __('event_booking.step5.continue_to_payment') }} →
                                </span>
                                <span wire:loading wire:target="goToPaymentStep" class="inline-flex items-center gap-2">
                                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                    {{ __('event_booking.step5.validating') }}
                                </span>
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Sticky Summary Sidebar (1/3 width) --}}
                <div class="lg:col-span-1">
                    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden lg:sticky lg:top-24">
                        <div class="bg-gradient-to-r from-brand to-brand-hover text-white px-5 py-4">
                            <h3 class="font-bold text-base">{{ __('event_booking.step5.booking_summary') }}</h3>
                        </div>
                        <div class="p-5 space-y-4 text-sm">

                            <div>
                                <p class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-0.5">{{ __('event_booking.summary.event') }}</p>
                                <p class="font-semibold text-gray-900 leading-tight">{{ $event->getTranslation('title', app()->getLocale()) }}</p>
                            </div>

                            <div class="pt-3 border-t border-gray-100 grid grid-cols-2 gap-3">
                                <div>
                                    <p class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-0.5">{{ __('event_booking.summary.date') }}</p>
                                    <p class="font-medium text-gray-800">{{ \Carbon\Carbon::parse($selectedDate)->format('M d, Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-0.5">{{ __('event_booking.summary.time') }}</p>
                                    <p class="font-medium text-gray-800">{{ $timeSlots->find($selectedSlot)?->getTimeRange() }}</p>
                                </div>
                            </div>



                            {{-- Tickets breakdown --}}
                            <div class="pt-3 border-t border-gray-100">
                                <p class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-2">{{ __('event_booking.summary.tickets') }}</p>
                                @foreach ($ticketTypes as $ticketType)
                                    @php $qty = $ticketQuantities[$ticketType->id] ?? 0; @endphp
                                    @if ($qty > 0)
                                        <div class="flex justify-between items-center text-gray-700 mb-1">
                                            <span class="truncate pr-2">{{ $ticketType->getTranslation('name', app()->getLocale()) }}</span>
                                            <span class="shrink-0 text-gray-400 text-xs">× {{ $qty }}</span>
                                        </div>
                                        <div class="flex justify-between text-gray-500 text-xs mb-2">
                                            <span>OMR{{ number_format($ticketType->price, 3) }} × {{ $qty }}</span>
                                            <span>OMR{{ number_format($ticketType->price * $qty, 3) }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>

                            {{-- Attendees breakdown --}}
                            @if (count($attendees))
                                <div class="pt-3 border-t border-gray-100">
                                    <p class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-2">{{ __('event_booking.summary.attendees') }}</p>
                                    @foreach ($attendees as $attendee)
                                        @php $attendeeTicketType = $ticketTypes->find($attendee['ticket_type_id'] ?? null); @endphp
                                        <div class="flex justify-between items-center text-gray-700 mb-1">
                                            <span class="truncate pr-2">{{ trim(($attendee['first_name'] ?? '') . ' ' . ($attendee['last_name'] ?? '')) ?: '—' }}</span>
                                            <span class="shrink-0 text-gray-400 text-xs truncate max-w-[40%]">{{ $attendeeTicketType?->getTranslation('name', app()->getLocale()) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Services breakdown --}}
                            @php
                                $hasServices = collect($ticketTypeServices)->flatten()->isNotEmpty();
                            @endphp
                            @if ($hasServices)
                                <div class="pt-3 border-t border-gray-100">
                                    <p class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-2">{{ __('event_booking.summary.extra_services') }}</p>
                                    @foreach ($ticketTypes as $ticketType)
                                        @php
                                            $qty        = $ticketQuantities[$ticketType->id] ?? 0;
                                            $serviceIds = $ticketTypeServices[$ticketType->id] ?? [];
                                        @endphp
                                        @if ($qty > 0 && !empty($serviceIds))
                                            <p class="text-xs text-gray-400 mb-1">{{ $ticketType->getTranslation('name', app()->getLocale()) }}</p>
                                            @foreach ($serviceIds as $serviceId)
                                                @php $svc = $extraServices->find($serviceId); @endphp
                                                @if ($svc)
                                                    <div class="flex justify-between text-gray-600 text-xs mb-1">
                                                        <span class="truncate pr-2">{{ $svc->getTranslation('name', app()->getLocale()) }}</span>
                                                        <span class="shrink-0">OMR{{ number_format($svc->price * $qty, 3) }}</span>
                                                    </div>
                                                @endif
                                            @endforeach
                                        @endif
                                    @endforeach
                                </div>
                            @endif

                            <div class="pt-3 border-t-2 border-gray-200 flex justify-between items-baseline">
                                <span class="font-bold text-gray-900 text-base">{{ __('event_booking.summary.total') }}</span>
                                <span class="font-black text-2xl text-blue-600">OMR{{ number_format($totalPrice, 3) }}</span>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        @endif

        {{-- ════════════════════════════
             STEP 6 — Payment
        ════════════════════════════ --}}
        @if ($step === 6)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                {{-- Payment options (2/3 width) --}}
                <div class="lg:col-span-2">
                    <h2 class="text-2xl font-bold text-gray-900 mb-1">{{ __('event_booking.step6.heading') }}</h2>
                    <p class="text-gray-500 mb-6">{{ __('event_booking.step6.subheading') }}</p>

                    {{-- Gateway card --}}
                    @if ($activeGateway === 'thawani')
                        <div class="p-6 border-2 border-blue-500 bg-blue-50 rounded-2xl shadow-md shadow-blue-100 mb-6">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-sm border border-blue-100 shrink-0">
                                    {{-- Thawani icon placeholder --}}
                                    <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900 text-lg">{{ __('event_booking.step6.thawani_title') }}</p>
                                    <p class="text-sm text-gray-500">{{ __('event_booking.step6.thawani_subtitle') }}</p>
                                </div>
                                <div class="ml-auto">
                                    <span class="inline-block w-3 h-3 rounded-full bg-blue-500 ring-4 ring-blue-200"></span>
                                </div>
                            </div>
                            <p class="mt-4 text-sm text-blue-700 bg-blue-100 rounded-lg px-3 py-2">
                                {{ __('event_booking.step6.thawani_redirect_note') }}
                            </p>
                        </div>

                    @elseif ($activeGateway === 'nbo')
                        <div class="p-6 border-2 border-indigo-500 bg-indigo-50 rounded-2xl shadow-md shadow-indigo-100 mb-6">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-sm border border-indigo-100 shrink-0">
                                    <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900 text-lg">{{ __('event_booking.step6.nbo_title') }}</p>
                                    <p class="text-sm text-gray-500">{{ __('event_booking.step6.nbo_subtitle') }}</p>
                                </div>
                                <div class="ml-auto">
                                    <span class="inline-block w-3 h-3 rounded-full bg-indigo-500 ring-4 ring-indigo-200"></span>
                                </div>
                            </div>
                            <p class="mt-4 text-sm text-indigo-700 bg-indigo-100 rounded-lg px-3 py-2">
                                {{ __('event_booking.step6.nbo_redirect_note') }}
                            </p>
                        </div>

                    @elseif ($activeGateway === 'cash')
                        <div class="p-6 border-2 border-amber-400 bg-amber-50 rounded-2xl mb-6">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-sm border border-amber-100 shrink-0">
                                    <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900 text-lg">{{ __('event_booking.step6.pay_at_door_title') }}</p>
                                    <p class="text-sm text-gray-500">{{ __('event_booking.step6.pay_at_door_subtitle') }}</p>
                                </div>
                            </div>
                        </div>

                    @else
                        {{-- Free / no payment required --}}
                        <div class="p-6 border-2 border-green-400 bg-green-50 rounded-2xl mb-6">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-sm border border-green-100 shrink-0">
                                    <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900 text-lg">{{ __('event_booking.step6.free_title') }}</p>
                                    <p class="text-sm text-gray-500">{{ __('event_booking.step6.free_subtitle') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Nav + Confirm --}}
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <button type="button" wire:click="previousStep"
                            class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-gray-600 hover:text-gray-900 font-medium text-sm transition-colors border border-gray-200 rounded-xl hover:bg-gray-50 sm:w-auto">
                            ← {{ __('event_booking.back') }}
                        </button>

                        @if ($activeGateway === 'thawani')
                            <button type="button" wire:click="submitBooking"
                                class="flex-1 sm:flex-none px-8 py-3 bg-blue-600 text-white font-bold text-base rounded-xl hover:bg-blue-700 transition-colors shadow-md shadow-blue-200 disabled:opacity-60 disabled:cursor-not-allowed"
                                wire:loading.attr="disabled" wire:target="submitBooking">
                                <span wire:loading.remove wire:target="submitBooking">
                                    🔒 {{ __('event_booking.step6.pay_thawani_btn') }}
                                </span>
                                <span wire:loading wire:target="submitBooking" class="inline-flex items-center gap-2">
                                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                    {{ __('event_booking.step6.redirecting_thawani') }}
                                </span>
                            </button>
                        @elseif ($activeGateway === 'nbo')
                            <button type="button" wire:click="submitBooking"
                                class="flex-1 sm:flex-none px-8 py-3 bg-indigo-600 text-white font-bold text-base rounded-xl hover:bg-indigo-700 transition-colors shadow-md shadow-indigo-200 disabled:opacity-60 disabled:cursor-not-allowed"
                                wire:loading.attr="disabled" wire:target="submitBooking">
                                <span wire:loading.remove wire:target="submitBooking">
                                    🔒 {{ __('event_booking.step6.pay_nbo_btn') }}
                                </span>
                                <span wire:loading wire:target="submitBooking" class="inline-flex items-center gap-2">
                                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                    {{ __('event_booking.step6.redirecting_nbo') }}
                                </span>
                            </button>
                        @else
                            <button type="button" wire:click="submitBooking"
                                class="flex-1 sm:flex-none px-8 py-3 bg-green-600 text-white font-bold text-base rounded-xl hover:bg-green-700 transition-colors shadow-md shadow-green-200 disabled:opacity-60 disabled:cursor-not-allowed"
                                wire:loading.attr="disabled" wire:target="submitBooking">
                                <span wire:loading.remove wire:target="submitBooking">
                                    ✓ {{ __('event_booking.step6.confirm_booking') }}
                                </span>
                                <span wire:loading wire:target="submitBooking" class="inline-flex items-center gap-2">
                                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                    {{ __('event_booking.step6.processing') }}
                                </span>
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Sticky Summary Sidebar (1/3 width) --}}
                <div class="lg:col-span-1">
                    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden lg:sticky lg:top-24">
                        <div class="bg-gradient-to-r from-brand to-brand-hover text-white px-5 py-4">
                            <h3 class="font-bold text-base">{{ __('event_booking.step6.order_summary') }}</h3>
                        </div>
                        <div class="p-5 space-y-4 text-sm">

                            <div>
                                <p class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-0.5">{{ __('event_booking.summary.event') }}</p>
                                <p class="font-semibold text-gray-900 leading-tight">{{ $event->getTranslation('title', app()->getLocale()) }}</p>
                            </div>

                            <div class="pt-3 border-t border-gray-100 grid grid-cols-2 gap-3">
                                <div>
                                    <p class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-0.5">{{ __('event_booking.summary.date') }}</p>
                                    <p class="font-medium text-gray-800">{{ \Carbon\Carbon::parse($selectedDate)->format('M d, Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-0.5">{{ __('event_booking.summary.time') }}</p>
                                    <p class="font-medium text-gray-800">{{ $timeSlots->find($selectedSlot)?->getTimeRange() }}</p>
                                </div>
                            </div>

                            {{-- Tickets breakdown --}}
                            <div class="pt-3 border-t border-gray-100">
                                <p class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-2">{{ __('event_booking.summary.tickets') }}</p>
                                @foreach ($ticketTypes as $ticketType)
                                    @php $qty = $ticketQuantities[$ticketType->id] ?? 0; @endphp
                                    @if ($qty > 0)
                                        <div class="flex justify-between text-gray-600 text-xs mb-1">
                                            <span class="truncate pr-2">{{ $ticketType->getTranslation('name', app()->getLocale()) }} × {{ $qty }}</span>
                                            <span class="shrink-0">OMR{{ number_format($ticketType->price * $qty, 3) }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>

                            {{-- Attendees breakdown --}}
                            @if (count($attendees))
                                <div class="pt-3 border-t border-gray-100">
                                    <p class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-2">{{ __('event_booking.summary.attendees') }}</p>
                                    @foreach ($attendees as $attendee)
                                        @php $attendeeTicketType = $ticketTypes->find($attendee['ticket_type_id'] ?? null); @endphp
                                        <div class="flex justify-between items-center text-gray-700 mb-1">
                                            <span class="truncate pr-2">{{ trim(($attendee['first_name'] ?? '') . ' ' . ($attendee['last_name'] ?? '')) ?: '—' }}</span>
                                            <span class="shrink-0 text-gray-400 text-xs truncate max-w-[40%]">{{ $attendeeTicketType?->getTranslation('name', app()->getLocale()) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <div class="pt-3 border-t-2 border-gray-200 flex justify-between items-baseline">
                                <span class="font-bold text-gray-900 text-base">{{ __('event_booking.summary.total') }}</span>
                                <span class="font-black text-2xl text-blue-600">OMR{{ number_format($totalPrice, 3) }}</span>
                            </div>

                            @if ($activeGateway === 'thawani')
                                <div class="pt-2 text-center text-xs text-gray-400">
                                    <svg class="inline w-4 h-4 mr-1 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ __('event_booking.step6.secured_by_thawani') }}
                                </div>
                            @elseif ($activeGateway === 'nbo')
                                <div class="pt-2 text-center text-xs text-gray-400">
                                    <svg class="inline w-4 h-4 mr-1 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ __('event_booking.step6.secured_by_nbo') }}
                                </div>
                            @endif

                        </div>
                    </div>
                </div>

            </div>
        @endif

    </div>
</div>
