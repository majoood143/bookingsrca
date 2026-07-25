<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Actions\Action;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class BalanceDueBookingsWidget extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 6;

    protected int | string | array $columnSpan = 'full';

    protected function getTableHeading(): string
    {
        return __('widgets.balance_due_bookings.heading');
    }

    protected function paidAmountSubquery(): \Illuminate\Database\Query\Builder
    {
        return DB::table('booking_payments')
            ->selectRaw('COALESCE(SUM(amount), 0)')
            ->whereColumn('booking_payments.booking_id', 'bookings.id');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Booking::query()
                    ->with(['event', 'ticketType'])
                    ->whereIn('status', ['confirmed', 'checked_in'])
                    ->whereRaw('total_price > (' . $this->paidAmountSubquery()->toSql() . ')')
                    ->addSelect('bookings.*')
                    ->selectSub($this->paidAmountSubquery(), 'paid_amount')
                    ->orderByRaw('(total_price - (' . $this->paidAmountSubquery()->toSql() . ')) desc')
            )
            ->columns([
                TextColumn::make('booking_reference')
                    ->label(__('widgets.balance_due_bookings.reference'))
                    ->weight('bold')
                    ->copyable()
                    ->searchable(),

                TextColumn::make('event.title')
                    ->label(__('widgets.balance_due_bookings.event'))
                    ->searchable()
                    ->limit(25),

                TextColumn::make('event_date')
                    ->label(__('widgets.balance_due_bookings.event_date'))
                    ->date('M j, Y')
                    ->sortable(),

                TextColumn::make('total_price')
                    ->label(__('widgets.balance_due_bookings.total'))
                    ->money('OMR')
                    ->sortable(),

                TextColumn::make('paid_amount')
                    ->label(__('widgets.balance_due_bookings.paid'))
                    ->money('OMR')
                    ->color('success'),

                TextColumn::make('balance_due')
                    ->label(__('widgets.balance_due_bookings.balance_due'))
                    ->state(fn (Booking $record): float => max(0, (float) $record->total_price - (float) $record->paid_amount))
                    ->money('OMR')
                    ->weight('bold')
                    ->color('danger'),

                BadgeColumn::make('status')
                    ->label(__('widgets.balance_due_bookings.status'))
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'confirmed',
                        'info'    => 'checked_in',
                    ]),
            ])
            ->recordActions([
                Action::make('view')
                    ->label(__('widgets.balance_due_bookings.action_view'))
                    ->icon('heroicon-m-eye')
                    ->url(fn (Booking $record): string =>
                        route('filament.admin.resources.bookings.view', $record)
                    ),
            ])
            ->paginated([10, 25])
            ->emptyStateHeading(__('widgets.balance_due_bookings.empty_heading'))
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
