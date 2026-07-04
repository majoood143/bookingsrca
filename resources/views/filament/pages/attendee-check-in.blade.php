<x-filament-panels::page>
    @php
        $locale = app()->getLocale();
        $statusColors = [
            'pending' => '#d97706',
            'confirmed' => '#16a34a',
            'cancelled' => '#dc2626',
            'checked_in' => '#4f46e5',
        ];
    @endphp

    <div
        x-data="{
            fullscreen: false,
            toggleFullscreen() {
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen?.();
                    this.fullscreen = true;
                } else {
                    document.exitFullscreen?.();
                    this.fullscreen = false;
                }
            }
        }"
        x-on:fullscreenchange.window="fullscreen = !!document.fullscreenElement"
        class="space-y-4"
    >
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-lg font-bold">{{ __('attendee_check_in.title') }}</h2>
            <button
                type="button"
                x-on:click="toggleFullscreen()"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-md text-sm font-medium text-white shrink-0 transition-colors duration-150"
                style="background-color: #4f46e5; min-height: 44px;"
                onmouseover="this.style.backgroundColor='#4338ca'"
                onmouseout="this.style.backgroundColor='#4f46e5'"
            >
                <span x-show="!fullscreen">{{ __('attendee_check_in.actions.fullscreen_on') }}</span>
                <span x-show="fullscreen" x-cloak>{{ __('attendee_check_in.actions.fullscreen_off') }}</span>
            </button>
        </div>

        <form wire:submit.prevent="search" x-on:keydown.enter.prevent="$wire.search()">
            {{ $this->form }}
        </form>

        @if ($candidateBookings && $candidateBookings->count() > 1)
            <div class="space-y-2">
                <p class="text-sm text-gray-600">{{ __('attendee_check_in.disambiguation.prompt') }}</p>
                @foreach ($candidateBookings as $candidate)
                    <button
                        type="button"
                        wire:click="selectCandidateBooking({{ $candidate->id }})"
                        class="w-full text-left border rounded-xl p-4 bg-white shadow-sm"
                        style="min-height: 56px;"
                    >
                        <span class="font-mono font-semibold">{{ $candidate->booking_reference }}</span>
                        — {{ $candidate->event?->getTranslation('title', $locale) }}
                        ({{ $candidate->event_date?->format('Y-m-d') }})
                    </button>
                @endforeach
            </div>
        @endif

        @if ($booking)
            @php $remaining = $booking->attendees->where('checked_in', false)->count(); @endphp

            <div class="border rounded-xl p-4 bg-white shadow-sm space-y-1">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="font-mono text-lg font-bold">{{ $booking->booking_reference }}</span>
                    <span
                        class="inline-flex px-2 py-1 rounded-full text-xs font-medium text-white"
                        style="background-color: {{ $statusColors[$booking->status] ?? '#6b7280' }};"
                    >
                        {{ __('booking.tabs.' . $booking->status) }}
                    </span>
                </div>
                <p class="text-sm text-gray-700">{{ $booking->event?->getTranslation('title', $locale) }}</p>
                <p class="text-sm text-gray-600">
                    {{ $booking->event_date?->format('Y-m-d') }} · {{ $booking->timeSlot?->getTimeRange() }}
                </p>
                <p class="text-sm text-gray-600">{{ $booking->ticketType?->getTranslation('name', $locale) }}</p>
                <p class="text-sm text-gray-600">
                    {{ __('attendee_check_in.summary', ['checked_in' => $booking->attendees->count() - $remaining, 'total' => $booking->attendees->count()]) }}
                </p>
            </div>

            @if ($remaining > 0)
                <button
                    type="button"
                    wire:click="checkInAll"
                    wire:confirm="{{ __('attendee_check_in.actions.check_in_all_confirm', ['count' => $remaining]) }}"
                    wire:loading.attr="disabled"
                    wire:target="checkInAll"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-md text-white font-semibold transition-colors duration-150 disabled:opacity-50"
                    style="background-color: #16a34a; min-height: 48px;"
                    onmouseover="this.style.backgroundColor='#15803d'"
                    onmouseout="this.style.backgroundColor='#16a34a'"
                >
                    {{ __('attendee_check_in.actions.check_in_all', ['count' => $remaining]) }}
                </button>
            @endif

            <div class="space-y-3">
                @foreach ($booking->attendees as $attendee)
                    <div class="flex items-center justify-between gap-3 border rounded-xl p-4 bg-white shadow-sm">
                        <div class="min-w-0">
                            <p class="font-semibold truncate">{{ $attendee->getFullName() }}</p>
                            <p class="text-xs text-gray-500 font-mono">{{ $attendee->ticket_number }}</p>
                            @if ($attendee->ticketType)
                                <p class="text-xs text-gray-500">{{ $attendee->ticketType->getTranslation('name', $locale) }}</p>
                            @endif
                        </div>

                        <button
                            type="button"
                            wire:click="toggleAttendee({{ $attendee->id }})"
                            wire:loading.attr="disabled"
                            wire:target="toggleAttendee({{ $attendee->id }})"
                            role="switch"
                            aria-checked="{{ $attendee->checked_in ? 'true' : 'false' }}"
                            class="relative inline-flex shrink-0 items-center rounded-full transition-colors duration-150"
                            style="width: 64px; height: 36px; background-color: {{ $attendee->checked_in ? '#16a34a' : '#d1d5db' }};"
                        >
                            <span
                                class="inline-block rounded-full bg-white shadow transform transition-transform duration-150"
                                style="width: 28px; height: 28px; margin: 4px; transform: translateX({{ $attendee->checked_in ? '28px' : '0' }});"
                            ></span>
                        </button>
                    </div>
                @endforeach
            </div>

            <button
                type="button"
                wire:click="resetSearch"
                class="w-full inline-flex items-center justify-center px-4 py-2 rounded-md text-sm font-medium border border-gray-300"
                style="min-height: 44px;"
            >
                {{ __('attendee_check_in.actions.scan_another') }}
            </button>
        @endif
    </div>
</x-filament-panels::page>
