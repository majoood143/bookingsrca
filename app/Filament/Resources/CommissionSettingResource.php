<?php

namespace App\Filament\Resources;

use App\Enums\CommissionType;
use App\Filament\Resources\CommissionSettingResource\Pages\CreateCommissionSetting;
use App\Filament\Resources\CommissionSettingResource\Pages\EditCommissionSetting;
use App\Filament\Resources\CommissionSettingResource\Pages\ListCommissionSettings;
use App\Models\BookingSetting;
use App\Models\CommissionSetting;
use App\Models\Event;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CommissionSettingResource extends Resource
{
    protected static ?string $model = CommissionSetting::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calculator';

    protected static ?int $navigationSort = 9;

    public static function canAccess(): bool
    {
        return parent::canAccess() && (bool) BookingSetting::get('module_commission_enabled', true);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('commission_setting.navigation.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('commission_setting.navigation.plural');
    }

    public static function getModelLabel(): string
    {
        return __('commission_setting.navigation.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('commission_setting.navigation.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('commission_setting.sections.scope'))
                    ->description(__('commission_setting.sections.scope_desc'))
                    ->schema([
                        Select::make('event_id')
                            ->label(__('commission_setting.fields.event'))
                            ->relationship('event', 'title')
                            ->getOptionLabelFromRecordUsing(fn (Event $record) => $record->getTranslation('title', app()->getLocale()))
                            ->searchable(['title->en', 'title->ar'])
                            ->preload()
                            ->native(false)
                            ->helperText(__('commission_setting.fields.event_helper')),

                        Placeholder::make('scope_note')
                            ->label('')
                            ->content(fn (): string => '💡 ' . __('commission_setting.fields.scope_note'))
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make(__('commission_setting.sections.details'))
                    ->schema([
                        TextInput::make('name.en')
                            ->label(__('commission_setting.fields.name_en'))
                            ->required()
                            ->maxLength(255),

                        TextInput::make('name.ar')
                            ->label(__('commission_setting.fields.name_ar'))
                            ->required()
                            ->maxLength(255)
                            ->extraInputAttributes(['dir' => 'rtl']),

                        Select::make('commission_type')
                            ->label(__('commission_setting.fields.commission_type'))
                            ->options(CommissionType::toArray())
                            ->required()
                            ->default(CommissionType::Percentage->value)
                            ->live()
                            ->native(false),

                        TextInput::make('commission_value')
                            ->label(__('commission_setting.fields.commission_value'))
                            ->numeric()
                            ->required()
                            ->step(0.001)
                            ->minValue(0)
                            ->maxValue(fn (Get $get): float => $get('commission_type') === CommissionType::Percentage->value ? 100 : 999999)
                            ->suffix(fn (Get $get): string => $get('commission_type') === CommissionType::Percentage->value ? '%' : BookingSetting::currencySymbol()),

                        Textarea::make('description.en')
                            ->label(__('commission_setting.fields.description_en'))
                            ->rows(3),

                        Textarea::make('description.ar')
                            ->label(__('commission_setting.fields.description_ar'))
                            ->rows(3)
                            ->extraInputAttributes(['dir' => 'rtl']),
                    ])
                    ->columns(2),

                Section::make(__('commission_setting.sections.validity'))
                    ->schema([
                        DatePicker::make('effective_from')
                            ->label(__('commission_setting.fields.effective_from'))
                            ->native(false)
                            ->helperText(__('commission_setting.fields.effective_from_helper')),

                        DatePicker::make('effective_to')
                            ->label(__('commission_setting.fields.effective_to'))
                            ->native(false)
                            ->helperText(__('commission_setting.fields.effective_to_helper'))
                            ->afterOrEqual('effective_from'),

                        Toggle::make('is_active')
                            ->label(__('commission_setting.fields.is_active'))
                            ->default(true)
                            ->inline(false),
                    ])
                    ->columns(3),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('commission_setting.columns.scope'))
                    ->badge()
                    ->color(fn (CommissionSetting $record): string => $record->is_global ? 'primary' : 'success')
                    ->formatStateUsing(fn (CommissionSetting $record): string => $record->is_global
                        ? __('commission_setting.columns.global')
                        : __('commission_setting.columns.event_specific', ['name' => $record->event?->getTranslation('title', app()->getLocale()) ?? '—'])),

                TextColumn::make('name')
                    ->label(__('commission_setting.columns.name'))
                    ->formatStateUsing(fn (CommissionSetting $record) => $record->getTranslation('name', app()->getLocale()))
                    ->searchable(['name->en', 'name->ar'])
                    ->limit(30),

                TextColumn::make('commission_type')
                    ->label(__('commission_setting.columns.type'))
                    ->badge()
                    ->sortable(),

                TextColumn::make('commission_value')
                    ->label(__('commission_setting.columns.value'))
                    ->formatStateUsing(fn (CommissionSetting $record) => $record->formatted_rate)
                    ->sortable(),

                TextColumn::make('effective_from')
                    ->label(__('commission_setting.columns.effective_from'))
                    ->date()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('effective_to')
                    ->label(__('commission_setting.columns.effective_to'))
                    ->date()
                    ->sortable()
                    ->toggleable()
                    ->placeholder(__('commission_setting.columns.indefinite')),

                IconColumn::make('is_active')
                    ->label(__('commission_setting.columns.is_active'))
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('commission_setting.columns.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('commission_type')
                    ->label(__('commission_setting.columns.type'))
                    ->options(CommissionType::toArray()),

                TernaryFilter::make('is_active')
                    ->label(__('commission_setting.filters.active'))
                    ->boolean()
                    ->native(false),

                Filter::make('scope')
                    ->label(__('commission_setting.columns.scope'))
                    ->schema([
                        Select::make('scope_type')
                            ->label(__('commission_setting.filters.scope_type'))
                            ->options([
                                'global' => __('commission_setting.columns.global'),
                                'event' => __('commission_setting.filters.event_specific'),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['scope_type'] === 'global', fn (Builder $q) => $q->whereNull('event_id'))
                            ->when($data['scope_type'] === 'event', fn (Builder $q) => $q->whereNotNull('event_id'));
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCommissionSettings::route('/'),
            'create' => CreateCommissionSetting::route('/create'),
            'edit' => EditCommissionSetting::route('/{record}/edit'),
        ];
    }
}
