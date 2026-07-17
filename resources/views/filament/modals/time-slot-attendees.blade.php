@php
    $attendees = $timeSlot->bookings->flatMap(
        fn($booking) => $booking->attendees->map(fn($attendee) => tap($attendee, fn($a) => $a->setRelation('booking', $booking)))
    );
    $totalAttendees = $attendees->count();
    $checkedInCount = $attendees->where('checked_in', true)->count();
    $emailedCount = $attendees->where('email_sent', true)->count();
@endphp

<div class="space-y-4">
    @if($totalAttendees > 0)
        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 px-1 text-sm text-gray-600">
            {{ __('time_slot.attendees_modal.summary', [
                'checked_in' => $checkedInCount,
                'emailed' => $emailedCount,
                'total' => $totalAttendees,
            ]) }}
        </div>
    @endif

    @forelse($attendees as $attendee)
        <div class="border rounded-xl p-4 bg-white shadow-sm">
            <div class="flex-1">
                <h3 class="text-lg font-bold">{{ $attendee->getFullName() }}</h3>
                <p class="text-sm text-gray-600">{{ $attendee->email ?: __('booking.attendees_modal.no_email') }}</p>
                @if($attendee->phone)
                    <p class="text-sm text-gray-600">{{ $attendee->phone }}</p>
                @endif
                <p class="text-sm text-gray-600">{{ __('booking_attendee.fields.ticket_number') }}: <span class="font-mono">{{ $attendee->ticket_number }}</span></p>

                @if($attendee->ticketType)
                    <p class="text-sm text-gray-700 mt-1">
                        <span class="font-semibold">{{ __('booking_attendee.fields.ticket_type') }}:</span>
                        {{ $attendee->ticketType->getTranslation('name', app()->getLocale()) }}
                        <span class="text-green-600 font-semibold">@include('partials.currency-amount', ['amount' => $attendee->ticket_price])</span>
                    </p>
                @endif

                <div class="mt-2 flex flex-wrap gap-2">
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                        {{ __('time_slot.attendees_modal.booking_ref') }}: {{ $attendee->booking->booking_reference }}
                    </span>

                    @if($attendee->email_sent)
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            ✓ {{ __('booking.attendees_modal.email_sent') }}
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            ✗ {{ __('booking.attendees_modal.email_not_sent') }}
                        </span>
                    @endif

                    @if($attendee->checked_in)
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            ✓ {{ __('booking.attendees_modal.checked_in') }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="text-center text-sm text-gray-500 py-8">
            {{ __('time_slot.attendees_modal.empty') }}
        </div>
    @endforelse
</div>
