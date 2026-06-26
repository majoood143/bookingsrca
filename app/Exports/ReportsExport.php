<?php

namespace App\Exports;

use App\Models\Booking;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ReportsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    public function __construct(
        protected Carbon $from,
        protected Carbon $to,
        protected ?int $eventId = null,
        protected ?string $eventDate = null,
        protected ?int $timeSlotId = null,
    ) {}

    public function title(): string
    {
        return __('reports.export.sheet_bookings');
    }

    public function collection()
    {
        $query = Booking::query()
            ->with(['event', 'timeSlot', 'ticketType', 'attendees', 'extraServices'])
            ->whereBetween('created_at', [$this->from, $this->to])
            ->orderBy('created_at', 'desc');

        if ($this->eventId) {
            $query->where('event_id', $this->eventId);
        }

        if ($this->eventDate) {
            $query->where('event_date', $this->eventDate);
        }

        if ($this->timeSlotId) {
            $query->where('time_slot_id', $this->timeSlotId);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            __('reports.export.headings.reference'),
            __('reports.export.headings.event'),
            __('reports.export.headings.event_date'),
            __('reports.export.headings.time_slot'),
            __('reports.export.headings.ticket_type'),
            __('reports.export.headings.quantity'),
            __('reports.export.headings.ticket_price'),
            __('reports.export.headings.services_price'),
            __('reports.export.headings.total_price'),
            __('reports.export.headings.status'),
            __('reports.export.headings.attendees'),
            __('reports.export.headings.confirmed_at'),
            __('reports.export.headings.cancelled_at'),
            __('reports.export.headings.created_at'),
        ];
    }

    public function map($booking): array
    {
        $locale = app()->getLocale();

        $attendeeNames = $booking->attendees
            ->map(fn($a) => $a->first_name . ' ' . $a->last_name)
            ->implode(', ');

        $timeSlotLabel = $booking->timeSlot
            ? $booking->timeSlot->start_time . ' – ' . $booking->timeSlot->end_time
            : '';

        return [
            $booking->booking_reference,
            $booking->event?->getTranslation('title', $locale) ?? '',
            $booking->event_date?->format('Y-m-d') ?? '',
            $timeSlotLabel,
            $booking->ticketType?->getTranslation('name', $locale) ?? '',
            $booking->quantity,
            number_format((float) $booking->ticket_price, 2),
            number_format((float) $booking->services_price, 2),
            number_format((float) $booking->total_price, 2),
            ucfirst(str_replace('_', ' ', $booking->status)),
            $attendeeNames,
            $booking->confirmed_at?->format('Y-m-d H:i') ?? '',
            $booking->cancelled_at?->format('Y-m-d H:i') ?? '',
            $booking->created_at?->format('Y-m-d H:i') ?? '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFF59E0B'],
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}
