<?php

namespace App\Exports;

use App\Models\BookingAttendee;
use App\Models\BookingSetting;
use App\Models\TimeSlot;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TimeSlotAttendeesExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected TimeSlot $timeSlot;

    public function __construct(TimeSlot $timeSlot)
    {
        $this->timeSlot = $timeSlot;
    }

    public function collection()
    {
        return BookingAttendee::query()
            ->whereHas('booking', fn($query) => $query->where('time_slot_id', $this->timeSlot->id))
            ->with(['booking', 'ticketType'])
            ->get();
    }

    public function headings(): array
    {
        $headings = [
            __('booking_attendee.fields.first_name'),
            __('booking_attendee.fields.last_name'),
            __('booking_attendee.fields.email'),
            __('booking_attendee.fields.phone'),
        ];

        if (BookingSetting::get('show_date_of_birth', true)) {
            $headings[] = __('booking_attendee.fields.date_of_birth');
        }

        if (BookingSetting::get('show_gender', true)) {
            $headings[] = __('booking_attendee.fields.gender');
        }

        if (BookingSetting::get('show_nationality', true)) {
            $headings[] = __('booking_attendee.fields.nationality');
        }

        if (BookingSetting::get('show_identity_number', true)) {
            $headings[] = __('booking_attendee.fields.identity_number');
        }

        return array_merge($headings, [
            __('booking_attendee.fields.ticket_number'),
            __('booking_attendee.fields.ticket_type'),
            __('booking_attendee.fields.ticket_price'),
            __('booking_attendee.fields.booking_reference'),
            __('booking_attendee.fields.booking_status'),
            __('booking_attendee.fields.email_sent'),
            __('booking_attendee.fields.checked_in'),
        ]);
    }

    public function map($attendee): array
    {
        $row = [
            $attendee->first_name,
            $attendee->last_name,
            $attendee->email,
            $attendee->phone,
        ];

        if (BookingSetting::get('show_date_of_birth', true)) {
            $row[] = $attendee->date_of_birth?->format('Y-m-d');
        }

        if (BookingSetting::get('show_gender', true)) {
            $row[] = $attendee->gender ? ucfirst($attendee->gender) : '';
        }

        if (BookingSetting::get('show_nationality', true)) {
            $row[] = $attendee->nationality;
        }

        if (BookingSetting::get('show_identity_number', true)) {
            $row[] = $attendee->identity_number;
        }

        return array_merge($row, [
            $attendee->ticket_number,
            $attendee->ticketType?->getTranslation('name', app()->getLocale()),
            number_format($attendee->ticket_price, 2),
            $attendee->booking->booking_reference,
            ucfirst(str_replace('_', ' ', $attendee->booking->status)),
            $attendee->email_sent ? 'Yes' : 'No',
            $attendee->checked_in ? 'Yes' : 'No',
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
