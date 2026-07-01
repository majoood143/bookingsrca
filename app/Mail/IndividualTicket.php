<?php

namespace App\Mail;

use App\Models\BookingAttendee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class IndividualTicket extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public BookingAttendee $attendee;

    public function __construct(BookingAttendee $attendee)
    {
        $this->attendee = $attendee;
    }

    public function build()
    {
        $booking = $this->attendee->booking;
        $locale  = $booking->locale ?? 'en';

        $email = $this->locale($locale)
            ->subject(__('event_booking.email.ticket_subject', ['ticket_number' => $this->attendee->ticket_number], $locale))
            ->view('emails.individual-ticket')
            ->with([
                'attendee' => $this->attendee,
                'booking'  => $booking,
                'locale'   => $locale,
            ]);

        // Attach PDF ticket
        if ($this->attendee->pdf_path && Storage::disk('public')->exists($this->attendee->pdf_path)) {
            $email->attach(
                Storage::disk('public')->path($this->attendee->pdf_path),
                [
                    'as' => 'ticket-' . $this->attendee->ticket_number . '.pdf',
                    'mime' => 'application/pdf',
                ]
            );
        }

        // Attach QR code
        if ($this->attendee->qr_code && Storage::disk('public')->exists($this->attendee->qr_code)) {
            $email->attach(
                Storage::disk('public')->path($this->attendee->qr_code),
                [
                    'as' => 'qr-code-' . $this->attendee->ticket_number . '.png',
                    'mime' => 'image/png',
                ]
            );
        }

        return $email;
    }
}