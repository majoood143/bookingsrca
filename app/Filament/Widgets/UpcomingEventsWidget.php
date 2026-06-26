<?php

namespace App\Filament\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Actions\Action;
use App\Models\Booking;
use App\Models\Event;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;

class UpcomingEventsWidget extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    protected function getTableHeading(): string
    {
        return __('widgets.upcoming_events.heading');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Event::query()
                    ->where('status', 'published')
                    ->where('end_date', '>=', Carbon::today())
                    ->orderBy('start_date', 'asc')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('title')
                    ->label(__('widgets.upcoming_events.event'))
                    ->searchable()
                    ->weight('bold')
                    ->limit(35),

                TextColumn::make('start_date')
                    ->label(__('widgets.upcoming_events.start_date'))
                    ->date('M j, Y')
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label(__('widgets.upcoming_events.end_date'))
                    ->date('M j, Y')
                    ->sortable(),

                TextColumn::make('days_until')
                    ->label(__('widgets.upcoming_events.starts_in'))
                    ->state(function (Event $record): string {
                        $days = Carbon::today()->diffInDays($record->start_date, false);
                        if ($days < 0) {
                            return __('widgets.upcoming_events.ongoing');
                        }
                        if ($days === 0) {
                            return __('widgets.upcoming_events.today');
                        }
                        return trans_choice('widgets.upcoming_events.days', $days, ['count' => $days]);
                    })
                    ->badge()
                    ->color(function (Event $record): string {
                        $days = Carbon::today()->diffInDays($record->start_date, false);
                        return match (true) {
                            $days <= 0  => 'success',
                            $days <= 3  => 'warning',
                            $days <= 7  => 'info',
                            default     => 'gray',
                        };
                    }),

                TextColumn::make('total_bookings')
                    ->label(__('widgets.upcoming_events.bookings'))
                    ->state(fn (Event $record): int =>
                        $record->bookings()->whereNotIn('status', ['cancelled'])->count()
                    )
                    ->badge()
                    ->color('primary'),

                TextColumn::make('capacity_pct')
                    ->label(__('widgets.upcoming_events.capacity'))
                    ->state(function (Event $record): string {
                        if (!$record->max_attendees) {
                            return __('widgets.upcoming_events.unlimited');
                        }
                        $booked = $record->bookings()
                            ->whereNotIn('status', ['cancelled'])
                            ->sum('quantity');
                        $pct = ($booked / $record->max_attendees) * 100;
                        return number_format($pct, 1) . '%';
                    })
                    ->color(function (Event $record): string {
                        if (!$record->max_attendees) {
                            return 'gray';
                        }
                        $booked = $record->bookings()
                            ->whereNotIn('status', ['cancelled'])
                            ->sum('quantity');
                        $pct = ($booked / $record->max_attendees) * 100;
                        return match (true) {
                            $pct >= 90 => 'danger',
                            $pct >= 70 => 'warning',
                            default    => 'success',
                        };
                    }),

                TextColumn::make('revenue')
                    ->label(__('widgets.upcoming_events.revenue'))
                    ->state(fn (Event $record): float =>
                        $record->bookings()
                            ->whereIn('status', ['confirmed', 'checked_in'])
                            ->sum('total_price')
                    )
                    ->money('OMR')
                    ->weight('bold')
                    ->color('success'),

                BadgeColumn::make('status')
                    ->label(__('widgets.upcoming_events.status'))
                    ->colors([
                        'success' => 'published',
                        'gray'    => 'draft',
                    ]),
            ])
            ->recordActions([
                Action::make('view_bookings')
                    ->label(__('widgets.upcoming_events.action_bookings'))
                    ->icon('heroicon-m-rectangle-stack')
                    ->url(fn (Event $record): string =>
                        route('filament.admin.resources.bookings.index') . '?tableFilters[event][value]=' . $record->id
                    ),

                Action::make('edit')
                    ->label(__('widgets.upcoming_events.action_edit'))
                    ->icon('heroicon-m-pencil-square')
                    ->url(fn (Event $record): string =>
                        route('filament.admin.resources.events.edit', $record)
                    ),
            ])
            ->paginated([5, 10, 25]);
    }
}
