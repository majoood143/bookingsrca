<?php

namespace App\Jobs;

use App\Models\BookingAttendee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

// QR code generation and mPDF rendering (Arabic OTL shaping in particular) are too
// slow to run inline in the request — this used to happen synchronously inside
// BookingAttendee's `created` model event, blocking the booking request (and holding
// its DB row locks) for every attendee on the booking.
class GenerateAttendeeTicketAssets implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $attendeeId)
    {
    }

    public function handle(): void
    {
        $attendee = BookingAttendee::find($this->attendeeId);

        if (!$attendee) {
            return;
        }

        if (!$attendee->qr_code) {
            $attendee->generateQrCode();
        }

        if (!$attendee->pdf_path) {
            $attendee->generatePdfTicket();
        }
    }
}
