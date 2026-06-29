<div class="space-y-4">
    @foreach($booking->attendees as $attendee)
        <div class="border rounded-xl p-4 bg-white shadow-sm">
            <div class="flex flex-col md:flex-row justify-between items-start gap-4">
                <div class="flex-1">
                    <h3 class="text-lg font-bold">{{ $attendee->getFullName() }}</h3>
                    <p class="text-sm text-gray-600">{{ $attendee->email }}</p>
                    <p class="text-sm text-gray-600">Ticket: <span class="font-mono">{{ $attendee->ticket_number }}</span></p>

                    @if($attendee->ticketType)
                        <p class="text-sm text-gray-700 mt-1">
                            <span class="font-semibold">Ticket Type:</span>
                            {{ $attendee->ticketType->getTranslation('name', 'en') }}
                            <span class="text-green-600 font-semibold">OMR {{ number_format($attendee->ticket_price, 3) }}</span>
                        </p>
                    @endif

                    <div class="mt-2 flex gap-2">
                        @if($attendee->email_sent)
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                ✓ Email Sent
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                ✗ Email Not Sent
                            </span>
                        @endif

                        @if($attendee->checked_in)
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                ✓ Checked In
                            </span>
                        @endif
                    </div>
                </div>

                <div class="flex flex-col gap-2 w-full md:w-auto">
                    @if($attendee->qr_code)
                        <a href="{{ $attendee->getQrCodeUrl() }}"
                           target="_blank"
                           class="inline-flex items-center justify-center gap-1.5 px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white shadow-sm transition-colors duration-150"
                           style="background-color: #2563eb;"
                           onmouseover="this.style.backgroundColor='#1d4ed8'"
                           onmouseout="this.style.backgroundColor='#2563eb'">
                            @svg('heroicon-o-qr-code', 'w-4 h-4', ['style' => 'width:16px;height:16px;flex-shrink:0;'])
                            View QR Code
                        </a>
                    @endif

                    @if($attendee->pdf_path)
                        <a href="{{ $attendee->getPdfUrl() }}"
                           download
                           class="inline-flex items-center justify-center gap-1.5 px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white shadow-sm transition-colors duration-150"
                           style="background-color: #16a34a;"
                           onmouseover="this.style.backgroundColor='#15803d'"
                           onmouseout="this.style.backgroundColor='#16a34a'">
                            @svg('heroicon-o-arrow-down-tray', 'w-4 h-4', ['style' => 'width:16px;height:16px;flex-shrink:0;'])
                            Download Ticket
                        </a>
                    @endif

                    @if(!$attendee->email_sent)
                        <button
                            wire:click="sendTicketEmail({{ $attendee->id }})"
                            type="button"
                            class="inline-flex items-center justify-center gap-1.5 px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white shadow-sm transition-colors duration-150"
                            style="background-color: #7c3aed;"
                            onmouseover="this.style.backgroundColor='#6d28d9'"
                            onmouseout="this.style.backgroundColor='#7c3aed'">
                            @svg('heroicon-o-paper-airplane', 'w-4 h-4', ['style' => 'width:16px;height:16px;flex-shrink:0;'])
                            Send Ticket
                        </button>
                    @else
                        <button
                            wire:click="resendTicketEmail({{ $attendee->id }})"
                            type="button"
                            class="inline-flex items-center justify-center gap-1.5 px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 shadow-sm transition-colors duration-150"
                            style="background-color: #ffffff;"
                            onmouseover="this.style.backgroundColor='#f9fafb'"
                            onmouseout="this.style.backgroundColor='#ffffff'">
                            @svg('heroicon-o-arrow-path', 'w-4 h-4', ['style' => 'width:16px;height:16px;flex-shrink:0;'])
                            Resend Ticket
                        </button>
                    @endif

                    @if(!$attendee->checked_in)
                        <button
                            wire:click="checkInAttendee({{ $attendee->id }})"
                            type="button"
                            class="inline-flex items-center justify-center gap-1.5 px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white shadow-sm transition-colors duration-150"
                            style="background-color: #4f46e5;"
                            onmouseover="this.style.backgroundColor='#4338ca'"
                            onmouseout="this.style.backgroundColor='#4f46e5'">
                            @svg('heroicon-o-check-circle', 'w-4 h-4', ['style' => 'width:16px;height:16px;flex-shrink:0;'])
                            Check In
                        </button>
                    @endif
                </div>
            </div>

            @if($attendee->qr_code)
                <div class="mt-4 flex flex-col items-center">
                    <div class="inline-block p-3 bg-white border border-gray-200 rounded-lg shadow-sm">
                        <img src="{{ $attendee->getQrCodeBase64() }}" alt="QR Code" style="max-width: 160px; width: 100%; height: auto;">
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Scan at entrance to check in</p>
                </div>
            @endif
        </div>
    @endforeach
</div>
