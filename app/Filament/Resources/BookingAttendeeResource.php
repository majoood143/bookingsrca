<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Size;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\BulkAction;
use App\Filament\Resources\BookingAttendeeResource\Pages\ListBookingAttendees;
use App\Filament\Resources\BookingAttendeeResource\Pages\ListBookingAttendeeActivities;
use App\Filament\Resources\BookingAttendeeResource\Pages\ViewBookingAttendee;
use App\Filament\Resources\BookingAttendeeResource\Pages;
use App\Models\BookingAttendee;
use App\Models\Event;
use App\Models\TicketType;
use App\Models\TimeSlot;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use App\Exports\BookingAttendeesExport;
use Mpdf\Tag\Columns;

class BookingAttendeeResource extends Resource
{
    protected static ?string $model = BookingAttendee::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('booking_attendee.navigation.group');
    }

    public static function getModelLabel(): string
    {
        return __('booking_attendee.navigation.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('booking_attendee.navigation.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('booking_attendee.sections.attendee_info'))
                    ->icon('heroicon-o-user')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('first_name')
                            ->label(__('booking_attendee.fields.first_name')),

                        TextEntry::make('last_name')
                            ->label(__('booking_attendee.fields.last_name')),

                        TextEntry::make('email')
                            ->label(__('booking_attendee.fields.email'))
                            ->copyable()
                            ->icon('heroicon-o-envelope'),

                        TextEntry::make('phone')
                            ->label(__('booking_attendee.fields.phone'))
                            ->placeholder('—'),

                        TextEntry::make('date_of_birth')
                            ->label(__('booking_attendee.fields.date_of_birth'))
                            ->date('M d, Y')
                            ->placeholder('—'),

                        TextEntry::make('gender')
                            ->label(__('booking_attendee.fields.gender'))
                            ->formatStateUsing(fn($state) => $state ? ucfirst($state) : '—'),

                        TextEntry::make('nationality')
                            ->label(__('booking_attendee.fields.nationality'))
                            ->placeholder('—'),

                        TextEntry::make('identity_number')
                            ->label(__('booking_attendee.fields.identity_number'))
                            ->placeholder('—'),
                    ]),

                Section::make(__('booking_attendee.sections.ticket_info'))
                    ->icon('heroicon-o-ticket')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('ticket_number')
                            ->label(__('booking_attendee.fields.ticket_number'))
                            ->copyable()
                            ->weight('bold')
                            ->color('primary'),

                        TextEntry::make('ticketType.name')
                            ->label(__('booking_attendee.fields.ticket_type'))
                            ->getStateUsing(fn($record) => $record->ticketType
                                ? $record->ticketType->getTranslation('name', app()->getLocale())
                                : '—'),

                        TextEntry::make('ticket_price')
                            ->label(__('booking_attendee.fields.ticket_price'))
                            ->money('OMR')
                            ->color('success'),

                        IconEntry::make('email_sent')
                            ->label(__('booking_attendee.fields.email_sent'))
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger'),

                        TextEntry::make('email_sent_at')
                            ->label(__('booking_attendee.fields.email_sent_at'))
                            ->dateTime('M d, Y H:i')
                            ->placeholder('—'),

                        IconEntry::make('checked_in')
                            ->label(__('booking_attendee.fields.checked_in'))
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('warning'),

                        TextEntry::make('checked_in_at')
                            ->label(__('booking_attendee.fields.checked_in_at'))
                            ->dateTime('M d, Y H:i')
                            ->placeholder('—'),

                        ImageEntry::make('qr_code')
                            ->label(__('booking_attendee.fields.qr_code'))
                            ->disk('public')
                            ->height(120)
                            ->visible(fn($record) => filled($record->qr_code)),
                    ]),

                Section::make(__('booking_attendee.sections.booking_info'))
                    ->icon('heroicon-o-rectangle-stack')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('booking.booking_reference')
                            ->label(__('booking_attendee.fields.booking_reference'))
                            ->copyable()
                            ->weight('bold')
                            ->url(fn($record) => BookingResource::getUrl('view', ['record' => $record->booking_id])),

                        TextEntry::make('booking.event.title')
                            ->label(__('booking_attendee.fields.event'))
                            ->getStateUsing(fn($record) => $record->booking->event->getTranslation('title', app()->getLocale())),

                        TextEntry::make('booking.event_date')
                            ->label(__('booking_attendee.fields.event_date'))
                            ->date('M d, Y'),

                        TextEntry::make('booking.timeSlot.time_range')
                            ->label(__('booking_attendee.fields.time_slot'))
                            ->getStateUsing(fn($record) => $record->booking->timeSlot->getTimeRange())
                            ->badge()
                            ->color('info'),

                        TextEntry::make('booking.status')
                            ->label(__('booking_attendee.fields.booking_status'))
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'pending'    => 'warning',
                                'confirmed'  => 'success',
                                'cancelled'  => 'danger',
                                'checked_in' => 'primary',
                                default      => 'gray',
                            })
                            ->formatStateUsing(fn($state) => ucfirst(str_replace('_', ' ', $state))),
                    ]),

                Section::make(__('booking_attendee.sections.extra_services'))
                    ->icon('heroicon-o-sparkles')
                    ->schema([
                        RepeatableEntry::make('booking.extraServices')
                            ->label('')
                            ->schema([
                                TextEntry::make('name')
                                    ->label(__('booking_attendee.fields.service_name'))
                                    ->getStateUsing(fn($record) => $record->getTranslation('name', app()->getLocale())),

                                TextEntry::make('pivot.quantity')
                                    ->label(__('booking_attendee.fields.service_quantity'))
                                    ->badge()
                                    ->color('primary'),

                                TextEntry::make('pivot.price')
                                    ->label(__('booking_attendee.fields.service_price'))
                                    ->money('USD')
                                    ->color('success'),
                            ])
                            ->columns(3)
                            ->contained(false),
                    ])
                    ->collapsible()
                    ->visible(fn($record) => $record->booking->extraServices->isNotEmpty()),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('full_name')
                    ->label(__('booking_attendee.columns.name'))
                    ->getStateUsing(fn($record) => $record->getFullName())
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['first_name'])
                    ->weight('semibold'),

                TextColumn::make('phone')
                    ->label(__('booking_attendee.columns.phone'))
                    ->description(fn($record) => $record->email)
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-o-envelope'),

                TextColumn::make('ticket_number')
                    ->label(__('booking_attendee.columns.ticket_number'))
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('booking.booking_reference')
                    ->label(__('booking_attendee.columns.booking_reference'))
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('booking.event.title')
                    ->label(__('booking_attendee.columns.event'))
                    ->getStateUsing(fn($record) => $record->booking->event->getTranslation('title', app()->getLocale()))
                    ->searchable()
                    ->wrap()
                    ->limit(30),

                TextColumn::make('booking.event_date')
                    ->label(__('booking_attendee.columns.event_date'))
                    ->date('M d, Y')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('booking.timeSlot.time_range')
                    ->label(__('booking_attendee.columns.time'))
                    ->getStateUsing(fn($record) => $record->booking->timeSlot?->getTimeRange())
                    ->badge()
                    //->searchable()
                    ->color('info'),

                TextColumn::make('ticketType.name')
                    ->label(__('booking_attendee.columns.ticket_type'))
                    ->getStateUsing(fn($record) => $record->ticketType
                        ? $record->ticketType->getTranslation('name', app()->getLocale())
                        : '—')
                    ->badge()
                    ->color('info'),

                IconColumn::make('email_sent')
                    ->label(__('booking_attendee.columns.email_sent'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->alignCenter(),

                IconColumn::make('checked_in')
                    ->label(__('booking_attendee.columns.checked_in'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->alignCenter(),

                TextColumn::make('booking.booking_reference')
                    ->label(__('booking_attendee.columns.booking_reference'))
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('booking.status')
                    ->label(__('booking_attendee.columns.booking_status'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending'    => 'warning',
                        'confirmed'  => 'success',
                        'cancelled'  => 'danger',
                        'checked_in' => 'primary',
                        default      => 'gray',
                    })
                    ->formatStateUsing(fn($state) => ucfirst(str_replace('_', ' ', $state)))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(__('booking_attendee.columns.created_at'))
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->label(__('booking_attendee.filters.event'))
                    ->options(
                        fn() => Event::query()
                            ->get()
                            ->mapWithKeys(fn($event) => [$event->id => $event->getTranslation('title', 'en')])
                            ->toArray()
                    )
                    ->query(function (Builder $query, array $data) {
                        $values = (array) ($data['values'] ?? []);
                        if (!empty($values)) {
                            $query->whereHas('booking', fn($q) => $q->whereIn('event_id', $values));
                        }
                    })
                    ->searchable()
                    ->multiple(),

                Filter::make('event_date')
                    ->label(__('booking_attendee.filters.event_date'))
                    ->schema([
                        DatePicker::make('event_date_from')
                            ->label(__('booking_attendee.filters.date_from'))
                            ->native(false),
                        DatePicker::make('event_date_until')
                            ->label(__('booking_attendee.filters.date_until'))
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['event_date_from'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereHas('booking', fn($q) => $q->whereDate('event_date', '>=', $date)),
                            )
                            ->when(
                                $data['event_date_until'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereHas('booking', fn($q) => $q->whereDate('event_date', '<=', $date)),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['event_date_from'] ?? null) {
                            $indicators[] = __('booking_attendee.filters.date_from') . ' ' . $data['event_date_from'];
                        }

                        if ($data['event_date_until'] ?? null) {
                            $indicators[] = __('booking_attendee.filters.date_until') . ' ' . $data['event_date_until'];
                        }

                        return $indicators;
                    }),

                    

                SelectFilter::make('time_slot')
                    ->label(__('booking_attendee.filters.time_slot'))
                    ->options(function ($livewire) {
                        $eventDateFilter = $livewire->getTableFilterState('event_date') ?? [];

                        return TimeSlot::query()
                            ->when(
                                $eventDateFilter['event_date_from'] ?? null,
                                fn($query, $date) => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $eventDateFilter['event_date_until'] ?? null,
                                fn($query, $date) => $query->whereDate('date', '<=', $date),
                            )
                            ->orderBy('date')
                            ->orderBy('start_time')
                            ->get()
                            ->mapWithKeys(fn($slot) => [$slot->id => $slot->date->format('Y-m-d') . ' - ' . $slot->getTimeRange()])
                            ->toArray();
                    })
                    ->query(function (Builder $query, array $data) {
                        $values = (array) ($data['values'] ?? []);
                        if (!empty($values)) {
                            $query->whereHas('booking', fn($q) => $q->whereIn('time_slot_id', $values));
                        }
                    })
                    ->searchable()
                    ->multiple(),

                SelectFilter::make('ticket_type')
                    ->label(__('booking_attendee.filters.ticket_type'))
                    ->options(
                        fn() => TicketType::query()
                            ->get()
                            ->mapWithKeys(fn($ticketType) => [$ticketType->id => $ticketType->getTranslation('name', 'en')])
                            ->toArray()
                    )
                    ->query(function (Builder $query, array $data) {
                        $values = (array) ($data['values'] ?? []);
                        if (!empty($values)) {
                            $query->whereIn('ticket_type_id', $values);
                        }
                    })
                    ->searchable()
                    ->multiple(),

                SelectFilter::make('booking_status')
                    ->label(__('booking_attendee.filters.booking_status'))
                    ->options([
                        'pending'    => 'Pending',
                        'confirmed'  => 'Confirmed',
                        'cancelled'  => 'Cancelled',
                        'checked_in' => 'Checked In',
                    ])
                    ->query(function (Builder $query, array $data) {
                        $values = (array) ($data['values'] ?? []);
                        if (!empty($values)) {
                            $query->whereHas('booking', fn($q) => $q->whereIn('status', $values));
                        }
                    })
                    ->multiple(),

                TernaryFilter::make('checked_in')
                    ->label(__('booking_attendee.filters.checked_in'))
                    ->trueLabel('Checked In')
                    ->falseLabel('Not Checked In'),

                TernaryFilter::make('email_sent')
                    ->label(__('booking_attendee.filters.email_sent'))
                    ->trueLabel('Email Sent')
                    ->falseLabel('Email Pending'),

            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),

                    Action::make('resend_ticket')
                        ->label(__('booking_attendee.actions.resend_ticket'))
                        ->icon('heroicon-o-paper-airplane')
                        ->color('success')
                        ->action(function (BookingAttendee $record) {
                            if ($record->sendTicketEmail()) {
                                Notification::make()
                                    ->success()
                                    ->title(__('booking_attendee.notifications.ticket_resent'))
                                    ->body(__('booking_attendee.notifications.ticket_resent_body', ['email' => $record->email]))
                                    ->send();
                            } else {
                                Notification::make()
                                    ->danger()
                                    ->title(__('booking_attendee.notifications.ticket_resend_failed'))
                                    ->body(__('booking_attendee.notifications.ticket_resend_failed_body'))
                                    ->send();
                            }
                        })
                        ->requiresConfirmation()
                        ->modalHeading(__('booking_attendee.modals.resend_heading'))
                        ->modalDescription(fn(BookingAttendee $record) => __('booking_attendee.modals.resend_description', ['email' => $record->email]))
                        ->modalSubmitActionLabel(__('booking_attendee.modals.resend_submit'))
                        ->tooltip(__('booking_attendee.tooltips.resend_ticket')),

                    Action::make('download_ticket')
                        ->label(__('booking_attendee.actions.download_ticket'))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('info')
                        ->url(fn(BookingAttendee $record) => $record->getPdfUrl())
                        ->openUrlInNewTab()
                        ->visible(fn(BookingAttendee $record) => filled($record->pdf_path))
                        ->tooltip(__('booking_attendee.tooltips.download_ticket')),

                    Action::make('check_in')
                        ->label(fn(BookingAttendee $record) => $record->checked_in
                            ? __('booking_attendee.actions.undo_check_in')
                            : __('booking_attendee.actions.check_in'))
                        ->icon(fn(BookingAttendee $record) => $record->checked_in
                            ? 'heroicon-o-arrow-uturn-left'
                            : 'heroicon-o-check-badge')
                        ->color(fn(BookingAttendee $record) => $record->checked_in ? 'warning' : 'primary')
                        ->action(function (BookingAttendee $record) {
                            if ($record->checked_in) {
                                $record->update(['checked_in' => false, 'checked_in_at' => null]);
                                Notification::make()
                                    ->warning()
                                    ->title(__('booking_attendee.notifications.check_in_undone'))
                                    ->body(__('booking_attendee.notifications.check_in_undone_body', ['name' => $record->getFullName()]))
                                    ->send();
                            } else {
                                $record->checkIn();
                                Notification::make()
                                    ->success()
                                    ->title(__('booking_attendee.notifications.checked_in'))
                                    ->body(__('booking_attendee.notifications.checked_in_body', ['name' => $record->getFullName()]))
                                    ->send();
                            }
                        })
                        ->requiresConfirmation()
                        ->modalHeading(__('booking_attendee.modals.check_in_heading'))
                        ->modalDescription(fn(BookingAttendee $record) => __('booking_attendee.modals.check_in_description', ['name' => $record->getFullName()]))
                        ->modalSubmitActionLabel(__('booking_attendee.modals.check_in_submit'))
                        ->tooltip(__('booking_attendee.tooltips.check_in'))
                        ->visible(fn(BookingAttendee $record) => in_array($record->booking->status, ['confirmed', 'checked_in'])),

                    Action::make('view_booking')
                        ->label(__('booking_attendee.actions.view_booking'))
                        ->icon('heroicon-o-rectangle-stack')
                        ->color('gray')
                        ->url(fn(BookingAttendee $record) => BookingResource::getUrl('view', ['record' => $record->booking_id]))
                        ->openUrlInNewTab(),
                ])
                    ->label('Actions')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size(Size::Small)
                    ->color('primary')
                    ->button(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->exports([
                            BookingAttendeesExport::make(),
                        ]),

                    BulkAction::make('bulk_resend_tickets')
                        ->label(__('booking_attendee.actions.resend_ticket'))
                        ->icon('heroicon-o-paper-airplane')
                        ->color('success')
                        ->action(function ($records) {
                            $sent = 0;
                            $failed = 0;
                            foreach ($records as $record) {
                                $record->sendTicketEmail() ? $sent++ : $failed++;
                            }
                            Notification::make()
                                ->success()
                                ->title(__('booking_attendee.notifications.ticket_resent'))
                                ->body("Sent: {$sent}" . ($failed ? ", Failed: {$failed}" : ''))
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'      => ListBookingAttendees::route('/'),
            'view'       => ViewBookingAttendee::route('/{record}'),
            'activities' => ListBookingAttendeeActivities::route('/{record}/activities'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['booking.event', 'booking.timeSlot', 'booking.extraServices', 'ticketType']);
    }
}
