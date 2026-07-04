<?php

namespace App\Exports;

use App\Models\Booking;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BookingsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $bookingIds;

    public function __construct($bookingIds = null)
    {
        $this->bookingIds = $bookingIds;
    }

    public function collection()
    {
        $query = Booking::with(['event', 'attendees', 'timeSlot', 'ticketType', 'extraServices']);
        
        if ($this->bookingIds) {
            $query->whereIn('id', $this->bookingIds);
        }
        
        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Booking Reference',
            'Status',
            'Attendee Name',
            'Email',
            'Phone',
            'Event Title',
            'Event Date',
            'Time Slot',
            'Ticket Type',
            'Quantity',
            'Ticket Price',
            'Services Price',
            'Total Price',
            'Extra Services',
            'Booked At',
            'Confirmed At',
        ];
    }

    public function map($booking): array
    {
        $extraServices = $booking->extraServices
            ->map(fn($service) => $service->getTranslation('name', 'en'))
            ->join(', ');

        $attendee = $booking->attendees->first();

        return [
            $booking->booking_reference,
            ucfirst($booking->status),
            $attendee?->getFullName(),
            $attendee?->email,
            $attendee?->phone,
            $booking->event->getTranslation('title', 'en'),
            $booking->event_date->format('Y-m-d'),
            $booking->timeSlot->getTimeRange(),
            $booking->ticketType->getTranslation('name', 'en'),
            $booking->quantity,
            number_format($booking->ticket_price, 2),
            number_format($booking->services_price, 2),
            number_format($booking->total_price, 2),
            $extraServices ?: 'None',
            $booking->created_at->format('Y-m-d H:i:s'),
            $booking->confirmed_at ? $booking->confirmed_at->format('Y-m-d H:i:s') : 'Not confirmed',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
