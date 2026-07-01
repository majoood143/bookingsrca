<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Booking $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function build()
    {
        $locale = $this->booking->locale ?? 'en';

        return $this->locale($locale)
            ->subject(__('event_booking.email.subject', ['reference' => $this->booking->booking_reference], $locale))
            ->view('emails.booking-confirmation')
            ->with(['booking' => $this->booking, 'locale' => $locale]);
    }
}
