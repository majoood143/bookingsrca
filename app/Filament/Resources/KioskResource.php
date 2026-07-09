<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Size;
use App\Filament\Resources\KioskResource\Pages\ListKiosks;
use App\Filament\Resources\KioskResource\Pages\CreateKiosk;
use App\Filament\Resources\KioskResource\Pages\EditKiosk;
use App\Models\Kiosk;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class KioskResource extends Resource
{
    protected static ?string $model = Kiosk::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-device-tablet';
    protected static ?int $navigationSort = 30;

    public static function getNavigationGroup(): ?string
    {
        return __('kiosk.navigation.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('kiosk.navigation.plural');
    }

    public static function getModelLabel(): string
    {
        return __('kiosk.navigation.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('kiosk.navigation.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('kiosk.sections.information'))
                    ->description(__('kiosk.sections.information_desc'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('kiosk.fields.name'))
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('code')
                                    ->label(__('kiosk.fields.code'))
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->disabled(fn($record) => $record !== null)
                                    ->dehydrated()
                                    ->helperText(__('kiosk.fields.code_helper')),
                            ]),

                        Select::make('event_id')
                            ->label(__('kiosk.fields.event'))
                            ->relationship('event', 'title')
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->getTranslation('title', 'en'))
                            ->searchable(['title->en', 'title->ar'])
                            ->preload()
                            ->native(false)
                            ->helperText(__('kiosk.fields.event_helper')),

                        Toggle::make('is_active')
                            ->label(__('kiosk.fields.is_active'))
                            ->helperText(__('kiosk.fields.is_active_helper'))
                            ->default(true)
                            ->inline(false),
                    ])
                    ->columns(1),

                Section::make(__('kiosk.sections.payment'))
                    ->description(__('kiosk.sections.payment_desc'))
                    ->schema([
                        CheckboxList::make('enabled_payment_methods')
                            ->label(__('kiosk.fields.enabled_payment_methods'))
                            ->options(__('kiosk.payment_methods'))
                            ->default(['pay_at_counter'])
                            ->required()
                            ->columns(2),

                        TextInput::make('idle_timeout_seconds')
                            ->label(__('kiosk.fields.idle_timeout_seconds'))
                            ->helperText(__('kiosk.fields.idle_timeout_helper'))
                            ->numeric()
                            ->minValue(15)
                            ->maxValue(600)
                            ->default(90)
                            ->required(),
                    ])
                    ->columns(1),

                Section::make(__('kiosk.sections.receipt'))
                    ->description(__('kiosk.sections.receipt_desc'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Textarea::make('receipt_footer_text.en')
                                    ->label(__('kiosk.fields.receipt_footer_en'))
                                    ->rows(2),

                                Textarea::make('receipt_footer_text.ar')
                                    ->label(__('kiosk.fields.receipt_footer_ar'))
                                    ->rows(2),
                            ]),
                    ])
                    ->collapsible(),

                Section::make(__('kiosk.sections.hardware'))
                    ->description(__('kiosk.sections.hardware_desc'))
                    ->schema([
                        Placeholder::make('reader_connected')
                            ->label(__('kiosk.columns.reader'))
                            ->content(fn($record) => $record
                                ? ($record->reader_connected ? __('kiosk.columns.connected') : __('kiosk.columns.disconnected'))
                                . ' — ' . ($record->reader_last_seen_at?->diffForHumans() ?? __('kiosk.columns.never'))
                                : '-'),

                        Placeholder::make('printer_connected')
                            ->label(__('kiosk.columns.printer'))
                            ->content(fn($record) => $record
                                ? ($record->printer_connected ? __('kiosk.columns.connected') : __('kiosk.columns.disconnected'))
                                . ' — ' . ($record->printer_last_seen_at?->diffForHumans() ?? __('kiosk.columns.never'))
                                : '-'),

                        Placeholder::make('app_version')
                            ->label(__('kiosk.columns.app_version'))
                            ->content(fn($record) => $record?->app_version ?? '-'),
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
                TextColumn::make('name')
                    ->label(__('kiosk.columns.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('code')
                    ->label(__('kiosk.columns.code'))
                    ->badge()
                    ->searchable(),

                TextColumn::make('event')
                    ->label(__('kiosk.columns.event'))
                    ->getStateUsing(fn($record) => $record->event
                        ? $record->event->getTranslation('title', app()->getLocale())
                        : __('kiosk.columns.all_events'))
                    ->badge()
                    ->color(fn($record) => $record->event ? 'gray' : 'info'),

                TextColumn::make('enabled_payment_methods')
                    ->label(__('kiosk.columns.payment_methods'))
                    ->badge()
                    ->formatStateUsing(fn($state) => __('kiosk.payment_methods.' . $state)),

                IconColumn::make('reader_connected')
                    ->label(__('kiosk.columns.reader'))
                    ->boolean()
                    ->trueIcon('heroicon-o-signal')
                    ->falseIcon('heroicon-o-signal-slash')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter(),

                IconColumn::make('printer_connected')
                    ->label(__('kiosk.columns.printer'))
                    ->boolean()
                    ->trueIcon('heroicon-o-printer')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter(),

                IconColumn::make('is_active')
                    ->label(__('kiosk.columns.status'))
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter(),

                TextColumn::make('bookings_count')
                    ->counts('bookings')
                    ->label(__('kiosk.columns.bookings'))
                    ->badge()
                    ->color('primary'),
            ])
            ->defaultSort('name')
            ->filters([
                SelectFilter::make('event')
                    ->label(__('kiosk.fields.event'))
                    ->relationship('event', 'title')
                    ->getOptionLabelFromRecordUsing(fn($record) => $record->getTranslation('title', 'en'))
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('is_active')
                    ->label(__('kiosk.columns.status')),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),

                    Action::make('toggle_active')
                        ->label(fn($record) => $record->is_active ? __('kiosk.actions.deactivate') : __('kiosk.actions.activate'))
                        ->icon(fn($record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                        ->color(fn($record) => $record->is_active ? 'danger' : 'success')
                        ->action(function (Kiosk $record) {
                            $record->update(['is_active' => !$record->is_active]);

                            Notification::make()
                                ->success()
                                ->title($record->is_active ? __('kiosk.notifications.kiosk_activated') : __('kiosk.notifications.kiosk_deactivated'))
                                ->send();
                        })
                        ->requiresConfirmation(),

                    DeleteAction::make(),
                ])
                    ->label('More actions')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size(Size::Small)
                    ->color('primary')
                    ->button(),
            ])
            ->emptyStateActions([
                CreateAction::make()->label(__('kiosk.actions.new')),
            ])
            ->emptyStateHeading(__('kiosk.empty_state.heading'))
            ->emptyStateDescription(__('kiosk.empty_state.description'))
            ->emptyStateIcon('heroicon-o-device-tablet');
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListKiosks::route('/'),
            'create' => CreateKiosk::route('/create'),
            'edit'   => EditKiosk::route('/{record}/edit'),
        ];
    }
}
