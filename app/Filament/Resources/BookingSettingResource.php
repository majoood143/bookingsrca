<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\ColorPicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Support\Enums\Size;
use App\Filament\Resources\BookingSettingResource\Pages\ListBookingSettings;
use App\Filament\Resources\BookingSettingResource\Pages\EditBookingSetting;
use App\Filament\Resources\BookingSettingResource\Pages\ListBookingSettingActivities;
use App\Filament\Resources\BookingSettingResource\Pages;
use App\Models\BookingSetting;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BookingSettingResource extends Resource
{
    protected static ?string $model = BookingSetting::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?int $navigationSort = 99;

    public static function getNavigationLabel(): string
    {
        return __('booking_setting.navigation.label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('booking_setting.navigation.group');
    }

    public static function getModelLabel(): string
    {
        return __('booking_setting.navigation.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('booking_setting.navigation.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('booking_setting.sections.configuration'))
                    ->schema([
                        TextInput::make('key')
                            ->label(__('booking_setting.fields.key'))
                            ->required()
                            ->disabled()
                            ->helperText(__('booking_setting.fields.key_helper')),

                        Toggle::make('value')
                            ->label(__('booking_setting.fields.value'))
                    // ->onLabel(__('booking_setting.fields.enabled'))
                    // ->offLabel(__('booking_setting.fields.disabled'))
                    ->onIcon('heroicon-m-check')
                    ->offIcon('heroicon-m-x-mark')
                            ->afterStateHydrated(function (Toggle $component, $state) {
                                $component->state(filter_var($state, FILTER_VALIDATE_BOOLEAN));
                            })
                            ->dehydrateStateUsing(fn($state) => $state ? 'true' : 'false')
                            ->helperText(fn($record) => $record?->description)
                            ->visible(fn($record) => $record && $record->type === 'boolean'),

                        RichEditor::make('value')
                            ->label(__('booking_setting.fields.value'))
                            ->helperText(fn($record) => $record?->description)
                            ->fileAttachmentsDisk('public')
                            ->visible(fn($record) => $record && $record->type === 'richtext'),

                        FileUpload::make('value')
                            ->label(__('booking_setting.fields.value'))
                            ->image()
                            ->disk('public')
                            ->directory('branding')
                            ->visibility('public')
                            ->helperText(fn($record) => $record?->description)
                            ->visible(fn($record) => $record && $record->type === 'file'),

                        ColorPicker::make('value')
                            ->label(__('booking_setting.fields.value'))
                            ->helperText(fn($record) => $record?->description)
                            ->visible(fn($record) => $record && $record->type === 'color'),

                        TextInput::make('value')
                            ->label(__('booking_setting.fields.value'))
                            ->required()
                            ->numeric(fn($record) => $record && $record->type === 'number')
                            ->minValue(fn($record) => $record && $record->key === 'min_tickets_per_booking' ? 1 : ($record && $record->key === 'max_attendee_age_years' ? 1 : 0))
                            ->maxValue(fn($record) => $record && $record->key === 'max_tickets_per_booking' ? 1000 : ($record && $record->key === 'max_attendee_age_years' ? 120 : null))
                            ->helperText(fn($record) => $record?->description)
                            ->visible(fn($record) => !$record || !in_array($record->type, ['boolean', 'richtext', 'file', 'color'])),

                        Textarea::make('description')
                            ->label(__('booking_setting.fields.description'))
                            ->disabled()
                            ->rows(2),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->label(__('booking_setting.columns.setting'))
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn($state) => str_replace('_', ' ', ucwords($state, '_'))),

                TextColumn::make('value')
                    ->label(__('booking_setting.columns.current_value'))
                    ->badge(fn($record) => !in_array($record->type, ['file', 'color']))
                    ->color(fn($state, $record) => $record->type === 'boolean'
                        ? (filter_var($state, FILTER_VALIDATE_BOOLEAN) ? 'success' : 'danger')
                        : 'success'
                    )
                    ->html()
                    ->formatStateUsing(fn($state, $record) => match($record->type) {
                        'boolean' => filter_var($state, FILTER_VALIDATE_BOOLEAN)
                            ? __('booking_setting.fields.enabled')
                            : __('booking_setting.fields.disabled'),
                        'richtext' => strlen(strip_tags($state)) > 0
                            ? __('booking_setting.fields.content_set')
                            : __('booking_setting.fields.not_set'),
                        'file' => filled($state)
                            ? '<img src="' . e(\Illuminate\Support\Facades\Storage::disk('public')->url($state)) . '" class="h-8 w-auto rounded" alt="">'
                            : __('booking_setting.fields.not_set'),
                        'color' => filled($state)
                            ? '<span class="inline-flex items-center gap-1.5"><span class="inline-block h-3 w-3 rounded-full border border-gray-300" style="background-color:' . e($state) . '"></span>' . e($state) . '</span>'
                            : __('booking_setting.fields.not_set'),
                        default => e($state),
                    }),

                TextColumn::make('description')
                    ->label(__('booking_setting.columns.description'))
                    ->wrap()
                    ->limit(50),

                TextColumn::make('updated_at')
                    ->label(__('booking_setting.columns.last_modified'))
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
            ])
            ->filters([])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                ])
                    ->label('More actions')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size(Size::Small)
                    ->color('primary')
                    ->button(),
            ])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index'      => ListBookingSettings::route('/'),
            'edit'       => EditBookingSetting::route('/{record}/edit'),
            'activities' => ListBookingSettingActivities::route('/{record}/activities'),
        ];
    }
}
