<?php

namespace App\Exports;

use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column;

class BookingsExport extends ExcelExport
{
    public function setUp(): void
    {
        $this->useTableQuery();

        $this->withColumns([
            Column::make('booking_reference')
                ->heading(__('booking.columns.reference')),

            Column::make('status')
                ->heading(__('booking.fields.status'))
                ->formatStateUsing(fn($state) => __('booking.options.status')[$state] ?? $state),

            Column::make('attendee_name')
                ->heading(__('booking.columns.attendee_phone'))
                ->getStateUsing(fn($record) => $record->firstAttendee?->getFullName()),

            Column::make('attendee_email')
                ->heading(__('booking.columns.attendee_email'))
                ->getStateUsing(fn($record) => $record->firstAttendee?->email),

            Column::make('attendee_phone')
                ->heading(__('booking.fields.phone'))
                ->getStateUsing(fn($record) => $record->firstAttendee?->phone),

            Column::make('event')
                ->heading(__('booking.columns.event'))
                ->getStateUsing(fn($record) => $record->event?->getTranslation('title', 'en')),

            Column::make('event_date')
                ->heading(__('booking.columns.date'))
                ->formatStateUsing(fn($state) => $state?->format('Y-m-d')),

            Column::make('time_slot')
                ->heading(__('booking.columns.time'))
                ->getStateUsing(fn($record) => $record->timeSlot?->getTimeRange()),

            Column::make('attendees_count')
                ->heading(__('booking.columns.attendees'))
                ->getStateUsing(fn($record) => $record->attendees->count()),

            Column::make('ticket_price')
                ->heading(__('booking.fields.ticket_price')),

            Column::make('services_price')
                ->heading(__('booking.fields.services_price')),

            Column::make('total_price')
                ->heading(__('booking.columns.total')),

            Column::make('source')
                ->heading(__('booking.columns.source'))
                ->formatStateUsing(fn($state) => __('booking.options.source')[$state] ?? $state),

            Column::make('created_by')
                ->heading(__('booking.columns.created_by'))
                ->getStateUsing(fn($record) => $record->createdBy?->name),

            Column::make('created_at')
                ->heading(__('booking.columns.booked_at'))
                ->formatStateUsing(fn($state) => $state?->format('Y-m-d H:i')),
        ]);
    }
}
