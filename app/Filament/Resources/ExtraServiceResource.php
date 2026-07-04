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
use Filament\Schemas\Components\Utilities\Get;
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
use App\Filament\Resources\ExtraServiceResource\Pages\ListExtraServices;
use App\Filament\Resources\ExtraServiceResource\Pages\CreateExtraService;
use App\Filament\Resources\ExtraServiceResource\Pages\EditExtraService;
use App\Filament\Resources\ExtraServiceResource\Pages\ListExtraServiceActivities;
use App\Filament\Resources\ExtraServiceResource\Pages;
use App\Models\ExtraService;
use App\Models\Event;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ExtraServiceResource extends Resource
{
    protected static ?string $model = ExtraService::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return __('extra_service.navigation.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('extra_service.navigation.plural');
    }

    public static function getModelLabel(): string
    {
        return __('extra_service.navigation.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('extra_service.navigation.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('extra_service.sections.information'))
                    ->description(__('extra_service.sections.information_desc'))
                    ->schema([
                        Select::make('event_id')
                            ->label(__('extra_service.fields.event'))
                            ->relationship('event', 'title')
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->getTranslation('title', 'en'))
                            ->searchable(['title->en', 'title->ar'])
                            ->preload()
                            ->required()
                            ->native(false)
                            ->live()
                            ->helperText(__('extra_service.fields.event_helper'))
                            ->createOptionForm([
                                TextInput::make('title.en')
                                    ->label(__('extra_service.create_event_form.title_en'))
                                    ->required(),
                                TextInput::make('title.ar')
                                    ->label(__('extra_service.create_event_form.title_ar'))
                                    ->required(),
                                DatePicker::make('start_date')
                                    ->required(),
                                DatePicker::make('end_date')
                                    ->required(),
                                Select::make('status')
                                    ->options([
                                        'draft'     => __('extra_service.create_event_form.draft'),
                                        'published' => __('extra_service.create_event_form.published'),
                                    ])
                                    ->default('published')
                                    ->required(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('name.en')
                                    ->label(__('extra_service.fields.name_en'))
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder(__('extra_service.placeholders.name_en'))
                                    ->helperText(__('extra_service.fields.name_en_helper')),

                                TextInput::make('name.ar')
                                    ->label(__('extra_service.fields.name_ar'))
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder(__('extra_service.placeholders.name_ar'))
                                    ->helperText(__('extra_service.fields.name_ar_helper')),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Textarea::make('description.en')
                                    ->label(__('extra_service.fields.description_en'))
                                    ->rows(3)
                                    ->placeholder(__('extra_service.placeholders.description_en'))
                                    ->columnSpanFull(),

                                Textarea::make('description.ar')
                                    ->label(__('extra_service.fields.description_ar'))
                                    ->rows(3)
                                    ->placeholder(__('extra_service.placeholders.description_ar'))
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columns(1)
                    ->collapsible(),

                Section::make(__('extra_service.sections.pricing'))
                    ->description(__('extra_service.sections.pricing_desc'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('price')
                                    ->label(__('extra_service.fields.price'))
                                    ->required()
                                    ->numeric()
                                    ->prefix('OMR')
                                    ->minValue(0)
                                    ->maxValue(999999.99)
                                    ->step(0.01)
                                    ->default(0)
                                    ->helperText(__('extra_service.fields.price_helper')),

                                TextInput::make('quantity_available')
                                    ->label(__('extra_service.fields.quantity_available'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100000)
                                    ->suffix(__('extra_service.suffix.units'))
                                    ->helperText(__('extra_service.fields.quantity_available_helper'))
                                    ->placeholder(__('extra_service.placeholders.quantity')),

                                TextInput::make('quantity_used')
                                    ->label(__('extra_service.fields.quantity_used'))
                                    ->numeric()
                                    ->default(0)
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->suffix(__('extra_service.suffix.used'))
                                    ->helperText(__('extra_service.fields.quantity_used_helper'))
                                    ->visible(fn($record) => $record !== null),
                            ]),

                        Placeholder::make('quantity_info')
                            ->label('')
                            ->content(function (Get $get) {
                                $available = $get('quantity_available');
                                if (empty($available)) {
                                    return new HtmlString(
                                        "<div class='text-sm'>
                                            <span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800'>
                                                " . __('extra_service.quantity_info.unlimited_badge') . "
                                            </span>
                                            <p class='text-gray-600 mt-2'>" . __('extra_service.quantity_info.unlimited_description') . "</p>
                                        </div>"
                                    );
                                }
                                return new HtmlString(
                                    "<div class='text-sm'>
                                        <span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800'>
                                            " . __('extra_service.quantity_info.limited_badge') . "
                                        </span>
                                        <p class='text-gray-600 mt-2'>" . __('extra_service.quantity_info.limited_description', ['count' => $available]) . "</p>
                                    </div>"
                                );
                            }),
                    ])
                    ->columns(1)
                    ->collapsible(),

                Section::make(__('extra_service.sections.status'))
                    ->schema([
                        Toggle::make('is_active')
                            ->label(__('extra_service.fields.is_active'))
                            ->helperText(__('extra_service.fields.is_active_helper'))
                            ->default(true)
                            ->inline(false),
                    ])
                    ->collapsible(),

                Section::make(__('extra_service.sections.usage'))
                    ->description(__('extra_service.sections.usage_desc'))
                    ->schema([
                        Placeholder::make('usage_info')
                            ->label('')
                            ->content(function ($record) {
                                if (!$record) {
                                    return __('extra_service.usage_info.pending');
                                }

                                $isUnlimited = $record->quantity_available === null;

                                if ($isUnlimited) {
                                    $totalRevenue = $record->quantity_used * $record->price;

                                    return new HtmlString("
                                        <div class='space-y-4'>
                                            <div class='grid grid-cols-2 gap-4 text-sm'>
                                                <div class='bg-blue-50 p-4 rounded-lg'>
                                                    <div class='text-xs text-gray-600 mb-1'>" . __('extra_service.usage_info.total_used') . "</div>
                                                    <div class='text-3xl font-bold text-blue-600'>{$record->quantity_used}</div>
                                                    <div class='text-xs text-gray-500 mt-1'>" . __('extra_service.usage_info.no_limit') . "</div>
                                                </div>
                                                <div class='bg-green-50 p-4 rounded-lg'>
                                                    <div class='text-xs text-gray-600 mb-1'>" . __('extra_service.usage_info.revenue_generated') . "</div>
                                                    <div class='text-3xl font-bold text-green-600'>OMR" . number_format($totalRevenue, 3) . "</div>
                                                    <div class='text-xs text-gray-500 mt-1'>" . __('extra_service.usage_info.unlimited_service') . "</div>
                                                </div>
                                            </div>
                                        </div>
                                    ");
                                }

                                $remaining = $record->getRemainingQuantity();
                                $percentage = $record->quantity_available > 0
                                    ? round(($record->quantity_used / $record->quantity_available) * 100)
                                    : 0;

                                $totalRevenue = $record->quantity_used * $record->price;
                                $potentialRevenue = $record->quantity_available * $record->price;

                                $color = $remaining > 50 ? 'success' : ($remaining > 0 ? 'warning' : 'danger');

                                return new HtmlString("
                                    <div class='space-y-4'>
                                        <div class='grid grid-cols-3 gap-4 text-sm'>
                                            <div class='bg-blue-50 p-3 rounded-lg'>
                                                <div class='text-xs text-gray-600 mb-1'>" . __('extra_service.usage_info.used') . "</div>
                                                <div class='text-2xl font-bold text-blue-600'>{$record->quantity_used}</div>
                                            </div>
                                            <div class='bg-{$color}-50 p-3 rounded-lg'>
                                                <div class='text-xs text-gray-600 mb-1'>" . __('extra_service.usage_info.available') . "</div>
                                                <div class='text-2xl font-bold text-{$color}-600'>{$remaining}</div>
                                            </div>
                                            <div class='bg-gray-50 p-3 rounded-lg'>
                                                <div class='text-xs text-gray-600 mb-1'>" . __('extra_service.usage_info.total') . "</div>
                                                <div class='text-2xl font-bold text-gray-900'>{$record->quantity_available}</div>
                                            </div>
                                        </div>

                                        <div>
                                            <div class='flex justify-between text-xs text-gray-600 mb-1'>
                                                <span>" . __('extra_service.usage_info.usage_progress') . "</span>
                                                <span>{$percentage}%</span>
                                            </div>
                                            <div class='w-full bg-gray-200 rounded-full h-3'>
                                                <div class='bg-{$color}-600 h-3 rounded-full transition-all' style='width: {$percentage}%'></div>
                                            </div>
                                        </div>

                                        <div class='grid grid-cols-2 gap-4 text-sm pt-3 border-t'>
                                            <div>
                                                <div class='text-xs text-gray-600'>" . __('extra_service.usage_info.revenue_generated') . "</div>
                                                <div class='text-lg font-bold text-green-600'>OMR" . number_format($totalRevenue, 3) . "</div>
                                            </div>
                                            <div>
                                                <div class='text-xs text-gray-600'>" . __('extra_service.usage_info.potential_revenue') . "</div>
                                                <div class='text-lg font-bold text-gray-900'>OMR" . number_format($potentialRevenue, 3) . "</div>
                                            </div>
                                        </div>
                                    </div>
                                ");
                            })
                            ->visible(fn($record) => $record !== null),
                    ])
                    ->visible(fn($record) => $record !== null)
                    ->collapsible()
                    ->columnSpan(2),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('event.title')
                    ->label(__('extra_service.columns.event'))
                    ->getStateUsing(fn($record) => $record->event->getTranslation('title', app()->getLocale()))
                    ->searchable(['title->en', 'title->ar'])
                    ->sortable()
                    ->weight('medium')
                    ->wrap()
                    ->limit(30),

                TextColumn::make('name')
                    ->label(__('extra_service.columns.name'))
                    ->getStateUsing(fn($record) => $record->getTranslation('name', app()->getLocale()))
                    ->searchable(['name->en', 'name->ar'])
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-shopping-bag'),

                TextColumn::make('price')
                    ->label(__('extra_service.columns.price'))
                    ->money('OMR')
                    ->sortable()
                    ->weight('medium')
                    ->alignEnd(),

                TextColumn::make('quantity_available')
                    ->label(__('extra_service.columns.total'))
                    ->getStateUsing(fn($record) => $record->quantity_available ?? '∞')
                    ->badge()
                    ->color(fn($record) => $record->quantity_available === null ? 'info' : 'gray')
                    ->alignCenter()
                    ->toggleable(),

                TextColumn::make('quantity_used')
                    ->label(__('extra_service.columns.used'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('remaining_quantity')
                    ->label(__('extra_service.columns.available'))
                    ->getStateUsing(function ($record) {
                        if ($record->quantity_available === null) {
                            return '∞';
                        }
                        return $record->getRemainingQuantity();
                    })
                    ->badge()
                    ->color(fn($state, $record) => match (true) {
                        $record->quantity_available === null       => 'info',
                        is_numeric($state) && $state > 50         => 'success',
                        is_numeric($state) && $state > 20         => 'warning',
                        is_numeric($state) && $state > 0          => 'danger',
                        default                                    => 'danger',
                    })
                    ->icon(fn($state, $record) => match (true) {
                        $record->quantity_available === null  => 'heroicon-o-check-circle',
                        is_numeric($state) && $state > 0     => 'heroicon-o-check-circle',
                        default                              => 'heroicon-o-x-circle',
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderByRaw("
                            CASE
                                WHEN quantity_available IS NULL THEN 999999
                                ELSE (quantity_available - quantity_used)
                            END {$direction}
                        ");
                    }),

                TextColumn::make('usage_percentage')
                    ->label(__('extra_service.columns.used_pct'))
                    ->getStateUsing(function ($record) {
                        if ($record->quantity_available === null) return 'N/A';
                        if ($record->quantity_available == 0) return '0%';
                        return round(($record->quantity_used / $record->quantity_available) * 100) . '%';
                    })
                    ->badge()
                    ->color(fn($state) => match (true) {
                        $state === 'N/A'       => 'gray',
                        intval($state) >= 90   => 'danger',
                        intval($state) >= 70   => 'warning',
                        intval($state) >= 50   => 'info',
                        default                => 'success',
                    })
                    ->alignCenter()
                    ->toggleable(),

                TextColumn::make('revenue')
                    ->label(__('extra_service.columns.revenue'))
                    ->getStateUsing(fn($record) => $record->quantity_used * $record->price)
                    ->money('OMR')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderByRaw("(quantity_used * price) {$direction}");
                    })
                    ->weight('bold')
                    ->color('success')
                    ->alignEnd()
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label(__('extra_service.columns.status'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter(),

                TextColumn::make('bookings_count')
                    ->counts('bookings')
                    ->label(__('extra_service.columns.bookings'))
                    ->badge()
                    ->color('primary')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(__('extra_service.columns.created_at'))
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('extra_service.columns.updated_at'))
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('event')
                    ->label(__('extra_service.filters.by_event'))
                    ->relationship('event', 'title')
                    ->getOptionLabelFromRecordUsing(fn($record) => $record->getTranslation('title', 'en'))
                    ->searchable()
                    ->preload()
                    ->multiple(),

                TernaryFilter::make('is_active')
                    ->label(__('extra_service.filters.status'))
                    ->placeholder(__('extra_service.filters.status_all'))
                    ->trueLabel(__('extra_service.filters.status_active'))
                    ->falseLabel(__('extra_service.filters.status_inactive')),

                TernaryFilter::make('quantity_limit')
                    ->label(__('extra_service.filters.quantity_type'))
                    ->placeholder(__('extra_service.filters.quantity_all'))
                    ->trueLabel(__('extra_service.filters.quantity_limited'))
                    ->falseLabel(__('extra_service.filters.quantity_unlimited'))
                    ->queries(
                        true: fn(Builder $query) => $query->whereNotNull('quantity_available'),
                        false: fn(Builder $query) => $query->whereNull('quantity_available'),
                    ),

                Filter::make('availability')
                    ->label(__('extra_service.filters.availability'))
                    ->schema([
                        Select::make('availability_status')
                            ->label(__('extra_service.filters.availability_show'))
                            ->options([
                                'available'       => __('extra_service.filters.avail_available'),
                                'depleted'        => __('extra_service.filters.avail_depleted'),
                                'almost_depleted' => __('extra_service.filters.avail_almost'),
                                'low_stock'       => __('extra_service.filters.avail_low'),
                            ])
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['availability_status'] === 'available',
                            fn(Builder $query): Builder => $query->where(function ($q) {
                                $q->whereNull('quantity_available')
                                    ->orWhereColumn('quantity_used', '<', 'quantity_available');
                            })
                        )
                            ->when(
                                $data['availability_status'] === 'depleted',
                                fn(Builder $query): Builder => $query->whereNotNull('quantity_available')
                                    ->whereColumn('quantity_used', '>=', 'quantity_available')
                            )
                            ->when(
                                $data['availability_status'] === 'almost_depleted',
                                fn(Builder $query): Builder => $query->whereNotNull('quantity_available')
                                    ->whereRaw('(quantity_used / quantity_available) >= 0.8')
                            )
                            ->when(
                                $data['availability_status'] === 'low_stock',
                                fn(Builder $query): Builder => $query->whereNotNull('quantity_available')
                                    ->whereRaw('(quantity_available - quantity_used) < 20')
                                    ->whereColumn('quantity_used', '<', 'quantity_available')
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        return match ($data['availability_status'] ?? null) {
                            'available'       => __('extra_service.filters.ind_available'),
                            'depleted'        => __('extra_service.filters.ind_depleted'),
                            'almost_depleted' => __('extra_service.filters.ind_almost'),
                            'low_stock'       => __('extra_service.filters.ind_low'),
                            default           => null,
                        };
                    }),

                Filter::make('price_range')
                    ->label(__('extra_service.filters.price_range'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('price_from')
                                    ->label(__('extra_service.filters.price_from'))
                                    ->numeric()
                                    ->prefix('OMR'),
                                TextInput::make('price_to')
                                    ->label(__('extra_service.filters.price_to'))
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
                            $indicators['price_from'] = __('extra_service.filters.ind_price_from', ['amount' => number_format($data['price_from'], 2)]);
                        }
                        if ($data['price_to'] ?? null) {
                            $indicators['price_to'] = __('extra_service.filters.ind_price_to', ['amount' => number_format($data['price_to'], 2)]);
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
                            ? __('extra_service.actions.deactivate')
                            : __('extra_service.actions.activate'))
                        ->icon(fn($record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                        ->color(fn($record) => $record->is_active ? 'danger' : 'success')
                        ->action(function (ExtraService $record) {
                            $record->update(['is_active' => !$record->is_active]);

                            Notification::make()
                                ->success()
                                ->title(__('extra_service.notifications.status_updated'))
                                ->body($record->is_active
                                    ? __('extra_service.notifications.service_activated')
                                    : __('extra_service.notifications.service_deactivated'))
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading(fn($record) => $record->is_active
                            ? __('extra_service.modals.deactivate_heading')
                            : __('extra_service.modals.activate_heading'))
                        ->modalDescription(fn($record) => $record->is_active
                            ? __('extra_service.modals.deactivate_description')
                            : __('extra_service.modals.activate_description')),

                    DeleteAction::make()
                        ->before(function (ExtraService $record) {
                            if ($record->bookings()->count() > 0) {
                                Notification::make()
                                    ->danger()
                                    ->title(__('extra_service.notifications.cannot_delete'))
                                    ->body(__('extra_service.notifications.has_bookings'))
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
                                    ->title(__('extra_service.notifications.cannot_delete'))
                                    ->body(__('extra_service.notifications.bulk_has_bookings', ['count' => $hasBookings->count()]))
                                    ->send();

                                return false;
                            }
                        }),

                    BulkAction::make('activate')
                        ->label(__('extra_service.actions.activate_selected'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => true]);

                            Notification::make()
                                ->success()
                                ->title(__('extra_service.notifications.services_activated'))
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('deactivate')
                        ->label(__('extra_service.actions.deactivate_selected'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => false]);

                            Notification::make()
                                ->success()
                                ->title(__('extra_service.notifications.services_deactivated'))
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation(),
                ]),
            ])
            ->emptyStateActions([
                CreateAction::make()
                    ->label(__('extra_service.actions.create_first'))
                    ->icon('heroicon-o-plus'),
            ])
            ->emptyStateHeading(__('extra_service.empty_state.heading'))
            ->emptyStateDescription(__('extra_service.empty_state.description'))
            ->emptyStateIcon('heroicon-o-shopping-bag');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'      => ListExtraServices::route('/'),
            'create'     => CreateExtraService::route('/create'),
            'edit'       => EditExtraService::route('/{record}/edit'),
            'activities' => ListExtraServiceActivities::route('/{record}/activities'),
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
