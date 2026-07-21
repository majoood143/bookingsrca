<?php

namespace App\Filament\Resources\BookingResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Schemas\Components\Tabs\Tab;
use App\Models\Booking;
use App\Filament\Resources\BookingResource;
use App\Exports\BookingsExport;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;

class ListBookings extends ListRecords
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->label(__('booking.actions.export'))
                ->icon('heroicon-o-arrow-down-tray')
                ->exports([
                    BookingsExport::make()
                        ->withFilename(fn () => 'bookings-' . now()->format('Y-m-d'))
                        ->queue(),
                ]),

            CreateAction::make()
                ->label(__('booking.actions.new_booking'))
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('booking.tabs.all'))
                ->badge(fn() => Booking::count()),

            'pending' => Tab::make(__('booking.tabs.pending'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'pending'))
                ->badge(fn() => Booking::where('status', 'pending')->count())
                ->badgeColor('warning'),

            'confirmed' => Tab::make(__('booking.tabs.confirmed'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'confirmed'))
                ->badge(fn() => Booking::where('status', 'confirmed')->count())
                ->badgeColor('success'),

            'checked_in' => Tab::make(__('booking.tabs.checked_in'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'checked_in'))
                ->badge(fn() => Booking::where('status', 'checked_in')->count())
                ->badgeColor('primary'),

            'cancelled' => Tab::make(__('booking.tabs.cancelled'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'cancelled'))
                ->badge(fn() => Booking::where('status', 'cancelled')->count())
                ->badgeColor('danger'),

            'refunded' => Tab::make(__('booking.tabs.refunded'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'refunded'))
                ->badge(fn() => Booking::where('status', 'refunded')->count())
                ->badgeColor('gray'),
        ];
    }
}
