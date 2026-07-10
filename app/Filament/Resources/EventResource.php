<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\Filter;
use Carbon\Carbon;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Size;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkAction;
use Filament\Actions\CreateAction;
use App\Filament\Resources\EventResource\Pages\ListEvents;
use App\Filament\Resources\EventResource\Pages\CreateEvent;
use App\Filament\Resources\EventResource\Pages\EditEvent;
use App\Filament\Resources\EventResource\Pages\ViewEvent;
use App\Filament\Resources\EventResource\Pages\ListEventActivities;
use App\Filament\Resources\EventResource\Pages;
use App\Filament\Resources\EventResource\RelationManagers;
use App\Models\Event;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('event.navigation.group');
    }

    public static function getModelLabel(): string
    {
        return __('event.navigation.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('event.navigation.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('event.sections.information'))
                    ->description(__('event.sections.information_desc'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('title.en')
                                    ->label(__('event.fields.title_en'))
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                        if (! $get('slug')) {
                                            $set('slug', Str::slug($state));
                                        }
                                    }),

                                TextInput::make('title.ar')
                                    ->label(__('event.fields.title_ar'))
                                    ->required()
                                    ->maxLength(255),
                            ]),

                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText(__('event.fields.slug_helper')),

                        Grid::make(2)
                            ->schema([
                                Textarea::make('description.en')
                                    ->label(__('event.fields.description_en'))
                                    ->required()
                                    ->rows(4)
                                    ->columnSpanFull(),

                                Textarea::make('description.ar')
                                    ->label(__('event.fields.description_ar'))
                                    ->required()
                                    ->rows(4)
                                    ->columnSpanFull(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('organizer.en')
                                    ->label(__('event.fields.organizer_en'))
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder(__('event.placeholders.organizer_en')),

                                TextInput::make('organizer.ar')
                                    ->label(__('event.fields.organizer_ar'))
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder(__('event.placeholders.organizer_ar')),
                            ]),
                    ])
                    ->columns(1)
                    ->collapsible(),

                Section::make(__('event.sections.location_schedule'))
                    ->description(__('event.sections.location_desc'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('location.en')
                                    ->label(__('event.fields.location_en'))
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder(__('event.placeholders.location_en')),

                                TextInput::make('location.ar')
                                    ->label(__('event.fields.location_ar'))
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder(__('event.placeholders.location_ar')),
                            ]),

                        Grid::make(3)
                            ->schema([
                                DatePicker::make('start_date')
                                    ->label(__('event.fields.start_date'))
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('Y-m-d')
                                    ->default(now()),

                                DatePicker::make('end_date')
                                    ->label(__('event.fields.end_date'))
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('Y-m-d')
                                    ->after('start_date')
                                    ->default(now()->addDays(1)),

                                TextInput::make('max_attendees')
                                    ->label(__('event.fields.max_attendees'))
                                    ->required()
                                    ->numeric()
                                    ->default(100)
                                    ->minValue(1)
                                    ->suffix(__('event.suffix.people'))
                                    ->helperText(__('event.fields.max_attendees_helper')),
                            ]),
                    ])
                    ->columns(1)
                    ->collapsible(),

                Section::make(__('event.sections.recurring'))
                    ->description(__('event.sections.recurring_desc'))
                    ->schema([
                        Toggle::make('is_recurring')
                            ->label(__('event.fields.is_recurring'))
                            ->helperText(__('event.fields.is_recurring_helper'))
                            ->default(false)
                            ->live()
                            ->columnSpanFull(),

                        CheckboxList::make('recurring_days')
                            ->label(__('event.fields.recurring_days'))
                            ->options(__('event.options.days'))
                            ->columns(4)
                            ->gridDirection('row')
                            ->visible(fn(Get $get): bool => $get('is_recurring') === true)
                            ->required(fn(Get $get): bool => $get('is_recurring') === true)
                            ->helperText(__('event.fields.recurring_days_helper')),
                    ])
                    ->columns(1)
                    ->collapsible()
                    ->collapsed(),

                Section::make(__('event.sections.media_status'))
                    ->description(__('event.sections.media_desc'))
                    ->schema([
                        FileUpload::make('image')
                            ->label(__('event.fields.image'))
                            ->image()
                            ->disk('public')
                            ->directory('events')
                            ->visibility('public')
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->maxSize(2048)
                            ->helperText(__('event.fields.image_helper'))
                            ->columnSpanFull(),

                        Select::make('status')
                            ->label(__('event.fields.status'))
                            ->options(__('event.options.status'))
                            ->default('draft')
                            ->required()
                            ->native(false)
                            ->live()
                            ->helperText(__('event.fields.status_helper')),

                        TextInput::make('password')
                            ->label(__('event.fields.password'))
                            ->maxLength(255)
                            ->password()
                            ->revealable()
                            ->visible(fn (Get $get): bool => $get('status') === 'private')
                            ->required(fn (Get $get): bool => $get('status') === 'private')
                            ->helperText(__('event.fields.password_helper')),
                    ])
                    ->columns(1)
                    ->collapsible(),

                Section::make(__('event.sections.signage'))
                    ->description(__('event.sections.signage_desc'))
                    ->relationship('signageSetting')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                FileUpload::make('logo_path')
                                    ->label(__('event.fields.signage_logo'))
                                    ->image()
                                    ->disk('public')
                                    ->directory('signage/logos')
                                    ->visibility('public')
                                    ->helperText(__('event.fields.signage_logo_helper')),

                                FileUpload::make('background_image_path')
                                    ->label(__('event.fields.signage_background'))
                                    ->image()
                                    ->disk('public')
                                    ->directory('signage/backgrounds')
                                    ->visibility('public')
                                    ->helperText(__('event.fields.signage_background_helper')),

                                FileUpload::make('qr_code_image_path')
                                    ->label(__('event.fields.signage_qr'))
                                    ->image()
                                    ->disk('public')
                                    ->directory('signage/qrcodes')
                                    ->visibility('public')
                                    ->helperText(__('event.fields.signage_qr_helper')),
                            ]),

                        TextInput::make('contact_phone')
                            ->label(__('event.fields.signage_phone'))
                            ->tel()
                            ->maxLength(50)
                            ->placeholder('+968 1234 5678'),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('meeting_point.en')
                                    ->label(__('event.fields.signage_meeting_point_en'))
                                    ->maxLength(255),

                                TextInput::make('meeting_point.ar')
                                    ->label(__('event.fields.signage_meeting_point_ar'))
                                    ->maxLength(255),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('welcome_message.en')
                                    ->label(__('event.fields.signage_welcome_en'))
                                    ->maxLength(255),

                                TextInput::make('welcome_message.ar')
                                    ->label(__('event.fields.signage_welcome_ar'))
                                    ->maxLength(255),
                            ]),

                        TextInput::make('language_switch_seconds')
                            ->label(__('event.fields.signage_language_switch'))
                            ->numeric()
                            ->minValue(0)
                            ->default(10)
                            ->suffix(__('event.suffix.seconds'))
                            ->helperText(__('event.fields.signage_language_switch_helper')),

                        Grid::make(5)
                            ->schema([
                                TextInput::make('early_arrival_minutes')
                                    ->label(__('event.fields.signage_early_arrival'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(5),

                                TextInput::make('gathering_alert_minutes')
                                    ->label(__('event.fields.signage_gathering_alert'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(5),

                                TextInput::make('ready_threshold_minutes')
                                    ->label(__('event.fields.signage_ready_threshold'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(15),

                                TextInput::make('soon_threshold_minutes')
                                    ->label(__('event.fields.signage_soon_threshold'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(60),

                                TextInput::make('upcoming_trips_count')
                                    ->label(__('event.fields.signage_upcoming_count'))
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(20)
                                    ->default(4),
                            ]),

                        Toggle::make('is_enabled')
                            ->label(__('event.fields.signage_enabled'))
                            ->helperText(__('event.fields.signage_enabled_helper'))
                            ->default(true),
                    ])
                    ->columns(1)
                    ->collapsible()
                    ->collapsed(),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label(__('event.columns.image'))
                    ->circular()
                    ->defaultImageUrl(url('/images/placeholder-event.png')),

                TextColumn::make('title')
                    ->label(__('event.columns.title'))
                    ->getStateUsing(fn($record) => $record->getTranslation('title', app()->getLocale()))
                    ->searchable(['title->en', 'title->ar'])
                    ->sortable()
                    ->weight('bold')
                    ->wrap(),

                TextColumn::make('organizer')
                    ->label(__('event.columns.organizer'))
                    ->getStateUsing(fn($record) => $record->getTranslation('organizer', app()->getLocale()))
                    ->searchable(['organizer->en', 'organizer->ar'])
                    ->toggleable(),

                TextColumn::make('location')
                    ->label(__('event.columns.location'))
                    ->getStateUsing(fn($record) => $record->getTranslation('location', app()->getLocale()))
                    ->searchable(['location->en', 'location->ar'])
                    ->toggleable()
                    ->limit(30),

                TextColumn::make('start_date')
                    ->label(__('event.columns.start_date'))
                    ->date('M d, Y')
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label(__('event.columns.end_date'))
                    ->date('M d, Y')
                    ->sortable(),

                IconColumn::make('is_recurring')
                    ->label(__('event.columns.is_recurring'))
                    ->boolean()
                    ->trueIcon('heroicon-o-arrow-path')
                    ->falseIcon('heroicon-o-calendar')
                    ->trueColor('info')
                    ->falseColor('gray')
                    ->toggleable(),

                BadgeColumn::make('status')
                    ->label(__('event.columns.status'))
                    ->colors([
                        'secondary' => 'draft',
                        'success'   => 'published',
                        'danger'    => 'cancelled',
                        'warning'   => 'private',
                    ])
                    ->icons([
                        'heroicon-o-pencil'       => 'draft',
                        'heroicon-o-check-circle' => 'published',
                        'heroicon-o-x-circle'     => 'cancelled',
                        'heroicon-o-lock-closed'  => 'private',
                    ]),

                TextColumn::make('bookings_count')
                    ->counts('bookings')
                    ->label(__('event.columns.bookings'))
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('max_attendees')
                    ->label(__('event.columns.capacity'))
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(__('event.columns.created_at'))
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('event.columns.updated_at'))
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label(__('event.filters.status'))
                    ->options(__('event.options.status'))
                    ->multiple(),

                TernaryFilter::make('is_recurring')
                    ->label(__('event.filters.recurring'))
                    ->placeholder(__('event.filters.recurring_all'))
                    ->trueLabel(__('event.filters.recurring_true'))
                    ->falseLabel(__('event.filters.recurring_false')),

                Filter::make('start_date')
                    ->label(__('event.filters.start_date'))
                    ->schema([
                        DatePicker::make('start_from')
                            ->label(__('event.filters.start_from'))
                            ->native(false),
                        DatePicker::make('start_until')
                            ->label(__('event.filters.start_until'))
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['start_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('start_date', '>=', $date),
                            )
                            ->when(
                                $data['start_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('start_date', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['start_from'] ?? null) {
                            $indicators['start_from'] = __('event.filters.indicator_from', [
                                'date' => Carbon::parse($data['start_from'])->toFormattedDateString(),
                            ]);
                        }
                        if ($data['start_until'] ?? null) {
                            $indicators['start_until'] = __('event.filters.indicator_until', [
                                'date' => Carbon::parse($data['start_until'])->toFormattedDateString(),
                            ]);
                        }
                        return $indicators;
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),

                    Action::make('viewOnSite')
                        ->label(__('event.actions.view_on_site'))
                        ->icon('heroicon-o-arrow-top-right-on-square')
                        ->color('info')
                        ->url(fn(Event $record): string => route('event.booking', $record->slug))
                        ->openUrlInNewTab(),

                    Action::make('duplicate')
                        ->label(__('event.actions.duplicate'))
                        ->icon('heroicon-o-document-duplicate')
                        ->color('secondary')
                        ->action(function (Event $record) {
                            $newEvent = $record->replicate();
                            $newEvent->slug = $record->slug . '-copy-' . time();
                            $newEvent->status = 'draft';
                            $newEvent->save();

                            Notification::make()
                                ->success()
                                ->title(__('event.notifications.duplicated'))
                                ->body(__('event.notifications.duplicated_body'))
                                ->send();
                        })
                        ->requiresConfirmation(),
                ])
                    ->label('More actions')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size(Size::Small)
                    ->color('primary')
                    ->button(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),

                    BulkAction::make('publish')
                        ->label(__('event.actions.publish_selected'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each->update(['status' => 'published']);
                            Notification::make()
                                ->success()
                                ->title(__('event.notifications.published'))
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation(),

                    BulkAction::make('draft')
                        ->label(__('event.actions.move_to_draft'))
                        ->icon('heroicon-o-pencil')
                        ->color('secondary')
                        ->action(function ($records) {
                            $records->each->update(['status' => 'draft']);
                            Notification::make()
                                ->success()
                                ->title(__('event.notifications.moved_to_draft'))
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->emptyStateActions([
                CreateAction::make()
                    ->label(__('event.actions.create_first'))
                    ->icon('heroicon-o-plus'),
            ])
            ->emptyStateHeading(__('event.empty_state.heading'))
            ->emptyStateDescription(__('event.empty_state.description'))
            ->emptyStateIcon('heroicon-o-calendar-days');
    }

    public static function getRelations(): array
    {
        return [
            // RelationManagers\TimeSlotsRelationManager::class,
            // RelationManagers\TicketTypesRelationManager::class,
            // RelationManagers\ExtraServicesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'      => ListEvents::route('/'),
            'create'     => CreateEvent::route('/create'),
            'edit'       => EditEvent::route('/{record}/edit'),
            'view'       => ViewEvent::route('/{record}'),
            'activities' => ListEventActivities::route('/{record}/activities'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'published')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
