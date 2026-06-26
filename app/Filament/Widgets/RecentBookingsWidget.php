<?php

namespace App\Filament\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Actions\Action;
use App\Models\Booking;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentBookingsWidget extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    protected function getTableHeading(): string
    {
        return __('widgets.recent_bookings.heading');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Booking::query()
                    ->with(['event', 'ticketType', 'attendees'])
                    ->latest()
                    ->limit(15)
            )
            ->columns([
                TextColumn::make('booking_reference')
                    ->label(__('widgets.recent_bookings.reference'))
                    ->weight('bold')
                    ->copyable()
                    ->searchable(),

                TextColumn::make('event.title')
                    ->label(__('widgets.recent_bookings.event'))
                    ->searchable()
                    ->limit(25),

                TextColumn::make('event_date')
                    ->label(__('widgets.recent_bookings.event_date'))
                    ->date('M j, Y')
                    ->sortable(),

                TextColumn::make('quantity')
                    ->label(__('widgets.recent_bookings.qty'))
                    ->badge()
                    ->color('gray'),

                TextColumn::make('total_price')
                    ->label(__('widgets.recent_bookings.total'))
                    ->money('OMR')
                    ->weight('bold')
                    ->color('success'),

                BadgeColumn::make('status')
                    ->label(__('widgets.recent_bookings.status'))
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'confirmed',
                        'danger'  => 'cancelled',
                        'info'    => 'checked_in',
                    ]),

                TextColumn::make('created_at')
                    ->label(__('widgets.recent_bookings.booked'))
                    ->since()
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('confirm')
                    ->label(__('widgets.recent_bookings.action_confirm'))
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->visible(fn (Booking $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (Booking $record): void {
                        $record->confirm();
                        Notification::make()
                            ->title(__('widgets.recent_bookings.confirmed_msg'))
                            ->success()
                            ->send();
                    }),

                Action::make('view')
                    ->label(__('widgets.recent_bookings.action_view'))
                    ->icon('heroicon-m-eye')
                    ->url(fn (Booking $record): string =>
                        route('filament.admin.resources.bookings.view', $record)
                    ),

                Action::make('edit')
                    ->label(__('widgets.recent_bookings.action_edit'))
                    ->icon('heroicon-m-pencil-square')
                    ->url(fn (Booking $record): string =>
                        route('filament.admin.resources.bookings.edit', $record)
                    ),
            ])
            ->paginated([10, 25]);
    }
}
