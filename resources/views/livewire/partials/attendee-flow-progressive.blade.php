{{-- Kiosk-only progressive attendee flow: one guest at a time, with a
     "reuse attendee 1's details for everyone?" decision to skip the rest
     when it's a group booking. See KioskBooking::confirmAttendee() etc. --}}
@php
    $totalAttendees = count($attendees);
@endphp
<div class="max-w-2xl mx-auto">
    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-1">{{ __('event_booking.step5.heading') }}</h2>
        <p class="text-gray-500">{{ __('event_booking.step5.subheading') }}</p>
    </div>

    {{-- Attendee tracker — every confirmed guest becomes a visible checkmark
         (a small, satisfying sense of progress that pulls the customer
         toward finishing the last one). --}}
    @if ($totalAttendees > 1)
        <div class="flex items-center justify-center mb-8">
            @foreach ($attendees as $i => $a)
                @php
                    $isDone = $i < $focusedAttendee;
                    $isCurrent = $i === $focusedAttendee;
                @endphp
                <div class="flex items-center {{ !$loop->last ? 'flex-1' : '' }}">
                    <button type="button" wire:click="editAttendee({{ $i }})"
                        {{ $i > $focusedAttendee ? 'disabled' : '' }}
                        class="kiosk-tracker-dot w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm shrink-0 transition-all duration-300
                            {{ $isDone ? 'bg-brand text-white' : ($isCurrent ? 'bg-brand-hover text-white ring-4 ring-brand-hover/25 scale-110' : 'bg-gray-100 text-gray-400 cursor-default') }}">
                        @if ($isDone)
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                            </svg>
                        @else
                            {{ $i + 1 }}
                        @endif
                    </button>
                    @if (!$loop->last)
                        <div class="flex-1 h-0.5 mx-1 transition-colors duration-300 {{ $i < $focusedAttendee ? 'bg-brand' : 'bg-gray-100' }}"></div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-center">{{ session('error') }}</div>
    @endif

    @if ($focusedAttendee === 0)
        {{-- Attendee 1's form — always filled in individually; it's the one
             whose details might be reused for everyone else. --}}
        <div wire:key="attendee-card-0" class="kiosk-attendee-card border-2 border-brand-hover/30 rounded-3xl overflow-hidden bg-white shadow-sm">
            <div class="px-6 py-4 bg-brand-hover/10 border-b border-brand-hover/30">
                <span class="font-bold text-gray-800">{{ __('event_booking.step5.attendee_n', ['n' => 1]) }}</span>
                <span class="text-xs text-brand-hover font-normal ml-1">({{ __('event_booking.step5.primary') }})</span>
            </div>
            <div class="p-6 space-y-4">
                @include('livewire.partials.attendee-fields', ['i' => 0, 'attendee' => $attendees[0]])
            </div>
        </div>

        <div class="mt-6 flex flex-col sm:flex-row items-stretch sm:items-center sm:justify-between gap-3">
            <button type="button" wire:click="previousStep"
                class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-gray-600 hover:text-gray-900 font-medium text-sm border border-gray-200 rounded-xl hover:bg-gray-50">
                ← {{ __('event_booking.back') }}
            </button>
            <button type="button" wire:click="confirmAttendee(0)" wire:loading.attr="disabled" wire:target="confirmAttendee"
                class="kiosk-continue-btn flex-1 sm:flex-none px-8 py-3 bg-brand text-white font-bold text-base rounded-xl hover:bg-brand-hover transition-colors shadow-md">
                {{ __('event_booking.continue') }} →
            </button>
        </div>
    @elseif ($totalAttendees > 1 && $applySameToAll === null)
        {{-- Decision: reuse attendee 1's details, or fill each guest in? A small
             commitment (one tap) that saves the biggest source of friction in a
             group kiosk booking — retyping the same details N times. --}}
        <div wire:key="apply-all-decision" class="kiosk-decision-card text-center p-8 sm:p-10 border-2 border-dashed border-brand-hover/40 rounded-3xl bg-brand-hover/5">
            <div class="text-5xl mb-4">✨</div>
            <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">
                {{ __('event_booking.step5.reuse_prompt_heading', ['name' => trim(($attendees[0]['first_name'] ?? '') . ' ' . ($attendees[0]['last_name'] ?? '')) ?: __('event_booking.step5.attendee_n', ['n' => 1])]) }}
            </h3>
            <p class="text-gray-500 mb-8">{{ __('event_booking.step5.reuse_prompt_body', ['n' => $totalAttendees - 1]) }}</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <button type="button" wire:click="applySameDetailsToAll" wire:loading.attr="disabled" wire:target="applySameDetailsToAll"
                    class="kiosk-decision-btn px-6 py-5 bg-brand text-white font-bold text-lg rounded-2xl hover:bg-brand-hover transition-colors shadow-md">
                    {{ __('event_booking.step5.reuse_yes') }}
                </button>
                <button type="button" wire:click="declineApplySameToAll" wire:loading.attr="disabled" wire:target="declineApplySameToAll"
                    class="kiosk-decision-btn px-6 py-5 bg-white border-2 border-gray-200 text-gray-700 font-bold text-lg rounded-2xl hover:border-gray-300 transition-colors">
                    {{ __('event_booking.step5.reuse_no') }}
                </button>
            </div>
        </div>
    @elseif ($focusedAttendee < $totalAttendees)
        {{-- Individual walk-through for attendee $focusedAttendee (>= 1). --}}
        @php $i = $focusedAttendee; @endphp
        <div wire:key="attendee-card-{{ $i }}" class="kiosk-attendee-card border-2 border-brand-hover/30 rounded-3xl overflow-hidden bg-white shadow-sm">
            <div class="px-6 py-4 bg-brand-hover/10 border-b border-brand-hover/30 flex items-center justify-between">
                <span class="font-bold text-gray-800">{{ __('event_booking.step5.attendee_n', ['n' => $i + 1]) }}</span>
                <span class="text-xs text-gray-400">{{ $i + 1 }} / {{ $totalAttendees }}</span>
            </div>
            <div class="p-6 space-y-4">
                @include('livewire.partials.attendee-fields', ['i' => $i, 'attendee' => $attendees[$i]])
            </div>
        </div>

        <div class="mt-6 flex flex-col sm:flex-row items-stretch sm:items-center sm:justify-between gap-3">
            <button type="button" wire:click="editAttendee({{ max(0, $i - 1) }})"
                class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-gray-600 hover:text-gray-900 font-medium text-sm border border-gray-200 rounded-xl hover:bg-gray-50">
                ← {{ __('event_booking.back') }}
            </button>
            <button type="button" wire:click="confirmAttendee({{ $i }})" wire:loading.attr="disabled" wire:target="confirmAttendee"
                class="kiosk-continue-btn flex-1 sm:flex-none px-8 py-3 bg-brand text-white font-bold text-base rounded-xl hover:bg-brand-hover transition-colors shadow-md">
                {{ $i === $totalAttendees - 1 ? __('event_booking.step5.continue_to_terms') : __('event_booking.continue') }} →
            </button>
        </div>
    @else
        {{-- Every attendee confirmed (individually, or via reuse) — recap + terms + submit. --}}
        <div wire:key="attendees-summary" class="mb-6 space-y-2">
            @foreach ($attendees as $i => $a)
                <div class="flex items-center gap-3 p-3 bg-white border border-gray-200 rounded-xl">
                    <div class="w-7 h-7 rounded-full bg-brand text-white flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <span class="font-semibold text-gray-800 truncate">
                        {{ trim(($a['first_name'] ?? '') . ' ' . ($a['last_name'] ?? '')) ?: __('event_booking.step5.attendee_n', ['n' => $i + 1]) }}
                    </span>
                    <button type="button" wire:click="editAttendee({{ $i }})" class="ml-auto text-xs text-brand-hover font-semibold hover:underline">
                        {{ __('event_booking.step5.edit') }}
                    </button>
                </div>
            @endforeach
        </div>

        <form wire:submit.prevent="goToPaymentStep" class="space-y-6 kiosk-attendee-card" novalidate>
            @if ($termsEn || $termsAr)
                <div class="rounded-xl border border-gray-200 overflow-hidden" x-data="{ open: false }">
                    <button type="button"
                        class="w-full flex items-center justify-between bg-gray-50 px-4 py-3 text-left focus:outline-none"
                        @click="open = !open" :aria-expanded="open">
                        <h4 class="font-semibold text-gray-800 text-sm">
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
                        class="border-t border-gray-200 overflow-hidden">
                        @if (app()->getLocale() === 'ar' && $termsAr)
                            <div class="px-4 py-3">
                                <div class="prose prose-sm max-w-none text-gray-600 max-h-48 overflow-y-auto text-sm leading-relaxed" dir="rtl">
                                    {!! $termsAr !!}
                                </div>
                            </div>
                        @elseif ($termsEn)
                            <div class="px-4 py-3">
                                <div class="prose prose-sm max-w-none text-gray-600 max-h-48 overflow-y-auto text-sm leading-relaxed" dir="ltr">
                                    {!! $termsEn !!}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <input type="checkbox" wire:model="agreedToTerms" id="agreedToTermsProgressive"
                        class="mt-0.5 h-4 w-4 rounded border-gray-300 text-brand-hover focus:ring-brand-hover cursor-pointer shrink-0
                            {{ $errors->has('agreedToTerms') ? 'border-red-400 ring-1 ring-red-300' : '' }}">
                    <label for="agreedToTermsProgressive" class="text-sm text-gray-700 cursor-pointer leading-snug">
                        {{ __('event_booking.step5.terms_agree') }}
                        <a href="{{ app()->getLocale() === 'ar' ? 'https://razatfarm.gov.om/terms-of-use/' : 'https://razatfarm.gov.om/en/terms-of-use/' }}"
                            target="_blank" rel="noopener noreferrer"
                            class="font-semibold text-gray-900 underline hover:text-brand">{{ __('event_booking.step5.terms_heading') }}</a>
                        <span class="text-red-500 ml-0.5">*</span>
                    </label>
                </div>
                @error('agreedToTerms')
                    <p class="text-red-500 text-sm -mt-3">{{ __('event_booking.step5.terms_required') }}</p>
                @enderror
            @endif

            <div class="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center sm:justify-between gap-3">
                <button type="button" wire:click="editAttendee({{ max(0, $totalAttendees - 1) }})"
                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-gray-600 hover:text-gray-900 font-medium text-sm border border-gray-200 rounded-xl hover:bg-gray-50">
                    ← {{ __('event_booking.back') }}
                </button>
                <button type="submit"
                    class="kiosk-continue-btn flex-1 sm:flex-none px-8 py-3 bg-brand text-white font-bold text-base rounded-xl hover:bg-brand-hover transition-colors shadow-md shadow-brand/30 disabled:opacity-60 disabled:cursor-not-allowed"
                    wire:loading.attr="disabled" wire:target="goToPaymentStep">
                    <span wire:loading.remove wire:target="goToPaymentStep">
                        {{ __('event_booking.step5.continue_to_payment') }} →
                    </span>
                    <span wire:loading wire:target="goToPaymentStep" class="inline-flex items-center gap-2">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>
                        {{ __('event_booking.step5.validating') }}
                    </span>
                </button>
            </div>
        </form>
    @endif
</div>
