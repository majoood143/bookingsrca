<div class="space-y-4">
    @foreach($booking->attendees as $attendee)
        <div class="border rounded-lg p-4 bg-white">
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <h3 class="text-lg font-bold">{{ $attendee->getFullName() }}</h3>
                    <p class="text-sm text-gray-600">{{ $attendee->email }}</p>
                    <p class="text-sm text-gray-600">Ticket: <span class="font-mono">{{ $attendee->ticket_number }}</span></p>

                     {{-- ADD THIS: Show ticket type --}}
                    @if($attendee->ticketType)
                        <p class="text-sm text-gray-700 mt-1">
                            <span class="font-semibold">Ticket Type:</span>
                            {{ $attendee->ticketType->getTranslation('name', 'en') }}
                            <span class="text-green-600 font-semibold">OMR {{ number_format($attendee->ticket_price, 3) }}</span>
                        </p>
                    @endif

                    <div class="mt-2 flex gap-2">
                        @if($attendee->email_sent)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-green-100 text-green-800">
                                ✓ Email Sent
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-800">
                                ✗ Email Not Sent
                            </span>
                        @endif

                        @if($attendee->checked_in)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800">
                                ✓ Checked In
                            </span>
                        @endif
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    @if($attendee->qr_code)
                        <a href="{{ $attendee->getQrCodeUrl() }}"
                           target="_blank"
                           class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            View QR Code
                        </a>
                    @endif

                    @if($attendee->pdf_path)
                        <a href="{{ $attendee->getPdfUrl() }}"
                           download
                           class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                            Download Ticket
                        </a>
                    @endif

                    @if(!$attendee->email_sent)
                        <button
                            wire:click="sendTicketEmail({{ $attendee->id }})"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700">
                            Send Ticket
                        </button>
                    @else
                        <button
                            wire:click="resendTicketEmail({{ $attendee->id }})"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Resend Ticket
                        </button>
                    @endif

                    @if(!$attendee->checked_in)
                        <button
                            wire:click="checkInAttendee({{ $attendee->id }})"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                            Check In
                        </button>
                    @endif
                </div>
            </div>

            @if($attendee->qr_code)
                <div class="mt-4 text-center">
                    <img src="events{{ $attendee->getQrCodeUrl() }}" alt="QR Code" class="mx-auto" style="max-width: 150px;">
                </div>
            @endif
        </div>
    @endforeach
</div>
