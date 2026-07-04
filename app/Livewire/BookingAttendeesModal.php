<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Booking;
use Filament\Notifications\Notification;

class BookingAttendeesModal extends Component
{
    public Booking $booking;

    public function mount(Booking $booking)
    {
        $this->booking = $booking->load('attendees.ticketType');
    }

    public function sendTicketEmail($attendeeId)
    {
        $attendee = $this->booking->attendees->firstWhere('id', $attendeeId);

        if ($attendee && $attendee->sendTicketEmail()) {
            Notification::make()
                ->success()
                ->title('Ticket sent')
                ->body('Ticket has been sent to ' . $attendee->email)
                ->send();

            $this->booking->load('attendees.ticketType');
        } else {
            Notification::make()
                ->danger()
                ->title('Failed to send')
                ->body('Could not send ticket email')
                ->send();
        }
    }

    public function resendTicketEmail($attendeeId)
    {
        $this->sendTicketEmail($attendeeId);
    }

    public function checkInAttendee($attendeeId)
    {
        $attendee = $this->booking->attendees->firstWhere('id', $attendeeId);

        if ($attendee) {
            $attendee->checkIn();

            Notification::make()
                ->success()
                ->title('Checked in')
                ->body($attendee->getFullName() . ' has been checked in')
                ->send();

            $this->booking->load('attendees.ticketType');
        }
    }

    public function render()
    {
        return view('filament.modals.booking-attendees', [
            'booking' => $this->booking
        ]);
    }
}