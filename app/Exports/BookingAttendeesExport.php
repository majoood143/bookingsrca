<?php

namespace App\Exports;

use App\Models\BookingSetting;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column;

class BookingAttendeesExport extends ExcelExport
{
    public function setUp(): void
    {
        $this->useTableQuery();

        $columns = [
            Column::make('first_name')
                ->heading(__('booking_attendee.fields.first_name')),

            Column::make('last_name')
                ->heading(__('booking_attendee.fields.last_name')),

            Column::make('email')
                ->heading(__('booking_attendee.fields.email')),

            Column::make('phone')
                ->heading(__('booking_attendee.fields.phone')),
        ];

        if (BookingSetting::get('show_date_of_birth', true)) {
            $columns[] = Column::make('date_of_birth')
                ->heading(__('booking_attendee.fields.date_of_birth'))
                ->formatStateUsing(fn($state) => $state?->format('Y-m-d'));
        }

        if (BookingSetting::get('show_gender', true)) {
            $columns[] = Column::make('gender')
                ->heading(__('booking_attendee.fields.gender'))
                ->formatStateUsing(fn($state) => $state ? ucfirst($state) : '');
        }

        if (BookingSetting::get('show_nationality', true)) {
            $columns[] = Column::make('nationality')
                ->heading(__('booking_attendee.fields.nationality'));
        }

        if (BookingSetting::get('show_identity_number', true)) {
            $columns[] = Column::make('identity_number')
                ->heading(__('booking_attendee.fields.identity_number'));
        }

        $columns = array_merge($columns, [
            Column::make('ticket_number')
                ->heading(__('booking_attendee.fields.ticket_number')),

            Column::make('ticket_type')
                ->heading(__('booking_attendee.fields.ticket_type'))
                ->getStateUsing(fn($record) => $record->ticketType
                    ? $record->ticketType->getTranslation('name', app()->getLocale())
                    : ''),

            Column::make('ticket_price')
                ->heading(__('booking_attendee.fields.ticket_price')),

            Column::make('booking_reference')
                ->heading(__('booking_attendee.fields.booking_reference'))
                ->getStateUsing(fn($record) => $record->booking->booking_reference),

            Column::make('event')
                ->heading(__('booking_attendee.fields.event'))
                ->getStateUsing(fn($record) => $record->booking->event->getTranslation('title', app()->getLocale())),

            Column::make('event_date')
                ->heading(__('booking_attendee.fields.event_date'))
                ->getStateUsing(fn($record) => $record->booking->event_date?->format('Y-m-d')),

            Column::make('booking_status')
                ->heading(__('booking_attendee.fields.booking_status'))
                ->getStateUsing(fn($record) => ucfirst(str_replace('_', ' ', $record->booking->status))),

            Column::make('email_sent')
                ->heading(__('booking_attendee.fields.email_sent'))
                ->formatStateUsing(fn($state) => $state ? 'Yes' : 'No'),

            Column::make('email_sent_at')
                ->heading(__('booking_attendee.fields.email_sent_at'))
                ->formatStateUsing(fn($state) => $state?->format('Y-m-d H:i')),

            Column::make('checked_in')
                ->heading(__('booking_attendee.fields.checked_in'))
                ->formatStateUsing(fn($state) => $state ? 'Yes' : 'No'),

            Column::make('checked_in_at')
                ->heading(__('booking_attendee.fields.checked_in_at'))
                ->formatStateUsing(fn($state) => $state?->format('Y-m-d H:i')),

            Column::make('created_at')
                ->heading(__('booking_attendee.columns.created_at'))
                ->formatStateUsing(fn($state) => $state?->format('Y-m-d H:i')),
        ]);

        $this->withColumns($columns);
    }
}
