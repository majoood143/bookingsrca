<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\BookingSetting;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

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
                    ->with(['event', 'ticketType', 'firstAttendee'])
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

                TextColumn::make('firstAttendee.phone')
                    ->label(__('widgets.balance_due_bookings.mobile'))
                    ->placeholder('—')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('event.title')
                    ->label(__('widgets.balance_due_bookings.event'))
                    ->searchable()
                    ->limit(25),

                TextColumn::make('event_date')
                    ->label(__('widgets.balance_due_bookings.event_date'))
                    ->date('M j, Y')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('widgets.balance_due_bookings.booking_date'))
                    ->dateTime('M j, Y g:i A')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('total_price')
                    ->label(__('widgets.balance_due_bookings.total'))
                    ->html()
                    ->searchable()
                    ->formatStateUsing(fn($state) => view('partials.currency-amount', ['amount' => (float) $state])->render())
                    ->sortable(),

                TextColumn::make('paid_amount')
                    ->label(__('widgets.balance_due_bookings.paid'))
                    ->html()
                    ->formatStateUsing(fn($state) => view('partials.currency-amount', ['amount' => (float) $state])->render())
                    ->color('success'),

                TextColumn::make('balance_due')
                    ->label(__('widgets.balance_due_bookings.balance_due'))
                    ->state(fn(Booking $record): float => max(0, (float) $record->total_price - (float) $record->paid_amount))
                    ->html()
                    ->formatStateUsing(fn($state) => view('partials.currency-amount', ['amount' => (float) $state])->render())
                    ->weight('bold')
                    ->color('danger'),

                BadgeColumn::make('status')
                    ->label(__('widgets.balance_due_bookings.status'))
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'confirmed',
                        'info'    => 'checked_in',
                    ]),

                BadgeColumn::make('source')
                    ->label(__('widgets.balance_due_bookings.source'))
                    ->formatStateUsing(fn($state) => __('booking.options.source')[$state] ?? $state)
                    ->sortable()
                    ->colors([
                        'info'    => 'online',
                        'gray'    => 'admin',
                        'success' => 'kiosk',
                    ]),
            ])
            ->filters([
                SelectFilter::make('source')
                    ->label(__('widgets.balance_due_bookings.source'))
                    ->options(__('booking.options.source'))
                    ->multiple(),
            ])
            ->recordActions([
                Action::make('record_payment')
                    ->label(__('widgets.balance_due_bookings.action_record_payment'))
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('payment_method')
                                ->label(__('booking.payments.fields.method'))
                                ->options([
                                    'cash'         => __('booking.payments.methods.cash'),
                                    'credit_debit' => __('booking.payments.methods.credit_debit'),
                                    'partial'      => __('booking.payments.methods.partial'),
                                ])
                                ->default('cash')
                                ->required()
                                ->native(false)
                                ->live(),

                            TextInput::make('amount')
                                ->label(__('booking.payments.fields.amount'))
                                ->numeric()
                                ->prefix(fn() => new HtmlString(view('partials.currency-label')->render()))
                                ->minValue(0.01)
                                ->required()
                                ->default(fn(Booking $record) => round($record->balance_due, 2))
                                ->helperText(fn(Booking $record) => __('booking.payments.summary.balance_due') . ': ' . BookingSetting::currencySymbol() . ' ' . number_format($record->balance_due, 2)),
                        ]),

                        TextInput::make('reference')
                            ->label(__('booking.payments.fields.reference'))
                            ->required(fn(Get $get) => $get('payment_method') === 'credit_debit')
                            ->maxLength(255),

                        Textarea::make('notes')
                            ->label(__('booking.payments.fields.notes'))
                            ->rows(2),
                    ])
                    ->action(function (Booking $record, array $data): void {
                        $record->payments()->create([
                            'payment_method' => $data['payment_method'],
                            'amount'         => $data['amount'],
                            'reference'      => $data['reference'] ?? null,
                            'notes'          => $data['notes'] ?? null,
                            'recorded_by'    => auth()->id(),
                        ]);

                        Notification::make()
                            ->success()
                            ->title(__('widgets.balance_due_bookings.payment_recorded'))
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading(__('widgets.balance_due_bookings.record_payment_heading'))
                    ->modalDescription(fn(Booking $record) => __('widgets.balance_due_bookings.record_payment_description', ['amount' => number_format($record->balance_due, 2)]))
                    ->modalSubmitActionLabel(__('widgets.balance_due_bookings.record_payment_submit')),

                Action::make('view')
                    ->label(__('widgets.balance_due_bookings.action_view'))
                    ->icon('heroicon-m-eye')
                    ->url(
                        fn(Booking $record): string =>
                        route('filament.admin.resources.bookings.view', $record)
                    ),
            ])
            ->paginated([10, 25])
            ->emptyStateHeading(__('widgets.balance_due_bookings.empty_heading'))
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
