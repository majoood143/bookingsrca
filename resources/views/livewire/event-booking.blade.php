<div dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
@if (in_array($event->status, ['draft', 'cancelled']))
    {{-- ── Maintenance / Unavailable View ── --}}
    <div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
        <div class="max-w-lg w-full text-center py-12">
            <div class="flex justify-end mb-6">
                @if (app()->getLocale() === 'ar')
                    <a href="{{ route('lang.switch', 'en') }}"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-full transition-colors">
                        🌐 {{ __('event_booking.switch_to_english') }}
                    </a>
                @else
                    <a href="{{ route('lang.switch', 'ar') }}"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-full transition-colors">
                        🌐 {{ __('event_booking.switch_to_arabic') }}
                    </a>
                @endif
            </div>

            <img src="{{ asset('storage/images/horizontalLogo-03.svg') }}" alt="Logo"
                class="h-20 w-auto mx-auto mb-8">

            <div class="text-6xl mb-4">🛠️</div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3">
                {{ __('event_booking.unavailable.heading') }}
            </h1>
            <p class="text-gray-500 text-base leading-relaxed">
                {{ $event->status === 'cancelled'
                    ? __('event_booking.unavailable.cancelled_message')
                    : __('event_booking.unavailable.draft_message') }}
            </p>

            <a href="{{ url('/') }}"
                class="mt-8 inline-flex items-center gap-1.5 px-6 py-2.5 bg-brand text-white font-semibold rounded-xl hover:bg-brand-hover transition-colors shadow-sm">
                {{ __('event_booking.unavailable.back_home') }}
            </a>
        </div>
    </div>
@elseif ($passwordRequired)
    @push('meta')
        <meta name="robots" content="noindex, nofollow">
    @endpush
    {{-- ── Private Event / Password Prompt ── --}}
    <div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
        <div class="max-w-md w-full text-center py-12">
            <div class="flex justify-end mb-6">
                @if (app()->getLocale() === 'ar')
                    <a href="{{ route('lang.switch', 'en') }}"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-full transition-colors">
                        🌐 {{ __('event_booking.switch_to_english') }}
                    </a>
                @else
                    <a href="{{ route('lang.switch', 'ar') }}"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-full transition-colors">
                        🌐 {{ __('event_booking.switch_to_arabic') }}
                    </a>
                @endif
            </div>

            <img src="{{ asset('storage/images/horizontalLogo-03.svg') }}" alt="Logo"
                class="h-20 w-auto mx-auto mb-8">

            <div class="text-6xl mb-4">🔒</div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3">
                {{ __('event_booking.private.heading') }}
            </h1>
            <p class="text-gray-500 text-base leading-relaxed mb-6">
                {{ __('event_booking.private.message') }}
            </p>

            <form wire:submit.prevent="submitEventPassword" class="text-start">
                <label for="eventPasswordInput" class="sr-only">
                    {{ __('event_booking.private.password_label') }}
                </label>
                <input
                    type="password"
                    id="eventPasswordInput"
                    wire:model="eventPasswordInput"
                    placeholder="{{ __('event_booking.private.password_placeholder') }}"
                    autofocus
                    class="w-full rounded-xl border-gray-300 focus:border-brand focus:ring-brand shadow-sm px-4 py-3"
                >
                @error('eventPasswordInput')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
                @if ($eventPasswordError)
                    <p class="mt-2 text-sm text-red-600">{{ $eventPasswordError }}</p>
                @endif

                <button type="submit"
                    class="mt-4 w-full inline-flex items-center justify-center gap-1.5 px-6 py-3 bg-brand text-white font-semibold rounded-xl hover:bg-brand-hover transition-colors shadow-sm">
                    {{ __('event_booking.private.submit') }}
                </button>
            </form>

            <a href="{{ url('/') }}"
                class="mt-6 inline-block text-sm text-gray-400 hover:text-gray-600 transition-colors">
                {{ __('event_booking.unavailable.back_home') }}
            </a>
        </div>
    </div>
@else
    <div class="min-h-screen bg-gray-50">

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

            <div class="flex items-center justify-between gap-4">
                <h1 class="text-2xl sm:text-3xl font-bold leading-tight">
                    {{ $event->getTranslation('title', app()->getLocale()) }}
                </h1>
                <img src="{{ asset('storage/images/horizontalLogo-03.svg') }}" alt="Logo"
                    class="h-40 sm:h-40 w-auto shrink-0">
            </div>
            <p class="mt-2 text-blue-100 text-base sm:text-lg line-clamp-4">
                {{ $event->getTranslation('description', app()->getLocale()) }}
            </p>
            <div class="mt-3 flex flex-wrap gap-x-5 gap-y-1 text-sm text-blue-200">
                <span>📍 {{ $event->getTranslation('location', app()->getLocale()) }}</span>
                {{-- <span>📅 {{ $event->start_date->locale(app()->getLocale())->translatedFormat('M d') }} –
                    {{ $event->end_date->locale(app()->getLocale())->translatedFormat('M d, Y') }}</span> --}}
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
                    $steps = [
                        1 => __('event_booking.steps.date'),
                        2 => __('event_booking.steps.time'),
                        3 => __('event_booking.steps.tickets'),
                    ];
                    if ($extraServices->isNotEmpty()) {
                        $steps[4] = __('event_booking.steps.extras');
                    }
                    $steps[5] = __('event_booking.steps.details');
                    $steps[6] = __('event_booking.steps.payment');
                @endphp
                @foreach ($steps as $num => $label)
                    <div class="flex items-center {{ $num < 6 ? 'flex-1' : '' }}">
                        <div class="flex items-center gap-2 shrink-0">
                            <div
                                class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-colors
                                {{ $step > $num
                                    ? 'bg-green-500 text-white'
                                    : ($step === $num
                                        ? 'bg-blue-600 text-white ring-4 ring-blue-100'
                                        : 'bg-gray-200 text-gray-500') }}">
                                @if ($step > $num)
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                @else
                                    {{ $num }}
                                @endif
                            </div>
                            <span
                                class="hidden sm:block text-sm font-medium
                                {{ $step >= $num ? 'text-gray-800' : 'text-gray-400' }}">
                                {{ $label }}
                            </span>
                        </div>
                        @if ($num < 6)
                            <div
                                class="flex-1 h-0.5 mx-2 sm:mx-3 transition-colors
                                {{ $step > $num ? 'bg-green-400' : 'bg-gray-200' }}">
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── Main Content ── --}}
    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-8">

        @include('livewire.partials.booking-wizard-steps')

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
                        <div
                            class="p-6 border-2 border-blue-500 bg-blue-50 rounded-2xl shadow-md shadow-blue-100 mb-6">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-sm border border-blue-100 shrink-0">
                                    {{-- Thawani icon placeholder --}}
                                    <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900 text-lg">
                                        {{ __('event_booking.step6.thawani_title') }}</p>
                                    <p class="text-sm text-gray-500">{{ __('event_booking.step6.thawani_subtitle') }}
                                    </p>
                                </div>
                                <div class="ml-auto">
                                    <span
                                        class="inline-block w-3 h-3 rounded-full bg-blue-500 ring-4 ring-blue-200"></span>
                                </div>
                            </div>
                            <p class="mt-4 text-sm text-blue-700 bg-blue-100 rounded-lg px-3 py-2">
                                {{ __('event_booking.step6.thawani_redirect_note') }}
                            </p>
                        </div>
                    @elseif ($activeGateway === 'nbo')
                        <div
                            class="p-6 border-2 border-indigo-500 bg-indigo-50 rounded-2xl shadow-md shadow-indigo-100 mb-6">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-sm border border-indigo-100 shrink-0">
                                    <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900 text-lg">
                                        {{ __('event_booking.step6.nbo_title') }}</p>
                                    <p class="text-sm text-gray-500">{{ __('event_booking.step6.nbo_subtitle') }}</p>
                                </div>
                                <div class="ml-auto">
                                    <span
                                        class="inline-block w-3 h-3 rounded-full bg-indigo-500 ring-4 ring-indigo-200"></span>
                                </div>
                            </div>
                            <p class="mt-4 text-sm text-indigo-700 bg-indigo-100 rounded-lg px-3 py-2">
                                {{ __('event_booking.step6.nbo_redirect_note') }}
                            </p>
                        </div>
                    @elseif ($activeGateway === 'cash')
                        <div class="p-6 border-2 border-amber-400 bg-amber-50 rounded-2xl mb-6">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-sm border border-amber-100 shrink-0">
                                    <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900 text-lg">
                                        {{ __('event_booking.step6.pay_at_door_title') }}</p>
                                    <p class="text-sm text-gray-500">
                                        {{ __('event_booking.step6.pay_at_door_subtitle') }}</p>
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- Free / no payment required --}}
                        <div class="p-6 border-2 border-green-400 bg-green-50 rounded-2xl mb-6">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-sm border border-green-100 shrink-0">
                                    <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900 text-lg">
                                        {{ __('event_booking.step6.free_title') }}</p>
                                    <p class="text-sm text-gray-500">{{ __('event_booking.step6.free_subtitle') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="flex items-start gap-3">
                        <input type="checkbox" wire:model="agreedToTerms" id="agreedToTerms1"
                            class="mt-0.5 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer shrink-0
                                        {{ $errors->has('agreedToTerms') ? 'border-red-400 ring-1 ring-red-300' : '' }}">
                        <label for="agreedToTerms" class="text-sm text-gray-700 cursor-pointer leading-snug">
                            {{ __('event_booking.step5.terms_agree') }}
                            <span
                                class="font-semibold text-gray-900">{{ __('event_booking.step5.terms_heading') }}</span>
                            <span class="text-red-500 ml-0.5">*</span>
                        </label>
                    </div>
                    @error('agreedToTerms1')
                        <p class="text-red-500 text-sm -mt-3">{{ __('event_booking.step5.terms_required') }}</p>
                    @enderror
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
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
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
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
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
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                    </svg>
                                    {{ __('event_booking.step6.processing') }}
                                </span>
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Sticky Summary Sidebar (1/3 width) --}}
                <div class="lg:col-span-1">
                    <div
                        class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden lg:sticky lg:top-24">
                        <div class="bg-gradient-to-r from-brand to-brand-hover text-white px-5 py-4">
                            <h3 class="font-bold text-base">{{ __('event_booking.step6.order_summary') }}</h3>
                        </div>
                        <div class="p-5 space-y-4 text-sm">

                            <div>
                                <p class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-0.5">
                                    {{ __('event_booking.summary.event') }}</p>
                                <p class="font-semibold text-gray-900 leading-tight">
                                    {{ $event->getTranslation('title', app()->getLocale()) }}</p>
                            </div>

                            <div class="pt-3 border-t border-gray-100 grid grid-cols-2 gap-3">
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
                                        {{ $timeSlots->find($selectedSlot)?->getTimeRange() }}</p>
                                </div>
                            </div>

                            {{-- Tickets breakdown --}}
                            <div class="pt-3 border-t border-gray-100">
                                <p class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-2">
                                    {{ __('event_booking.summary.tickets') }}</p>
                                @foreach ($ticketTypes as $ticketType)
                                    @php $qty = $ticketQuantities[$ticketType->id] ?? 0; @endphp
                                    @if ($qty > 0)
                                        <div class="flex justify-between text-gray-600 text-xs mb-1">
                                            <span
                                                class="truncate pr-2">{{ $ticketType->getTranslation('name', app()->getLocale()) }}
                                                × {{ $qty }}</span>
                                            <span
                                                class="shrink-0">{{ $ticketType->price > 0 ? 'OMR' . number_format($ticketType->price * $qty, 3) : __('event_booking.step3.free') }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>

                            {{-- Attendees breakdown --}}
                            @if (count($attendees))
                                <div class="pt-3 border-t border-gray-100">
                                    <p class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-2">
                                        {{ __('event_booking.summary.attendees') }}</p>
                                    @foreach ($attendees as $attendee)
                                        @php $attendeeTicketType = $ticketTypes->find($attendee['ticket_type_id'] ?? null); @endphp
                                        <div class="flex justify-between items-center text-gray-700 mb-1">
                                            <span
                                                class="truncate pr-2">{{ trim(($attendee['first_name'] ?? '') . ' ' . ($attendee['last_name'] ?? '')) ?: '—' }}</span>
                                            <span
                                                class="shrink-0 text-gray-400 text-xs truncate max-w-[40%]">{{ $attendeeTicketType?->getTranslation('name', app()->getLocale()) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @php
                                $hasServices = collect($ticketTypeServices)->flatten()->sum() > 0;
                            @endphp
                            @if ($hasServices)
                                <div class="pt-3 border-t border-gray-100">
                                    <p class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-2">
                                        {{ __('event_booking.summary.extra_services') }}</p>
                                    @foreach ($ticketTypes as $ticketType)
                                        @php
                                            $qty = $ticketQuantities[$ticketType->id] ?? 0;
                                            $serviceCounts = $ticketTypeServices[$ticketType->id] ?? [];
                                        @endphp
                                        @if ($qty > 0 && !empty($serviceCounts))
                                            <p class="text-xs text-gray-400 mb-1">
                                                {{ $ticketType->getTranslation('name', app()->getLocale()) }}</p>
                                            @foreach ($serviceCounts as $serviceId => $count)
                                                @php $svc = $count > 0 ? $extraServices->find($serviceId) : null; @endphp
                                                @if ($svc)
                                                    <div class="flex justify-between text-gray-600 text-xs mb-1">
                                                        <span
                                                            class="truncate pr-2">{{ $svc->getTranslation('name', app()->getLocale()) }}
                                                            × {{ $count }}</span>
                                                        <span
                                                            class="shrink-0">OMR{{ number_format($svc->price * $count, 3) }}</span>
                                                    </div>
                                                @endif
                                            @endforeach
                                        @endif
                                    @endforeach
                                </div>
                            @endif

                            <div class="pt-3 border-t-2 border-gray-200 flex justify-between items-baseline">
                                <span
                                    class="font-bold text-gray-900 text-base">{{ __('event_booking.summary.total') }}</span>
                                <span
                                    class="font-black text-2xl text-blue-600">OMR{{ number_format($totalPrice, 3) }}</span>
                            </div>

                            @if ($activeGateway === 'thawani')
                                <div class="pt-2 text-center text-xs text-gray-400">
                                    <svg class="inline w-4 h-4 mr-1 text-green-500" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    {{ __('event_booking.step6.secured_by_thawani') }}
                                </div>
                            @elseif ($activeGateway === 'nbo')
                                <div class="pt-2 text-center text-xs text-gray-400">
                                    <svg class="inline w-4 h-4 mr-1 text-green-500" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                            clip-rule="evenodd" />
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
@endif
</div>
