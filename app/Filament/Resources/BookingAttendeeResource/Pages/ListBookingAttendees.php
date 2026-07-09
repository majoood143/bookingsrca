<?php

namespace App\Filament\Resources\BookingAttendeeResource\Pages;

use Filament\Schemas\Components\Tabs\Tab;
use App\Filament\Resources\BookingAttendeeResource;
use App\Models\BookingAttendee;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;
use App\Exports\BookingAttendeesExport;

class ListBookingAttendees extends ListRecords
{
    protected static string $resource = BookingAttendeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->exports([
                    BookingAttendeesExport::make(),
                ]),
        ];
    }

    public function getTabs(): array
    {
        return [
            'confirmed' => Tab::make(__('booking_attendee.tabs.confirmed'))
                ->modifyQueryUsing(fn(Builder $query) => $query->whereHas('booking', fn(Builder $query) => $query->where('status', 'confirmed')))
                ->badge(fn() => BookingAttendee::whereHas('booking', fn(Builder $query) => $query->where('status', 'confirmed'))->count())
                ->badgeColor('success'),

            'cancelled' => Tab::make(__('booking_attendee.tabs.cancelled'))
                ->modifyQueryUsing(fn(Builder $query) => $query->whereHas('booking', fn(Builder $query) => $query->where('status', 'cancelled')))
                ->badge(fn() => BookingAttendee::whereHas('booking', fn(Builder $query) => $query->where('status', 'cancelled'))->count())
                ->badgeColor('danger'),

            'all' => Tab::make(__('booking_attendee.tabs.all'))
                ->badge(fn() => BookingAttendee::count()),

            'checked_in' => Tab::make(__('booking_attendee.tabs.checked_in'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('checked_in', true))
                ->badge(fn() => BookingAttendee::where('checked_in', true)->count())
                ->badgeColor('success'),

            'not_checked_in' => Tab::make(__('booking_attendee.tabs.not_checked_in'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('checked_in', false))
                ->badge(fn() => BookingAttendee::where('checked_in', false)->count())
                ->badgeColor('warning'),

            'email_sent' => Tab::make(__('booking_attendee.tabs.email_sent'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('email_sent', true))
                ->badge(fn() => BookingAttendee::where('email_sent', true)->count())
                ->badgeColor('primary'),

            'email_pending' => Tab::make(__('booking_attendee.tabs.email_pending'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('email_sent', false))
                ->badge(fn() => BookingAttendee::where('email_sent', false)->count())
                ->badgeColor('danger'),
        ];
    }
}
