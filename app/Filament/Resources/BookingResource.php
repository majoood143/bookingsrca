<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\HtmlString;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Size;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\BookingResource\Pages\ListBookings;
use App\Filament\Resources\BookingResource\Pages\CreateBooking;
use App\Filament\Resources\BookingResource\Pages\EditBooking;
use App\Filament\Resources\BookingResource\Pages\ViewBooking;
use App\Filament\Resources\BookingResource\Pages\ListBookingActivities;
use App\Filament\Resources\BookingResource\Pages;
use App\Models\Booking;
use App\Models\Event;
use App\Models\TimeSlot;
use App\Models\TicketType;
use App\Models\ExtraService;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\BookingSetting;
use Filament\Tables\Columns\Summarizers\Sum;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('booking.navigation.group');
    }

    public static function getModelLabel(): string
    {
        return __('booking.navigation.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('booking.navigation.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make(__('booking.sections.event_selection'))
                    ->schema([
                        Select::make('event_id')
                            ->label(__('booking.fields.event'))
                            ->relationship('event', 'title', fn($query) => $query->where('status', 'published'))
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->getTranslation('title', 'en'))
                            ->searchable(['title->en', 'title->ar'])
                            ->preload()
                            ->required()
                            ->native(false),

                        DatePicker::make('event_date')
                            ->label(__('booking.fields.event_date'))
                            ->required()
                            ->native(false)
                            ->displayFormat('Y-m-d'),

                        Select::make('time_slot_id')
                            ->label(__('booking.fields.time_slot'))
                            ->options(function (Get $get) {
                                if (!$get('event_id') || !$get('event_date')) return [];
                                return TimeSlot::where('event_id', $get('event_id'))
                                    ->where('is_active', true)
                                    ->where('date', $get('event_date'))
                                    ->get()
                                    ->mapWithKeys(fn($slot) => [
                                        $slot->id => $slot->getTimeRange() . ' (' . $slot->getRemainingCapacity() . ' available)'
                                    ]);
                            })
                            ->required()
                            ->native(false)
                            ->live(),

                        TextInput::make('quantity')
                            ->label(__('booking.fields.quantity'))
                            ->required()
                            ->numeric()
                            ->minValue(fn() => BookingSetting::get('min_tickets_per_booking', 1))
                            ->maxValue(fn() => BookingSetting::get('max_tickets_per_booking', 10))
                            ->default(fn() => BookingSetting::get('min_tickets_per_booking', 1))
                            ->live(onBlur: true)
                            ->helperText(fn() => __('booking.fields.quantity_helper', [
                                'min' => BookingSetting::get('min_tickets_per_booking', 1),
                                'max' => BookingSetting::get('max_tickets_per_booking', 10),
                            ]))
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                $quantity = (int) $state ?: 1;
                                $set('attendees_count', $quantity);
                                self::syncAttendees($set, $get, $quantity);
                                self::calculateTotal($set, $get);
                            })
                            ->helperText(__('booking.fields.quantity_description'))
                            ->columnSpan(1),

                        Hidden::make('attendees_count')
                            ->default(1),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make(__('booking.sections.extra_services'))
                    ->schema([
                        CheckboxList::make('extra_services')
                            ->label(__('booking.fields.extra_services'))
                            ->options(function (Get $get) {
                                if (!$get('event_id')) return [];
                                return ExtraService::where('event_id', $get('event_id'))
                                    ->where('is_active', true)
                                    ->get()
                                    ->mapWithKeys(fn($service) => [
                                        $service->id => $service->getTranslation('name', 'en') .
                                            ' - $' . number_format($service->price, 2)
                                    ]);
                            })
                            ->columns(2)
                            ->live()
                            ->afterStateUpdated(fn(Set $set, Get $get) => self::calculateTotal($set, $get))
                            ->visible(fn(Get $get) => filled($get('event_id'))),
                    ])
                    ->visible(fn(Get $get) => filled($get('event_id')))
                    ->collapsible()
                    ->collapsed(),

                Section::make(__('booking.sections.attendee_details'))
                    ->description(fn(Get $get) => __('booking.attendee_details_description', ['count' => (int) $get('quantity') ?: 1]))
                    ->schema([
                        Repeater::make('attendees')
                            ->label('')
                            ->id('attendees_repeater')
                            ->relationship('attendees')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('first_name')
                                            ->label(__('booking.fields.first_name'))
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder(__('booking.placeholders.first_name')),

                                        TextInput::make('last_name')
                                            ->label(__('booking.fields.last_name'))
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder(__('booking.placeholders.last_name')),
                                    ]),

                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('email')
                                            ->label(__('booking.fields.email'))
                                            ->email()
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder(__('booking.placeholders.email')),

                                        TextInput::make('phone')
                                            ->label(__('booking.fields.phone'))
                                            ->tel()
                                            ->maxLength(20)
                                            ->placeholder(__('booking.placeholders.phone')),
                                    ]),

                                Grid::make(3)
                                    ->schema([
                                        DatePicker::make('date_of_birth')
                                            ->label(__('booking.fields.date_of_birth'))
                                            ->native(false)
                                            ->maxDate(now())
                                            ->minDate(fn() => now()->subYears((int) BookingSetting::get('max_attendee_age_years', 75)))
                                            ->placeholder(__('booking.placeholders.date')),

                                        Select::make('gender')
                                            ->label(__('booking.fields.gender'))
                                            ->options(__('booking.options.gender'))
                                            ->native(false)
                                            ->placeholder(__('booking.placeholders.gender')),

                                        Select::make('nationality')
                                            ->label(__('booking.fields.nationality'))
                                            ->required()
                                            ->searchable()
                                            ->native(false)
                                            ->options(__('booking.options.nationality'))
                                            ->placeholder(__('booking.placeholders.nationality')),
                                    ]),

                                Grid::make(2)
                                    ->schema([
                                        Select::make('ticket_type_id')
                                            ->label(__('booking.fields.ticket_type'))
                                            ->options(function (Get $get) {
                                                $eventId = $get('../../event_id');
                                                if (!$eventId) return [];

                                                return TicketType::where('event_id', $eventId)
                                                    ->where('is_active', true)
                                                    ->get()
                                                    ->mapWithKeys(fn($ticket) => [
                                                        $ticket->id => $ticket->getTranslation('name', 'en') .
                                                            ' - OMR ' . number_format($ticket->price, 3) .
                                                            ' (' . $ticket->getRemainingQuantity() . ' available)'
                                                    ]);
                                            })
                                            ->required()
                                            ->native(false)
                                            ->live()
                                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                                if ($state) {
                                                    $ticketType = TicketType::find($state);
                                                    if ($ticketType) {
                                                        $set('ticket_price', $ticketType->price);
                                                    }
                                                } else {
                                                    $set('ticket_price', 0);
                                                }

                                                // Booking-level totals live two containers up
                                                // (repeater item -> repeater -> form root).
                                                self::calculateTotal($set, $get, '../../');
                                            })
                                            ->visible(fn(Get $get) => filled($get('../../event_id')))
                                            ->dehydratedWhenHidden()
                                            ->helperText(__('booking.ticket_type_helper')),

                                        TextInput::make('ticket_price')
                                            ->label(__('booking.fields.ticket_price'))
                                            ->numeric()
                                            ->prefix('OMR')
                                            ->disabled()
                                            ->dehydrated(true)
                                            ->default(0)
                                            ->live()
                                            ->visible(fn(Get $get) => filled($get('ticket_type_id'))),
                                    ]),

                                Placeholder::make('ticket_info')
                                    ->label(__('booking.fields.ticket_info'))
                                    ->content(function ($record) {
                                        if (!$record) {
                                            return new HtmlString("
                                <div class='text-sm text-gray-500 italic'>
                                    " . __('booking.ticket_info.will_generate') . "
                                </div>
                            ");
                                        }

                                        $emailStatus = $record->email_sent
                                            ? '<span class="text-green-600">✓ ' . __('booking.ticket_info.sent_on', ['date' => $record->email_sent_at->format('M d, H:i')]) . '</span>'
                                            : '<span class="text-gray-500">' . __('booking.ticket_info.not_sent') . '</span>';

                                        $checkinStatus = $record->checked_in
                                            ? '<span class="text-blue-600">✓ ' . __('booking.ticket_info.checked_in_on', ['date' => $record->checked_in_at->format('M d, H:i')]) . '</span>'
                                            : '<span class="text-gray-500">' . __('booking.ticket_info.not_checked_in') . '</span>';

                                        return new HtmlString("
                            <div class='space-y-2 text-sm'>
                                <div class='flex items-center gap-2'>
                                    <span class='font-semibold'>" . __('booking.ticket_info.ticket_number') . "</span>
                                    <code class='bg-gray-100 px-2 py-1 rounded'>{$record->ticket_number}</code>
                                </div>
                                <div class='flex items-center gap-2'>
                                    <span class='font-semibold'>" . __('booking.ticket_info.email_label') . "</span>
                                    {$emailStatus}
                                </div>
                                <div class='flex items-center gap-2'>
                                    <span class='font-semibold'>" . __('booking.ticket_info.checkin_label') . "</span>
                                    {$checkinStatus}
                                </div>
                            </div>
                        ");
                                    })
                                    ->visible(fn($record) => $record !== null),
                            ])
                            ->itemLabel(
                                fn(array $state): ?string =>
                                !empty($state['first_name']) && !empty($state['last_name'])
                                    ? '👤 ' . $state['first_name'] . ' ' . $state['last_name']
                                    : (isset($state['id'])
                                        ? __('booking.attendee_id', ['id' => $state['id']])
                                        : __('booking.attendee_new'))
                            )
                            ->defaultItems(function (Get $get, $record) {
                                if ($record) {
                                    return count($record->attendees ?? []);
                                }
                                return (int) $get('quantity') ?: 1;
                            })
                            ->columnSpanFull()
                            ->live()
                            ->cloneable(false),
                    ])
                    ->visible(fn(Get $get) => filled($get('quantity')) && (int) $get('quantity') > 0)
                    ->collapsible(),


                Section::make(__('booking.sections.pricing'))
                    ->schema([
                        TextInput::make('ticket_price')
                            ->label(__('booking.fields.ticket_price'))
                            ->numeric()
                            ->prefix('OMR')
                            ->disabled()
                            ->dehydrated()
                            ->live()
                            ->default(0),

                        TextInput::make('services_price')
                            ->label(__('booking.fields.services_price'))
                            ->numeric()
                            ->prefix('OMR')
                            ->disabled()
                            ->dehydrated()
                            ->live()
                            ->default(0),

                        TextInput::make('total_price')
                            ->label(__('booking.fields.total_price'))
                            ->numeric()
                            ->prefix('OMR')
                            ->disabled()
                            ->dehydrated()
                            ->live()
                            ->default(0),
                    ])
                    ->columns(3)
                    ->collapsible(),

                Section::make(__('booking.sections.booking_settings'))
                    ->schema([
                        TextInput::make('booking_reference')
                            ->label(__('booking.fields.booking_reference'))
                            ->disabled()
                            ->dehydrated()
                            ->visible(fn($record) => $record !== null),

                        Select::make('source')
                            ->label(__('booking.fields.source'))
                            ->options(__('booking.options.source'))
                            ->disabled()
                            ->dehydrated(false)
                            ->native(false)
                            ->visible(fn($record) => $record !== null),

                        TextInput::make('created_by')
                            ->label(__('booking.fields.created_by'))
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(fn($record) => $record?->createdBy?->name ?? __('booking.fields.created_by_online'))
                            ->visible(fn($record) => $record !== null && $record->source === 'admin'),

                        Select::make('status')
                            ->label(__('booking.fields.status'))
                            ->options(__('booking.options.status'))
                            ->default('pending')
                            ->required()
                            ->native(false)
                            ->visible(fn($record) => $record !== null),
                    ])
                    ->columns(2)
                    ->visible(fn($record) => $record !== null)
                    ->collapsible(),

                Section::make(__('booking.payments.title'))
                    ->schema([
                        TextInput::make('total_paid')
                            ->label(__('booking.payments.summary.total_paid'))
                            ->prefix('OMR')
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(fn($record) => number_format($record?->total_paid ?? 0, 2)),

                        TextInput::make('balance_due')
                            ->label(__('booking.payments.summary.balance_due'))
                            ->prefix('OMR')
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(fn($record) => number_format($record?->balance_due ?? 0, 2)),
                    ])
                    ->columns(2)
                    ->visible(fn($record) => $record !== null)
                    ->collapsible(),
            ])
            ->columns(1);
    }


    protected static function syncAttendees(Set $set, Get $get, int $quantity): void
    {
        $quantity = max(1, $quantity);
        $attendees = $get('attendees') ?? [];
        $count = count($attendees);

        if ($count < $quantity) {
            for ($i = $count; $i < $quantity; $i++) {
                $attendees[] = [];
            }
        } elseif ($count > $quantity) {
            $attendees = array_slice($attendees, 0, $quantity);
        }

        $set('attendees', $attendees);
    }

    protected static function calculateTotal(Set $set, Get $get, string $prefix = ''): void
    {
        $ticketPrice = 0;
        $servicesPrice = 0;
        $quantity = (int) $get($prefix . 'quantity') ?: 1;

        $attendees = $get($prefix . 'attendees') ?? [];
        foreach ($attendees as $attendee) {
            if (isset($attendee['ticket_price'])) {
                $ticketPrice += (float) $attendee['ticket_price'];
            }
        }

        if ($get($prefix . 'extra_services')) {
            $serviceIds = is_array($get($prefix . 'extra_services')) ? $get($prefix . 'extra_services') : [];
            $services = ExtraService::whereIn('id', $serviceIds)->get();
            foreach ($services as $service) {
                $servicesPrice += $service->price * $quantity;
            }
        }

        $set($prefix . 'ticket_price', number_format($ticketPrice, 2, '.', ''));
        $set($prefix . 'services_price', number_format($servicesPrice, 2, '.', ''));
        $set($prefix . 'total_price', number_format($ticketPrice + $servicesPrice, 2, '.', ''));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('booking_reference')
                    ->label(__('booking.columns.reference'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),

                TextColumn::make('attendees_count')
                    ->counts('attendees')
                    ->label(__('booking.columns.attendees'))
                    ->badge()
                    ->color('primary')
                    ->summarize(Sum::make()),

                TextColumn::make('firstAttendee.phone')
                    ->label(__('booking.columns.attendee_phone'))
                    ->placeholder('—')
                    ->searchable()
                    ->copyable()
                    ->badge()
                    ->color('primary')
                    ->toggleable(),

                TextColumn::make('firstAttendee.identity_number')
                    ->label(__('booking.columns.attendee_identity'))
                    ->placeholder('—')
                    ->searchable()
                    ->toggleable()
                    ->visible(fn () => (bool) BookingSetting::get('show_identity_number', true)),

                TextColumn::make('firstAttendee.email')
                    ->label(__('booking.columns.attendee_email'))
                    ->placeholder('—')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('event.title')
                    ->label(__('booking.columns.event'))
                    ->getStateUsing(fn($record) => $record->event->getTranslation('title', app()->getLocale()))
                    ->searchable()
                    ->wrap()
                    ->limit(30),

                TextColumn::make('event_date')
                    ->label(__('booking.columns.date'))
                    ->date('M d, Y')
                    ->sortable(),

                TextColumn::make('timeSlot.time_range')
                    ->label(__('booking.columns.time'))
                    ->getStateUsing(fn($record) => $record->timeSlot->getTimeRange())
                    ->badge()
                    ->color('info'),

                // TextColumn::make('quantity')
                //     ->label(__('booking.columns.quantity'))
                //     ->alignCenter(),

                TextColumn::make('total_price')
                    ->label(__('booking.columns.total'))
                    ->money('OMR')
                    ->weight('bold')
                    ->color('success')
                    ->summarize(Sum::make()),

                BadgeColumn::make('status')
                    ->label(__('booking.columns.status'))
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'confirmed',
                        'danger'  => 'cancelled',
                        'primary' => 'checked_in',
                    ]),

                BadgeColumn::make('source')
                    ->label(__('booking.columns.source'))
                    ->formatStateUsing(fn($state) => __('booking.options.source')[$state] ?? $state)
                    ->colors([
                        'info'    => 'online',
                        'gray'    => 'admin',
                        'success' => 'kiosk',
                    ]),

                TextColumn::make('createdBy.name')
                    ->label(__('booking.columns.created_by'))
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(__('booking.columns.booked_at'))
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label(__('booking.fields.status'))
                    ->options(__('booking.options.status'))
                    ->multiple(),

                SelectFilter::make('source')
                    ->label(__('booking.fields.source'))
                    ->options(__('booking.options.source'))
                    ->multiple(),

                SelectFilter::make('event')
                    ->label(__('booking.fields.event'))
                    ->relationship('event', 'title')
                    ->getOptionLabelFromRecordUsing(fn($record) => $record->getTranslation('title', 'en'))
                    ->searchable()
                    ->preload()
                    ->multiple(),

                Filter::make('event_date')
                    ->label(__('booking.fields.event_date'))
                    ->schema([
                        DatePicker::make('event_date_from')
                            ->label(__('booking.filters.event_date_from'))
                            ->native(false),
                        DatePicker::make('event_date_until')
                            ->label(__('booking.filters.event_date_until'))
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['event_date_from'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('event_date', '>=', $date),
                            )
                            ->when(
                                $data['event_date_until'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('event_date', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['event_date_from'] ?? null) {
                            $indicators[] = __('booking.filters.event_date_from') . ' ' . $data['event_date_from'];
                        }

                        if ($data['event_date_until'] ?? null) {
                            $indicators[] = __('booking.filters.event_date_until') . ' ' . $data['event_date_until'];
                        }

                        return $indicators;
                    }),

                SelectFilter::make('time_slot_id')
                    ->label(__('booking.fields.time_slot'))
                    ->options(function ($livewire) {
                        $eventDateFilter = $livewire->getTableFilterState('event_date') ?? [];
                        $from = $eventDateFilter['event_date_from'] ?? null;
                        $until = $eventDateFilter['event_date_until'] ?? null;

                        return TimeSlot::query()
                            ->when($from, fn($query, $date) => $query->whereDate('date', '>=', $date))
                            ->when($until, fn($query, $date) => $query->whereDate('date', '<=', $date))
                            ->orderBy('date')
                            ->orderBy('start_time')
                            ->get()
                            ->mapWithKeys(fn($slot) => [
                                $slot->id => $slot->date->format('Y-m-d') . ' - ' . $slot->getTimeRange(),
                            ])
                            ->toArray();
                    })
                    ->searchable()
                    ->multiple(),

                Filter::make('booked_at')
                    ->label(__('booking.filters.booked_at'))
                    ->schema([
                        DatePicker::make('booked_at_from')
                            ->label(__('booking.filters.booked_date_from'))
                            ->native(false),
                        DatePicker::make('booked_at_until')
                            ->label(__('booking.filters.booked_date_until'))
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['booked_at_from'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['booked_at_until'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['booked_at_from'] ?? null) {
                            $indicators[] = __('booking.filters.booked_date_from') . ' ' . $data['booked_at_from'];
                        }

                        if ($data['booked_at_until'] ?? null) {
                            $indicators[] = __('booking.filters.booked_date_until') . ' ' . $data['booked_at_until'];
                        }

                        return $indicators;
                    }),

            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),

                    Action::make('resend_tickets')
                        ->label(__('booking.actions.resend_tickets'))
                        ->icon('heroicon-o-paper-airplane')
                        ->color('success')
                        ->action(function (Booking $record) {
                            if ($record->sendAllTickets()) {
                                Notification::make()
                                    ->success()
                                    ->title(__('booking.notifications.tickets_sent'))
                                    ->body(__('booking.notifications.tickets_sent_body', ['count' => $record->attendees->count()]))
                                    ->send();
                            } else {
                                Notification::make()
                                    ->danger()
                                    ->title(__('booking.notifications.tickets_partial'))
                                    ->body(__('booking.notifications.tickets_partial_body'))
                                    ->send();
                            }
                        })
                        ->requiresConfirmation()
                        ->modalHeading(__('booking.modals.resend_tickets_heading'))
                        ->modalDescription(
                            fn($record) => __('booking.modals.resend_tickets_description', ['count' => $record->attendees->count()])
                        )
                        ->modalSubmitActionLabel(__('booking.modals.resend_tickets_submit'))
                        ->visible(fn(Booking $record) => in_array($record->status, ['confirmed', 'checked_in']))
                        ->tooltip(__('booking.tooltips.resend_tickets')),

                    Action::make('download_pdf')
                        ->label(__('booking.actions.download_pdf'))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('info')
                        ->action(function (Booking $record) {
                            return response()->streamDownload(
                                function () use ($record) {
                                    echo $record->generateSummaryPdf();
                                },
                                'booking-' . $record->booking_reference . '.pdf'
                            );
                        })
                        ->disabled(fn(Booking $record) => $record->status === 'cancelled')
                        ->tooltip(__('booking.tooltips.download_pdf')),

                    Action::make('print_receipt')
                        ->label(__('booking.actions.print_receipt'))
                        ->icon('heroicon-o-printer')
                        ->color('gray')
                        ->url(fn(Booking $record) => route('bookings.receipt', $record))
                        ->openUrlInNewTab()
                        ->disabled(fn(Booking $record) => $record->status === 'cancelled')
                        ->tooltip(__('booking.tooltips.print_receipt')),

                    Action::make('print_tickets')
                        ->label(__('booking.actions.print_tickets'))
                        ->icon('heroicon-o-ticket')
                        ->color('gray')
                        ->url(fn(Booking $record) => route('bookings.attendee-tickets', $record))
                        ->openUrlInNewTab()
                        ->disabled(fn(Booking $record) => $record->status === 'cancelled')
                        ->tooltip(__('booking.tooltips.print_tickets')),

                    Action::make('view_attendees')
                        ->label(__('booking.actions.view_attendees'))
                        ->icon('heroicon-o-users')
                        ->color('info')
                        ->modalHeading(fn($record) => __('booking.actions.view_attendees') . ' - ' . $record->booking_reference)
                        ->modalContent(function ($record) {
                            return view('filament.modals.booking-attendees-wrapper', [
                                'booking' => $record
                            ]);
                        })
                        ->modalWidth('5xl')
                        ->slideOver(),

                    Action::make('view_agent_details')
                        ->label(__('booking.actions.view_agent_details'))
                        ->icon('heroicon-o-device-phone-mobile')
                        ->color('gray')
                        ->modalHeading(fn($record) => __('booking.actions.view_agent_details') . ' - ' . $record->booking_reference)
                        ->modalContent(fn($record) => view('filament.modals.booking-agent-details', [
                            'booking' => $record,
                            'device'  => $record->getDeviceInfo(),
                        ]))
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel(__('booking.actions.close'))
                        ->modalWidth('lg')
                        ->tooltip(__('booking.tooltips.view_agent_details'))
                        ->slideOver(),

                    Action::make('view_gateway_logs')
                        ->label(__('booking.actions.view_gateway_logs'))
                        ->icon('heroicon-o-shield-exclamation')
                        ->color('gray')
                        ->modalHeading(fn($record) => __('booking.actions.view_gateway_logs') . ' - ' . $record->booking_reference)
                        ->modalContent(fn($record) => view('filament.modals.payment-gateway-logs', [
                            'logs' => $record->gatewayLogs,
                        ]))
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel(__('booking.actions.close'))
                        ->modalWidth('5xl')
                        ->visible(function () {
                            /** @var \App\Models\User|null $user */
                            $user = \Illuminate\Support\Facades\Auth::user();
                            return $user?->hasRole('super_admin') ?? false;
                        })
                        ->tooltip(__('booking.tooltips.view_gateway_logs'))
                        ->slideOver(),

                    Action::make('confirm')
                        ->label(__('booking.actions.confirm'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Booking $record) {
                            $record->confirm();
                            Notification::make()
                                ->success()
                                ->title(__('booking.notifications.booking_confirmed'))
                                ->body(__('booking.notifications.booking_confirmed_body'))
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->visible(fn(Booking $record) => $record->status === 'pending'),

                    Action::make('cancel')
                        ->label(__('booking.actions.cancel'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function (Booking $record) {
                            $record->cancel();
                            Notification::make()
                                ->success()
                                ->title(__('booking.notifications.booking_cancelled'))
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->visible(fn(Booking $record) => in_array($record->status, ['pending', 'confirmed'])),
                ])
                    ->label(__('booking.actions.more_actions'))
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size(Size::Small)
                    ->color('primary')
                    ->button(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('confirm')
                        ->label(__('booking.actions.confirm_selected'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if ($record->status === 'pending') {
                                    $record->confirm();
                                }
                            });
                            Notification::make()
                                ->success()
                                ->title(__('booking.notifications.bookings_confirmed'))
                                ->send();
                        })
                        ->requiresConfirmation(),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'      => ListBookings::route('/'),
            'create'     => CreateBooking::route('/create'),
            'edit'       => EditBooking::route('/{record}/edit'),
            'view'       => ViewBooking::route('/{record}'),
            'activities' => ListBookingActivities::route('/{record}/activities'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            BookingResource\RelationManagers\PaymentsRelationManager::class,
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'booking_reference',
            'attendees.phone',
            'attendees.email',
            'attendees.identity_number',
        ];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->booking_reference;
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        $attendee = $record->firstAttendee;

        return [
            __('booking.fields.event') => $record->event?->getTranslation('title', app()->getLocale()),
            __('booking.fields.phone') => $attendee?->phone,
            __('booking.fields.email') => $attendee?->email,
            __('booking.fields.identity_number') => $attendee?->identity_number,
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['event', 'firstAttendee']);
    }
}
