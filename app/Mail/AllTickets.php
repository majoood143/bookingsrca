<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class AllTickets extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Booking $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function build()
    {
        $booking = $this->booking;
        $locale  = $booking->locale ?? 'en';
        $primary = $booking->attendees->first();

        $email = $this->locale($locale)
            ->subject(__('event_booking.email.tickets_subject', ['reference' => $booking->booking_reference], $locale))
            ->view('emails.all-tickets')
            ->with([
                'booking'   => $booking,
                'attendees' => $booking->attendees,
                'primary'   => $primary,
                'locale'    => $locale,
            ]);

        // Attach every attendee's PDF ticket and QR code to this single email
        foreach ($booking->attendees as $attendee) {
            if ($attendee->pdf_path && Storage::disk('public')->exists($attendee->pdf_path)) {
                $email->attach(
                    Storage::disk('public')->path($attendee->pdf_path),
                    [
                        'as' => 'ticket-' . $attendee->ticket_number . '.pdf',
                        'mime' => 'application/pdf',
                    ]
                );
            }

            if ($attendee->qr_code && Storage::disk('public')->exists($attendee->qr_code)) {
                $email->attach(
                    Storage::disk('public')->path($attendee->qr_code),
                    [
                        'as' => 'qr-code-' . $attendee->ticket_number . '.png',
                        'mime' => 'image/png',
                    ]
                );
            }
        }

        return $email;
    }
}
