<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\HtmlString;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\Filter;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Actions\DeleteAction;
use Filament\Support\Enums\Size;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkAction;
use Filament\Actions\CreateAction;
use App\Filament\Resources\TimeSlotResource\Pages\ListTimeSlots;
use App\Filament\Resources\TimeSlotResource\Pages\CreateTimeSlot;
use App\Filament\Resources\TimeSlotResource\Pages\EditTimeSlot;
use App\Filament\Resources\TimeSlotResource\Pages\ListTimeSlotActivities;
use App\Filament\Resources\TimeSlotResource\Pages;
use App\Models\TimeSlot;
use App\Models\Event;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Schemas\Components\Utilities\Get;

class TimeSlotResource extends Resource
{
    protected static ?string $model = TimeSlot::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clock';
    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('time_slot.navigation.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('time_slot.navigation.plural');
    }

    public static function getModelLabel(): string
    {
        return __('time_slot.navigation.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('time_slot.navigation.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('time_slot.sections.information'))
                    ->description(__('time_slot.sections.information_desc'))
                    ->schema([
                        Select::make('event_id')
                            ->label(__('time_slot.fields.event'))
                            ->relationship('event', 'title')
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->getTranslation('title', 'en'))
                            ->searchable(['title->en', 'title->ar'])
                            ->preload()
                            ->required()
                            ->native(false)
                            ->live()
                            ->helperText(__('time_slot.fields.event_helper'))
                            ->createOptionForm([
                                TextInput::make('title.en')
                                    ->label(__('time_slot.create_event_form.title_en'))
                                    ->required(),
                                TextInput::make('title.ar')
                                    ->label(__('time_slot.create_event_form.title_ar'))
                                    ->required(),
                                DatePicker::make('start_date')
                                    ->required(),
                                DatePicker::make('end_date')
                                    ->required(),
                                Select::make('status')
                                    ->options([
                                        'draft'     => __('time_slot.create_event_form.draft'),
                                        'published' => __('time_slot.create_event_form.published'),
                                    ])
                                    ->default('published')
                                    ->required(),
                            ]),

                        DatePicker::make('date')
                            ->label(__('time_slot.fields.date'))
                            ->required()
                            ->native(false)
                            ->displayFormat('Y-m-d')
                            ->live()
                            ->minDate(fn(Get $get) => Event::find($get('event_id'))?->start_date)
                            ->maxDate(fn(Get $get) => Event::find($get('event_id'))?->end_date)
                            ->rule(function (Get $get) {
                                return function (string $attribute, $value, \Closure $fail) use ($get) {
                                    $event = Event::find($get('event_id'));
                                    if (!$event || !$value) {
                                        return;
                                    }
                                    if ($event->is_recurring) {
                                        $day = strtolower(\Carbon\Carbon::parse($value)->format('l'));
                                        if (!in_array($day, $event->recurring_days ?? [])) {
                                            $fail(__('time_slot.fields.date_not_recurring_day'));
                                        }
                                    }
                                };
                            })
                            ->unique(
                                table: 'time_slots',
                                modifyRuleUsing: fn($rule, Get $get) => $rule
                                    ->where('event_id', $get('event_id'))
                                    ->where('start_time', $get('start_time'))
                                    ->where('end_time', $get('end_time')),
                                ignoreRecord: true,
                            )
                            ->validationMessages([
                                'unique' => __('time_slot.fields.date_unique'),
                            ])
                            ->helperText(__('time_slot.fields.date_helper')),

                        Grid::make(2)
                            ->schema([
                                TimePicker::make('start_time')
                                    ->label(__('time_slot.fields.start_time'))
                                    ->required()
                                    ->seconds(false)
                                    ->native(false)
                                    ->displayFormat('H:i')
                                    ->helperText(__('time_slot.fields.start_time_helper')),

                                TimePicker::make('end_time')
                                    ->label(__('time_slot.fields.end_time'))
                                    ->required()
                                    ->seconds(false)
                                    ->native(false)
                                    ->displayFormat('H:i')
                                    ->after('start_time')
                                    ->helperText(__('time_slot.fields.end_time_helper')),
                            ]),

                        TextInput::make('label')
                            ->label(__('time_slot.fields.label'))
                            ->maxLength(255)
                            ->placeholder(__('time_slot.fields.label_placeholder'))
                            ->helperText(__('time_slot.fields.label_helper')),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('max_attendees')
                                    ->label(__('time_slot.fields.max_attendees'))
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(10000)
                                    ->default(50)
                                    ->suffix(__('time_slot.suffix.people'))
                                    ->helperText(__('time_slot.fields.max_attendees_helper')),

                                TextInput::make('current_bookings')
                                    ->label(__('time_slot.fields.current_bookings'))
                                    ->numeric()
                                    ->default(0)
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->suffix(__('time_slot.suffix.booked'))
                                    ->helperText(__('time_slot.fields.current_bookings_helper'))
                                    ->visible(fn($record) => $record !== null),
                            ]),

                        Toggle::make('is_active')
                            ->label(__('time_slot.fields.is_active'))
                            ->helperText(__('time_slot.fields.is_active_helper'))
                            ->default(true)
                            ->inline(false),
                    ])
                    ->columns(1),

                Section::make(__('time_slot.sections.capacity'))
                    ->description(__('time_slot.sections.capacity_desc'))
                    ->schema([
                        Placeholder::make('capacity_info')
                            ->label('')
                            ->content(function ($record) {
                                if (!$record) {
                                    return __('time_slot.capacity_info.pending');
                                }

                                $remaining = $record->getRemainingCapacity();
                                $percentage = $record->max_attendees > 0
                                    ? round(($record->current_bookings / $record->max_attendees) * 100)
                                    : 0;

                                $color = $remaining > 20 ? 'success' : ($remaining > 0 ? 'warning' : 'danger');

                                return new HtmlString("
                                    <div class='space-y-2'>
                                        <div class='flex justify-between text-sm'>
                                            <span>" . __('time_slot.capacity_info.booked') . " <strong>{$record->current_bookings}</strong></span>
                                            <span>" . __('time_slot.capacity_info.available') . " <strong class='text-{$color}-600'>{$remaining}</strong></span>
                                            <span>" . __('time_slot.capacity_info.total') . " <strong>{$record->max_attendees}</strong></span>
                                        </div>
                                        <div class='w-full bg-gray-200 rounded-full h-2.5'>
                                            <div class='bg-{$color}-600 h-2.5 rounded-full' style='width: {$percentage}%'></div>
                                        </div>
                                        <p class='text-xs text-gray-500'>" . __('time_slot.capacity_info.filled_pct', ['percent' => $percentage]) . "</p>
                                    </div>
                                ");
                            })
                            ->visible(fn($record) => $record !== null),
                    ])
                    ->visible(fn($record) => $record !== null)
                    ->collapsible(),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('event.title')
                    ->label(__('time_slot.columns.event'))
                    ->getStateUsing(fn($record) => $record->event->getTranslation('title', app()->getLocale()))
                    ->searchable(['title->en', 'title->ar'])
                    ->sortable()
                    ->weight('medium')
                    ->wrap()
                    ->limit(40),

                TextColumn::make('date')
                    ->label(__('time_slot.columns.date'))
                    ->date('M d, Y')
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('start_time')
                    ->label(__('time_slot.columns.start_time'))
                    ->time('H:i')
                    ->sortable()
                    ->badge()
                    ->searchable()
                    ->color('info'),

                TextColumn::make('end_time')
                    ->label(__('time_slot.columns.end_time'))
                    ->time('H:i')
                    ->sortable()
                    ->badge()
                    ->searchable()
                    ->color('info'),

                TextColumn::make('time_range')
                    ->label(__('time_slot.columns.time_range'))
                    ->getStateUsing(fn($record) => $record->getTimeRange())
                    ->badge()
                    ->color('primary')
                    ->icon('heroicon-o-clock')
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('label')
                    ->label(__('time_slot.columns.label'))
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('max_attendees')
                    ->label(__('time_slot.columns.capacity'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('current_bookings')
                    ->label(__('time_slot.columns.booked'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->color(fn($record) => $record->current_bookings >= $record->max_attendees ? 'danger' : 'success'),

                TextColumn::make('remaining_capacity')
                    ->label(__('time_slot.columns.available'))
                    ->getStateUsing(fn($record) => $record->getRemainingCapacity())
                    ->badge()
                    ->color(fn($state) => match (true) {
                        $state > 20 => 'success',
                        $state > 0  => 'warning',
                        default     => 'danger',
                    })
                    ->icon(fn($state) => match (true) {
                        $state > 0 => 'heroicon-o-check-circle',
                        default    => 'heroicon-o-x-circle',
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderByRaw("(max_attendees - current_bookings) {$direction}");
                    }),

                TextColumn::make('availability_percentage')
                    ->label(__('time_slot.columns.filled'))
                    ->getStateUsing(function ($record) {
                        if ($record->max_attendees == 0) return '0%';
                        return round(($record->current_bookings / $record->max_attendees) * 100) . '%';
                    })
                    ->alignCenter()
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label(__('time_slot.columns.status'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter(),

                TextColumn::make('bookings_count')
                    ->counts('bookings')
                    ->label(__('time_slot.columns.total_bookings'))
                    ->badge()
                    ->color('primary')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(__('time_slot.columns.created_at'))
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('time_slot.columns.updated_at'))
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('date', 'asc')
            ->filters([
                Filter::make('date')
                    ->label(__('time_slot.filters.date'))
                    ->schema([
                        DatePicker::make('from_date')
                            ->label(__('time_slot.filters.from'))
                            ->native(false),
                        DatePicker::make('to_date')
                            ->label(__('time_slot.filters.to'))
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from_date'],
                                fn(Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['to_date'],
                                fn(Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),

                SelectFilter::make('event')
                    ->label(__('time_slot.filters.by_event'))
                    ->relationship('event', 'title')
                    ->getOptionLabelFromRecordUsing(fn($record) => $record->getTranslation('title', 'en'))
                    ->searchable()
                    ->preload()
                    ->multiple(),

                TernaryFilter::make('is_active')
                    ->label(__('time_slot.filters.status'))
                    ->placeholder(__('time_slot.filters.status_all'))
                    ->trueLabel(__('time_slot.filters.status_active'))
                    ->falseLabel(__('time_slot.filters.status_inactive')),

                Filter::make('availability')
                    ->label(__('time_slot.filters.availability'))
                    ->schema([
                        Select::make('availability_status')
                            ->label(__('time_slot.filters.availability_show'))
                            ->options([
                                'available'   => __('time_slot.filters.avail_available'),
                                'full'        => __('time_slot.filters.avail_full'),
                                'almost_full' => __('time_slot.filters.avail_almost'),
                            ])
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['availability_status'] === 'available',
                                fn(Builder $query): Builder => $query->whereColumn('current_bookings', '<', 'max_attendees')
                            )
                            ->when(
                                $data['availability_status'] === 'full',
                                fn(Builder $query): Builder => $query->whereColumn('current_bookings', '>=', 'max_attendees')
                            )
                            ->when(
                                $data['availability_status'] === 'almost_full',
                                fn(Builder $query): Builder => $query->whereRaw('(current_bookings / max_attendees) >= 0.8')
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['availability_status']) {
                            return null;
                        }
                        return match ($data['availability_status']) {
                            'available'   => __('time_slot.filters.ind_available'),
                            'full'        => __('time_slot.filters.ind_full'),
                            'almost_full' => __('time_slot.filters.ind_almost'),
                            default       => null,
                        };
                    }),

                Filter::make('time_range')
                    ->label(__('time_slot.filters.time_range'))
                    ->schema([
                        TimePicker::make('from_time')
                            ->label(__('time_slot.filters.from'))
                            ->native(false),
                        TimePicker::make('to_time')
                            ->label(__('time_slot.filters.to'))
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from_time'],
                                fn(Builder $query, $time): Builder => $query->where('start_time', '>=', $time),
                            )
                            ->when(
                                $data['to_time'],
                                fn(Builder $query, $time): Builder => $query->where('end_time', '<=', $time),
                            );
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),

                    Action::make('toggle_active')
                        ->label(fn($record) => $record->is_active
                            ? __('time_slot.actions.deactivate')
                            : __('time_slot.actions.activate'))
                        ->icon(fn($record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                        ->color(fn($record) => $record->is_active ? 'danger' : 'success')
                        ->action(function (TimeSlot $record) {
                            $record->update(['is_active' => !$record->is_active]);

                            Notification::make()
                                ->success()
                                ->title(__('time_slot.notifications.status_updated'))
                                ->body($record->is_active
                                    ? __('time_slot.notifications.slot_activated')
                                    : __('time_slot.notifications.slot_deactivated'))
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading(fn($record) => $record->is_active
                            ? __('time_slot.modals.deactivate_heading')
                            : __('time_slot.modals.activate_heading'))
                        ->modalDescription(fn($record) => $record->is_active
                            ? __('time_slot.modals.deactivate_description')
                            : __('time_slot.modals.activate_description')),

                    DeleteAction::make()
                        ->before(function (TimeSlot $record) {
                            if ($record->bookings()->count() > 0) {
                                Notification::make()
                                    ->danger()
                                    ->title(__('time_slot.notifications.cannot_delete'))
                                    ->body(__('time_slot.notifications.has_bookings'))
                                    ->send();

                                return false;
                            }
                        }),
                ])
                    ->label('More actions')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size(Size::Small)
                    ->color('primary')
                    ->button(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function ($records) {
                            $hasBookings = $records->filter(fn($record) => $record->bookings()->count() > 0);

                            if ($hasBookings->count() > 0) {
                                Notification::make()
                                    ->danger()
                                    ->title(__('time_slot.notifications.cannot_delete'))
                                    ->body(__('time_slot.notifications.bulk_has_bookings', ['count' => $hasBookings->count()]))
                                    ->send();

                                return false;
                            }
                        }),

                    BulkAction::make('activate')
                        ->label(__('time_slot.actions.activate_selected'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => true]);

                            Notification::make()
                                ->success()
                                ->title(__('time_slot.notifications.slots_activated'))
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('deactivate')
                        ->label(__('time_slot.actions.deactivate_selected'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => false]);

                            Notification::make()
                                ->success()
                                ->title(__('time_slot.notifications.slots_deactivated'))
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation(),

                    BulkAction::make('edit_time_range')
                        ->label(__('time_slot.actions.edit_time_range'))
                        ->icon('heroicon-o-pencil-square')
                        ->color('warning')
                        ->schema([
                            DatePicker::make('from_date')
                                ->label(__('time_slot.filters.from'))
                                ->helperText(__('time_slot.modals.edit_time_range_period_hint'))
                                ->native(false)
                                ->displayFormat('Y-m-d'),
                            DatePicker::make('to_date')
                                ->label(__('time_slot.filters.to'))
                                ->native(false)
                                ->displayFormat('Y-m-d'),
                            TimePicker::make('start_time')
                                ->label(__('time_slot.fields.start_time'))
                                ->required()
                                ->seconds(false)
                                ->native(false)
                                ->displayFormat('H:i'),
                            TimePicker::make('end_time')
                                ->label(__('time_slot.fields.end_time'))
                                ->required()
                                ->seconds(false)
                                ->native(false)
                                ->displayFormat('H:i')
                                ->after('start_time'),
                        ])
                        ->modalHeading(__('time_slot.modals.edit_time_range_heading'))
                        ->modalDescription(__('time_slot.modals.edit_time_range_description'))
                        ->action(function ($records, array $data): void {
                            $updated = 0;

                            foreach ($records as $record) {
                                if ($data['from_date'] && $record->date->lt(\Carbon\Carbon::parse($data['from_date']))) {
                                    continue;
                                }
                                if ($data['to_date'] && $record->date->gt(\Carbon\Carbon::parse($data['to_date']))) {
                                    continue;
                                }

                                $record->update([
                                    'start_time' => $data['start_time'],
                                    'end_time'   => $data['end_time'],
                                ]);
                                $updated++;
                            }

                            Notification::make()
                                ->success()
                                ->title(__('time_slot.notifications.time_range_updated'))
                                ->body(__('time_slot.notifications.time_range_updated_body', ['count' => $updated]))
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->emptyStateActions([
                CreateAction::make()
                    ->label(__('time_slot.actions.create_first'))
                    ->icon('heroicon-o-plus'),
            ])
            ->emptyStateHeading(__('time_slot.empty_state.heading'))
            ->emptyStateDescription(__('time_slot.empty_state.description'))
            ->emptyStateIcon('heroicon-o-clock');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'      => ListTimeSlots::route('/'),
            'create'     => CreateTimeSlot::route('/create'),
            'edit'       => EditTimeSlot::route('/{record}/edit'),
            'activities' => ListTimeSlotActivities::route('/{record}/activities'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
