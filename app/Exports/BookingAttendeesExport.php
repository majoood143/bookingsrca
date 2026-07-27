<?php

namespace App\Exports;

use App\Models\BookingSetting;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column;

class BookingAttendeesExport extends ExcelExport
{
    /**
     * Names of the table columns currently toggled on. Null means no
     * restriction (export every column regardless of toggle state).
     */
    protected ?array $visibleTableColumns = null;

    /**
     * ExcelExport::make() calls setUp() immediately, before any further
     * fluent methods can run, so the visible-columns restriction must be
     * applied here rather than via a setter chained after make().
     */
    public static function make(string $name = 'export', ?array $visibleTableColumns = null): static
    {
        $static = app(static::class, ['name' => $name]);
        $static->visibleTableColumns = $visibleTableColumns;
        $static->setUp();

        return $static;
    }

    protected function isColumnVisible(string $tableColumnName): bool
    {
        return $this->visibleTableColumns === null
            || in_array($tableColumnName, $this->visibleTableColumns, true);
    }

    public function setUp(): void
    {
        $this->useTableQuery();

        $columns = [];

        if ($this->isColumnVisible('full_name')) {
            $columns[] = Column::make('first_name')
                ->heading(__('booking_attendee.fields.first_name'));

            $columns[] = Column::make('last_name')
                ->heading(__('booking_attendee.fields.last_name'));
        }

        if ($this->isColumnVisible('phone')) {
            $columns[] = Column::make('email')
                ->heading(__('booking_attendee.fields.email'));

            $columns[] = Column::make('phone')
                ->heading(__('booking_attendee.fields.phone'));
        }

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

        if ($this->isColumnVisible('ticket_number')) {
            $columns[] = Column::make('ticket_number')
                ->heading(__('booking_attendee.fields.ticket_number'));
        }

        if ($this->isColumnVisible('ticketType.name')) {
            $columns[] = Column::make('ticket_type')
                ->heading(__('booking_attendee.fields.ticket_type'))
                ->getStateUsing(fn($record) => $record->ticketType
                    ? $record->ticketType->getTranslation('name', app()->getLocale())
                    : '');
        }

        $columns[] = Column::make('ticket_price')
            ->heading(__('booking_attendee.fields.ticket_price'));

        if ($this->isColumnVisible('booking.booking_reference')) {
            $columns[] = Column::make('booking_reference')
                ->heading(__('booking_attendee.fields.booking_reference'))
                ->getStateUsing(fn($record) => $record->booking->booking_reference);
        }

        if ($this->isColumnVisible('booking.event.title')) {
            $columns[] = Column::make('event')
                ->heading(__('booking_attendee.fields.event'))
                ->getStateUsing(fn($record) => $record->booking->event->getTranslation('title', app()->getLocale()));
        }

        if ($this->isColumnVisible('booking.event_date')) {
            $columns[] = Column::make('event_date')
                ->heading(__('booking_attendee.fields.event_date'))
                ->getStateUsing(fn($record) => $record->booking->event_date?->format('Y-m-d'));
        }

        if ($this->isColumnVisible('booking.timeSlot.time_range')) {
            $columns[] = Column::make('time_slot')
                ->heading(__('booking_attendee.fields.time_slot'))
                ->getStateUsing(fn($record) => $record->booking->timeSlot?->getTimeRange());
        }

        if ($this->isColumnVisible('booking.status')) {
            $columns[] = Column::make('booking_status')
                ->heading(__('booking_attendee.fields.booking_status'))
                ->getStateUsing(fn($record) => ucfirst(str_replace('_', ' ', $record->booking->status)));
        }

        if ($this->isColumnVisible('email_sent')) {
            $columns[] = Column::make('email_sent')
                ->heading(__('booking_attendee.fields.email_sent'))
                ->formatStateUsing(fn($state) => $state ? 'Yes' : 'No');

            $columns[] = Column::make('email_sent_at')
                ->heading(__('booking_attendee.fields.email_sent_at'))
                ->formatStateUsing(fn($state) => $state?->format('Y-m-d H:i'));
        }

        if ($this->isColumnVisible('checked_in')) {
            $columns[] = Column::make('checked_in')
                ->heading(__('booking_attendee.fields.checked_in'))
                ->formatStateUsing(fn($state) => $state ? 'Yes' : 'No');

            $columns[] = Column::make('checked_in_at')
                ->heading(__('booking_attendee.fields.checked_in_at'))
                ->formatStateUsing(fn($state) => $state?->format('Y-m-d H:i'));
        }

        if ($this->isColumnVisible('created_at')) {
            $columns[] = Column::make('created_at')
                ->heading(__('booking_attendee.columns.created_at'))
                ->formatStateUsing(fn($state) => $state?->format('Y-m-d H:i'));
        }

        $this->withColumns($columns);
    }
}
