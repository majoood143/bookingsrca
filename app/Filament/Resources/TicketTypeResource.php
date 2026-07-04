<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Toggle;
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
use App\Filament\Resources\TicketTypeResource\Pages\ListTicketTypes;
use App\Filament\Resources\TicketTypeResource\Pages\CreateTicketType;
use App\Filament\Resources\TicketTypeResource\Pages\EditTicketType;
use App\Filament\Resources\TicketTypeResource\Pages\ListTicketTypeActivities;
use App\Filament\Resources\TicketTypeResource\Pages;
use App\Models\TicketType;
use App\Models\Event;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Schemas\Components\Utilities\Get;

class TicketTypeResource extends Resource
{
    protected static ?string $model = TicketType::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-ticket';
    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('ticket_type.navigation.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('ticket_type.navigation.plural');
    }

    public static function getModelLabel(): string
    {
        return __('ticket_type.navigation.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('ticket_type.navigation.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('ticket_type.sections.information'))
                    ->description(__('ticket_type.sections.information_desc'))
                    ->schema([
                        Select::make('event_id')
                            ->label(__('ticket_type.fields.event'))
                            ->relationship('event', 'title')
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->getTranslation('title', 'en'))
                            ->searchable(['title->en', 'title->ar'])
                            ->preload()
                            ->required()
                            ->native(false)
                            ->live()
                            ->helperText(__('ticket_type.fields.event_helper'))
                            ->createOptionForm([
                                TextInput::make('title.en')
                                    ->label(__('ticket_type.create_event_form.title_en'))
                                    ->required(),
                                TextInput::make('title.ar')
                                    ->label(__('ticket_type.create_event_form.title_ar'))
                                    ->required(),
                                DatePicker::make('start_date')
                                    ->required(),
                                DatePicker::make('end_date')
                                    ->required(),
                                Select::make('status')
                                    ->options([
                                        'draft'     => __('ticket_type.create_event_form.draft'),
                                        'published' => __('ticket_type.create_event_form.published'),
                                    ])
                                    ->default('published')
                                    ->required(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('name.en')
                                    ->label(__('ticket_type.fields.name_en'))
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder(__('ticket_type.placeholders.name_en'))
                                    ->helperText(__('ticket_type.fields.name_en_helper')),

                                TextInput::make('name.ar')
                                    ->label(__('ticket_type.fields.name_ar'))
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder(__('ticket_type.placeholders.name_ar'))
                                    ->helperText(__('ticket_type.fields.name_ar_helper')),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Textarea::make('description.en')
                                    ->label(__('ticket_type.fields.description_en'))
                                    ->rows(3)
                                    ->placeholder(__('ticket_type.placeholders.description_en'))
                                    ->columnSpanFull(),

                                Textarea::make('description.ar')
                                    ->label(__('ticket_type.fields.description_ar'))
                                    ->rows(3)
                                    ->placeholder(__('ticket_type.placeholders.description_ar'))
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columns(1)
                    ->collapsible(),

                Section::make(__('ticket_type.sections.pricing'))
                    ->description(__('ticket_type.sections.pricing_desc'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('price')
                                    ->label(__('ticket_type.fields.price'))
                                    ->required()
                                    ->numeric()
                                    ->prefix('OMR')
                                    ->minValue(0)
                                    ->maxValue(999999.99)
                                    ->step(0.01)
                                    ->default(0)
                                    ->helperText(__('ticket_type.fields.price_helper')),

                                TextInput::make('quantity_available')
                                    ->label(__('ticket_type.fields.quantity_available'))
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(100000)
                                    ->default(100)
                                    ->suffix(__('ticket_type.suffix.tickets'))
                                    ->helperText(__('ticket_type.fields.quantity_available_helper')),

                                TextInput::make('quantity_sold')
                                    ->label(__('ticket_type.fields.quantity_sold'))
                                    ->numeric()
                                    ->default(0)
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->suffix(__('ticket_type.suffix.sold'))
                                    ->helperText(__('ticket_type.fields.quantity_sold_helper'))
                                    ->visible(fn($record) => $record !== null),
                            ]),
                    ])
                    ->columns(1)
                    ->collapsible(),

                Section::make(__('ticket_type.sections.sale_period'))
                    ->description(__('ticket_type.sections.sale_period_desc'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('sale_start_date')
                                    ->label(__('ticket_type.fields.sale_start_date'))
                                    ->native(false)
                                    ->displayFormat('Y-m-d')
                                    ->helperText(__('ticket_type.fields.sale_start_helper')),

                                DatePicker::make('sale_end_date')
                                    ->label(__('ticket_type.fields.sale_end_date'))
                                    ->native(false)
                                    ->displayFormat('Y-m-d')
                                    ->after('sale_start_date')
                                    ->helperText(__('ticket_type.fields.sale_end_helper')),
                            ]),

                        Placeholder::make('sale_period_info')
                            ->label('')
                            ->content(function ($record) {
                                if (!$record) {
                                    return __('ticket_type.sale_period_status.pending');
                                }

                                $now = now()->format('Y-m-d');
                                $startDate = $record->sale_start_date?->format('Y-m-d');
                                $endDate = $record->sale_end_date?->format('Y-m-d');

                                if (!$startDate && !$endDate) {
                                    return new HtmlString(
                                        "<span class='text-green-600 font-medium'>" . __('ticket_type.sale_period_status.always_available') . "</span>"
                                    );
                                }

                                if ($startDate && $now < $startDate) {
                                    return new HtmlString(
                                        "<span class='text-orange-600 font-medium'>" . __('ticket_type.sale_period_status.not_yet', ['date' => $startDate]) . "</span>"
                                    );
                                }

                                if ($endDate && $now > $endDate) {
                                    return new HtmlString(
                                        "<span class='text-red-600 font-medium'>" . __('ticket_type.sale_period_status.ended', ['date' => $endDate]) . "</span>"
                                    );
                                }

                                return new HtmlString(
                                    "<span class='text-green-600 font-medium'>" . __('ticket_type.sale_period_status.currently') . "</span>"
                                );
                            })
                            ->visible(fn($record) => $record !== null),
                    ])
                    ->columns(1)
                    ->collapsible()
                    ->collapsed(),

                Section::make(__('ticket_type.sections.status'))
                    ->schema([
                        Toggle::make('is_active')
                            ->label(__('ticket_type.fields.is_active'))
                            ->helperText(__('ticket_type.fields.is_active_helper'))
                            ->default(true)
                            ->inline(false),
                    ])
                    ->collapsible(),

                Section::make(__('ticket_type.sections.dependency'))
                    ->description(__('ticket_type.sections.dependency_desc'))
                    ->schema([
                        Select::make('dependsOnMany')
                            ->label(__('ticket_type.fields.depends_on'))
                            ->helperText(__('ticket_type.fields.depends_on_helper'))
                            ->relationship(
                                name: 'dependsOnMany',
                                titleAttribute: 'id',
                                modifyQueryUsing: fn (Builder $query, Get $get, $record) => $query
                                    ->where('ticket_types.event_id', $get('event_id'))
                                    ->when($record?->id, fn ($q) => $q->where('ticket_types.id', '!=', $record->id)),
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->getTranslation('name', 'en'))
                            ->placeholder(__('ticket_type.fields.depends_on_placeholder'))
                            ->native(false)
                            ->multiple()
                            ->preload()
                            ->searchable(),
                    ])
                    ->collapsible()
                    ->collapsed(fn ($record) => !$record?->dependsOnMany()->exists()),

                Section::make(__('ticket_type.sections.sales_overview'))
                    ->description(__('ticket_type.sections.sales_overview_desc'))
                    ->schema([
                        Placeholder::make('sales_info')
                            ->label('')
                            ->content(function ($record) {
                                if (!$record) {
                                    return __('ticket_type.sales_info.pending');
                                }

                                $remaining = $record->getRemainingQuantity();
                                $percentage = $record->quantity_available > 0
                                    ? round(($record->quantity_sold / $record->quantity_available) * 100)
                                    : 0;

                                $totalRevenue = $record->quantity_sold * $record->price;
                                $potentialRevenue = $record->quantity_available * $record->price;

                                $color = $remaining > 50 ? 'success' : ($remaining > 0 ? 'warning' : 'danger');

                                return new HtmlString("
                                    <div class='space-y-4'>
                                        <div class='grid grid-cols-3 gap-4 text-sm'>
                                            <div class='bg-blue-50 p-3 rounded-lg'>
                                                <div class='text-xs text-gray-600 mb-1'>" . __('ticket_type.sales_info.sold') . "</div>
                                                <div class='text-2xl font-bold text-blue-600'>{$record->quantity_sold}</div>
                                            </div>
                                            <div class='bg-{$color}-50 p-3 rounded-lg'>
                                                <div class='text-xs text-gray-600 mb-1'>" . __('ticket_type.sales_info.available') . "</div>
                                                <div class='text-2xl font-bold text-{$color}-600'>{$remaining}</div>
                                            </div>
                                            <div class='bg-gray-50 p-3 rounded-lg'>
                                                <div class='text-xs text-gray-600 mb-1'>" . __('ticket_type.sales_info.total') . "</div>
                                                <div class='text-2xl font-bold text-gray-900'>{$record->quantity_available}</div>
                                            </div>
                                        </div>

                                        <div>
                                            <div class='flex justify-between text-xs text-gray-600 mb-1'>
                                                <span>" . __('ticket_type.sales_info.sales_progress') . "</span>
                                                <span>{$percentage}%</span>
                                            </div>
                                            <div class='w-full bg-gray-200 rounded-full h-3'>
                                                <div class='bg-{$color}-600 h-3 rounded-full transition-all' style='width: {$percentage}%'></div>
                                            </div>
                                        </div>

                                        <div class='grid grid-cols-2 gap-4 text-sm pt-3 border-t'>
                                            <div>
                                                <div class='text-xs text-gray-600'>" . __('ticket_type.sales_info.revenue_generated') . "</div>
                                                <div class='text-lg font-bold text-green-600'>OMR" . number_format($totalRevenue, 3) . "</div>
                                            </div>
                                            <div>
                                                <div class='text-xs text-gray-600'>" . __('ticket_type.sales_info.potential_revenue') . "</div>
                                                <div class='text-lg font-bold text-gray-900'>OMR" . number_format($potentialRevenue, 3) . "</div>
                                            </div>
                                        </div>
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
                    ->label(__('ticket_type.columns.event'))
                    ->getStateUsing(fn($record) => $record->event->getTranslation('title', app()->getLocale()))
                    ->searchable(['title->en', 'title->ar'])
                    ->sortable()
                    ->weight('medium')
                    ->wrap()
                    ->limit(30),

                TextColumn::make('name')
                    ->label(__('ticket_type.columns.name'))
                    ->getStateUsing(fn($record) => $record->getTranslation('name', app()->getLocale()))
                    ->searchable(['name->en', 'name->ar'])
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-ticket'),

                TextColumn::make('price')
                    ->label(__('ticket_type.columns.price'))
                    ->money('OMR')
                    ->sortable()
                    ->weight('medium')
                    ->alignEnd(),

                TextColumn::make('quantity_available')
                    ->label(__('ticket_type.columns.total'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(),

                TextColumn::make('quantity_sold')
                    ->label(__('ticket_type.columns.sold'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('remaining_quantity')
                    ->label(__('ticket_type.columns.available'))
                    ->getStateUsing(fn($record) => $record->getRemainingQuantity())
                    ->badge()
                    ->color(fn($state) => match (true) {
                        $state > 50 => 'success',
                        $state > 20 => 'info',
                        $state > 0  => 'warning',
                        default     => 'danger',
                    })
                    ->icon(fn($state) => match (true) {
                        $state > 0 => 'heroicon-o-check-circle',
                        default    => 'heroicon-o-x-circle',
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderByRaw("(quantity_available - quantity_sold) {$direction}");
                    }),

                TextColumn::make('sales_percentage')
                    ->label(__('ticket_type.columns.sold_pct'))
                    ->getStateUsing(function ($record) {
                        if ($record->quantity_available == 0) return '0%';
                        return round(($record->quantity_sold / $record->quantity_available) * 100) . '%';
                    })
                    ->badge()
                    ->color(fn($state) => match (true) {
                        intval($state) >= 90 => 'danger',
                        intval($state) >= 70 => 'warning',
                        intval($state) >= 50 => 'info',
                        default              => 'success',
                    })
                    ->alignCenter()
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderByRaw("(quantity_sold / quantity_available) {$direction}");
                    }),

                TextColumn::make('revenue')
                    ->label(__('ticket_type.columns.revenue'))
                    ->getStateUsing(fn($record) => $record->quantity_sold * $record->price)
                    ->money('OMR')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderByRaw("(quantity_sold * price) {$direction}");
                    })
                    ->weight('bold')
                    ->color('success')
                    ->alignEnd()
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label(__('ticket_type.columns.status'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter(),

                TextColumn::make('dependsOnMany.name')
                    ->label(__('ticket_type.columns.depends_on'))
                    ->getStateUsing(fn ($record) => $record->dependsOnMany
                        ->map(fn ($t) => $t->getTranslation('name', app()->getLocale()))
                        ->all())
                    ->badge()
                    ->color('warning')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('sale_period')
                    ->label(__('ticket_type.columns.sale_period'))
                    ->getStateUsing(function ($record) {
                        $start = $record->sale_start_date?->format('M d, Y');
                        $end   = $record->sale_end_date?->format('M d, Y');

                        if (!$start && !$end) return __('ticket_type.sale_period_column.always');
                        if ($start && !$end)  return __('ticket_type.sale_period_column.from', ['date' => $start]);
                        if (!$start && $end)  return __('ticket_type.sale_period_column.until', ['date' => $end]);
                        return "{$start} - {$end}";
                    })
                    ->badge()
                    ->color(function ($record) {
                        $now   = now()->format('Y-m-d');
                        $start = $record->sale_start_date?->format('Y-m-d');
                        $end   = $record->sale_end_date?->format('Y-m-d');

                        if (!$start && !$end) return 'success';
                        if ($start && $now < $start) return 'warning';
                        if ($end && $now > $end) return 'danger';
                        return 'success';
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('bookings_count')
                    ->counts('bookings')
                    ->label(__('ticket_type.columns.bookings'))
                    ->badge()
                    ->color('primary')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(__('ticket_type.columns.created_at'))
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('ticket_type.columns.updated_at'))
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('event')
                    ->label(__('ticket_type.filters.by_event'))
                    ->relationship('event', 'title')
                    ->getOptionLabelFromRecordUsing(fn($record) => $record->getTranslation('title', 'en'))
                    ->searchable()
                    ->preload()
                    ->multiple(),

                TernaryFilter::make('is_active')
                    ->label(__('ticket_type.filters.status'))
                    ->placeholder(__('ticket_type.filters.status_all'))
                    ->trueLabel(__('ticket_type.filters.status_active'))
                    ->falseLabel(__('ticket_type.filters.status_inactive')),

                Filter::make('availability')
                    ->label(__('ticket_type.filters.availability'))
                    ->schema([
                        Select::make('availability_status')
                            ->label(__('ticket_type.filters.availability_show'))
                            ->options([
                                'available'       => __('ticket_type.filters.avail_available'),
                                'sold_out'        => __('ticket_type.filters.avail_sold_out'),
                                'almost_sold_out' => __('ticket_type.filters.avail_almost'),
                                'low_stock'       => __('ticket_type.filters.avail_low'),
                            ])
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['availability_status'] === 'available',
                                fn(Builder $query): Builder => $query->whereColumn('quantity_sold', '<', 'quantity_available')
                            )
                            ->when(
                                $data['availability_status'] === 'sold_out',
                                fn(Builder $query): Builder => $query->whereColumn('quantity_sold', '>=', 'quantity_available')
                            )
                            ->when(
                                $data['availability_status'] === 'almost_sold_out',
                                fn(Builder $query): Builder => $query->whereRaw('(quantity_sold / quantity_available) >= 0.8')
                            )
                            ->when(
                                $data['availability_status'] === 'low_stock',
                                fn(Builder $query): Builder => $query->whereRaw('(quantity_available - quantity_sold) < 50')
                                    ->whereColumn('quantity_sold', '<', 'quantity_available')
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        return match ($data['availability_status'] ?? null) {
                            'available'       => __('ticket_type.filters.ind_available'),
                            'sold_out'        => __('ticket_type.filters.ind_sold_out'),
                            'almost_sold_out' => __('ticket_type.filters.ind_almost'),
                            'low_stock'       => __('ticket_type.filters.ind_low'),
                            default           => null,
                        };
                    }),

                Filter::make('price_range')
                    ->label(__('ticket_type.filters.price_range'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('price_from')
                                    ->label(__('ticket_type.filters.price_from'))
                                    ->numeric()
                                    ->prefix('OMR'),
                                TextInput::make('price_to')
                                    ->label(__('ticket_type.filters.price_to'))
                                    ->numeric()
                                    ->prefix('OMR'),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['price_from'],
                                fn(Builder $query, $price): Builder => $query->where('price', '>=', $price),
                            )
                            ->when(
                                $data['price_to'],
                                fn(Builder $query, $price): Builder => $query->where('price', '<=', $price),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['price_from'] ?? null) {
                            $indicators['price_from'] = __('ticket_type.filters.ind_price_from', ['amount' => number_format($data['price_from'], 3)]);
                        }
                        if ($data['price_to'] ?? null) {
                            $indicators['price_to'] = __('ticket_type.filters.ind_price_to', ['amount' => number_format($data['price_to'], 3)]);
                        }
                        return $indicators;
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),

                    Action::make('toggle_active')
                        ->label(fn($record) => $record->is_active
                            ? __('ticket_type.actions.deactivate')
                            : __('ticket_type.actions.activate'))
                        ->icon(fn($record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                        ->color(fn($record) => $record->is_active ? 'danger' : 'success')
                        ->action(function (TicketType $record) {
                            $record->update(['is_active' => !$record->is_active]);

                            Notification::make()
                                ->success()
                                ->title(__('ticket_type.notifications.status_updated'))
                                ->body($record->is_active
                                    ? __('ticket_type.notifications.type_activated')
                                    : __('ticket_type.notifications.type_deactivated'))
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading(fn($record) => $record->is_active
                            ? __('ticket_type.modals.deactivate_heading')
                            : __('ticket_type.modals.activate_heading'))
                        ->modalDescription(fn($record) => $record->is_active
                            ? __('ticket_type.modals.deactivate_description')
                            : __('ticket_type.modals.activate_description')),

                    DeleteAction::make()
                        ->before(function (TicketType $record) {
                            if ($record->bookings()->count() > 0) {
                                Notification::make()
                                    ->danger()
                                    ->title(__('ticket_type.notifications.cannot_delete'))
                                    ->body(__('ticket_type.notifications.has_bookings'))
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
                                    ->title(__('ticket_type.notifications.cannot_delete'))
                                    ->body(__('ticket_type.notifications.bulk_has_bookings', ['count' => $hasBookings->count()]))
                                    ->send();

                                return false;
                            }
                        }),

                    BulkAction::make('activate')
                        ->label(__('ticket_type.actions.activate_selected'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => true]);

                            Notification::make()
                                ->success()
                                ->title(__('ticket_type.notifications.types_activated'))
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('deactivate')
                        ->label(__('ticket_type.actions.deactivate_selected'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => false]);

                            Notification::make()
                                ->success()
                                ->title(__('ticket_type.notifications.types_deactivated'))
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation(),
                ]),
            ])
            ->emptyStateActions([
                CreateAction::make()
                    ->label(__('ticket_type.actions.create_first'))
                    ->icon('heroicon-o-plus'),
            ])
            ->emptyStateHeading(__('ticket_type.empty_state.heading'))
            ->emptyStateDescription(__('ticket_type.empty_state.description'))
            ->emptyStateIcon('heroicon-o-ticket');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'      => ListTicketTypes::route('/'),
            'create'     => CreateTicketType::route('/create'),
            'edit'       => EditTicketType::route('/{record}/edit'),
            'activities' => ListTicketTypeActivities::route('/{record}/activities'),
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
