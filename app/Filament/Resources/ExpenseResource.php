<?php

namespace App\Filament\Resources;

use App\Enums\ExpensePaymentMethod;
use App\Enums\ExpensePaymentStatus;
use App\Enums\ExpenseType;
use App\Enums\RecurringFrequency;
use App\Filament\Resources\ExpenseResource\Pages\CreateExpense;
use App\Filament\Resources\ExpenseResource\Pages\EditExpense;
use App\Filament\Resources\ExpenseResource\Pages\ListExpenses;
use App\Filament\Resources\ExpenseResource\Pages\ViewExpense;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';
    protected static ?int $navigationSort = 7;

    public static function canAccess(): bool
    {
        return parent::canAccess() && (bool) \App\Models\BookingSetting::get('module_expenses_enabled', true);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('expense.navigation.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('expense.navigation.plural');
    }

    public static function getModelLabel(): string
    {
        return __('expense.navigation.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('expense.navigation.plural');
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()
            ->where('payment_status', ExpensePaymentStatus::Pending)
            ->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('expense.sections.information'))
                    ->description(__('expense.sections.information_desc'))
                    ->schema([
                        TextInput::make('expense_number')
                            ->label(__('expense.fields.expense_number'))
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn ($record) => $record !== null),

                        Select::make('expense_type')
                            ->label(__('expense.fields.expense_type'))
                            ->options(ExpenseType::toArray())
                            ->required()
                            ->default(ExpenseType::Operational->value)
                            ->live()
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                if ($state !== ExpenseType::Event->value) {
                                    $set('booking_id', null);
                                }
                                if ($state !== ExpenseType::Recurring->value) {
                                    $set('is_recurring', false);
                                    $set('recurring_frequency', null);
                                }
                            })
                            ->helperText(fn ($state) => $state ? ExpenseType::tryFrom($state)?->getDescription() : null),

                        Select::make('event_id')
                            ->label(__('expense.fields.event'))
                            ->relationship('event', 'title')
                            ->getOptionLabelFromRecordUsing(fn (Event $record) => $record->getTranslation('title', app()->getLocale()))
                            ->searchable(['title->en', 'title->ar'])
                            ->preload()
                            ->native(false)
                            ->live()
                            ->helperText(__('expense.fields.event_helper')),

                        Select::make('booking_id')
                            ->label(__('expense.fields.booking'))
                            ->options(function (Get $get) {
                                $eventId = $get('event_id');

                                $query = Booking::query();

                                if ($eventId) {
                                    $query->where('event_id', $eventId);
                                }

                                return $query
                                    ->orderBy('event_date', 'desc')
                                    ->limit(100)
                                    ->get()
                                    ->mapWithKeys(fn (Booking $booking) => [
                                        $booking->id => "{$booking->booking_reference} ({$booking->event_date->format('Y-m-d')})",
                                    ]);
                            })
                            ->searchable()
                            ->native(false)
                            ->visible(fn (Get $get) => $get('expense_type') === ExpenseType::Event->value)
                            ->helperText(__('expense.fields.booking_helper')),

                        Select::make('category_id')
                            ->label(__('expense.fields.category'))
                            ->relationship(
                                name: 'category',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query) => $query->where('is_active', true),
                            )
                            ->getOptionLabelFromRecordUsing(fn (ExpenseCategory $record) => $record->getTranslation('name', app()->getLocale()))
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->createOptionForm([
                                TextInput::make('name.en')
                                    ->label(__('expense_category.fields.name_en'))
                                    ->required(),
                                TextInput::make('name.ar')
                                    ->label(__('expense_category.fields.name_ar'))
                                    ->required(),
                                ColorPicker::make('color')
                                    ->label(__('expense_category.fields.color'))
                                    ->default('#6366f1'),
                            ])
                            ->createOptionUsing(fn (array $data) => ExpenseCategory::create($data)->id),
                    ])
                    ->columns(2),

                Section::make(__('expense.sections.description'))
                    ->schema([
                        Tabs::make('title_tabs')
                            ->tabs([
                                Tab::make(__('expense.fields.title_en'))
                                    ->schema([
                                        TextInput::make('title.en')
                                            ->label(__('expense.fields.title_en'))
                                            ->required()
                                            ->maxLength(255),
                                    ]),
                                Tab::make(__('expense.fields.title_ar'))
                                    ->schema([
                                        TextInput::make('title.ar')
                                            ->label(__('expense.fields.title_ar'))
                                            ->required()
                                            ->maxLength(255)
                                            ->extraInputAttributes(['dir' => 'rtl']),
                                    ]),
                            ])
                            ->columnSpanFull(),

                        Tabs::make('description_tabs')
                            ->tabs([
                                Tab::make(__('expense.fields.description_en'))
                                    ->schema([
                                        Textarea::make('description.en')
                                            ->label(__('expense.fields.description_en'))
                                            ->rows(3),
                                    ]),
                                Tab::make(__('expense.fields.description_ar'))
                                    ->schema([
                                        Textarea::make('description.ar')
                                            ->label(__('expense.fields.description_ar'))
                                            ->rows(3)
                                            ->extraInputAttributes(['dir' => 'rtl']),
                                    ]),
                            ])
                            ->columnSpanFull(),
                    ]),

                Section::make(__('expense.sections.financial'))
                    ->schema([
                        TextInput::make('amount')
                            ->label(__('expense.fields.amount'))
                            ->required()
                            ->numeric()
                            ->minValue(0.001)
                            ->step(0.001)
                            ->suffix('OMR')
                            ->live(onBlur: true),

                        TextInput::make('tax_amount')
                            ->label(__('expense.fields.tax_amount'))
                            ->numeric()
                            ->minValue(0)
                            ->step(0.001)
                            ->default(0)
                            ->suffix('OMR')
                            ->live(onBlur: true),

                        Placeholder::make('calculated_total')
                            ->label(__('expense.fields.total'))
                            ->content(function (Get $get) {
                                $amount = (float) ($get('amount') ?? 0);
                                $tax = (float) ($get('tax_amount') ?? 0);

                                return number_format($amount + $tax, 3) . ' OMR';
                            }),

                        DatePicker::make('expense_date')
                            ->label(__('expense.fields.expense_date'))
                            ->required()
                            ->default(now())
                            ->maxDate(now()),

                        Select::make('payment_method')
                            ->label(__('expense.fields.payment_method'))
                            ->options(ExpensePaymentMethod::toArray())
                            ->default(ExpensePaymentMethod::Cash->value)
                            ->required()
                            ->native(false)
                            ->live(),

                        Select::make('payment_status')
                            ->label(__('expense.fields.payment_status'))
                            ->options(ExpensePaymentStatus::toArray())
                            ->default(ExpensePaymentStatus::Paid->value)
                            ->required()
                            ->native(false)
                            ->live(),

                        TextInput::make('payment_reference')
                            ->label(__('expense.fields.payment_reference'))
                            ->maxLength(100)
                            ->visible(fn (Get $get) => ExpensePaymentMethod::tryFrom($get('payment_method') ?? '')?->requiresReference() ?? false),

                        DatePicker::make('due_date')
                            ->label(__('expense.fields.due_date'))
                            ->visible(fn (Get $get) => in_array($get('payment_status'), [
                                ExpensePaymentStatus::Pending->value,
                                ExpensePaymentStatus::Partial->value,
                            ])),
                    ])
                    ->columns(3),

                Section::make(__('expense.sections.vendor'))
                    ->schema([
                        TextInput::make('vendor_name')
                            ->label(__('expense.fields.vendor_name'))
                            ->maxLength(255),

                        TextInput::make('vendor_phone')
                            ->label(__('expense.fields.vendor_phone'))
                            ->tel()
                            ->maxLength(20),

                        TextInput::make('vendor_email')
                            ->label(__('expense.fields.vendor_email'))
                            ->email()
                            ->maxLength(255),
                    ])
                    ->columns(3)
                    ->collapsed()
                    ->collapsible(),

                Section::make(__('expense.sections.recurring'))
                    ->schema([
                        Toggle::make('is_recurring')
                            ->label(__('expense.fields.is_recurring'))
                            ->live()
                            ->default(false),

                        Select::make('recurring_frequency')
                            ->label(__('expense.fields.recurring_frequency'))
                            ->options(RecurringFrequency::toArray())
                            ->native(false)
                            ->visible(fn (Get $get) => $get('is_recurring'))
                            ->required(fn (Get $get) => $get('is_recurring')),

                        DatePicker::make('recurring_start_date')
                            ->label(__('expense.fields.recurring_start_date'))
                            ->visible(fn (Get $get) => $get('is_recurring'))
                            ->required(fn (Get $get) => $get('is_recurring')),

                        DatePicker::make('recurring_end_date')
                            ->label(__('expense.fields.recurring_end_date'))
                            ->visible(fn (Get $get) => $get('is_recurring'))
                            ->helperText(__('expense.fields.recurring_end_date_helper')),
                    ])
                    ->columns(2)
                    ->visible(fn (Get $get) => $get('expense_type') === ExpenseType::Recurring->value)
                    ->collapsible(),

                Section::make(__('expense.sections.attachments'))
                    ->schema([
                        FileUpload::make('attachments')
                            ->label(__('expense.fields.attachments'))
                            ->multiple()
                            ->disk('public')
                            ->directory('expenses/attachments')
                            ->acceptedFileTypes(['image/*', 'application/pdf'])
                            ->maxSize(5120)
                            ->downloadable()
                            ->reorderable()
                            ->helperText(__('expense.fields.attachments_helper')),
                    ])
                    ->collapsed()
                    ->collapsible(),

                Section::make(__('expense.sections.notes'))
                    ->schema([
                        Textarea::make('notes')
                            ->label(__('expense.fields.notes'))
                            ->rows(3)
                            ->maxLength(1000),
                    ])
                    ->collapsed()
                    ->collapsible(),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('expense_number')
                    ->label(__('expense.columns.number'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('title')
                    ->label(__('expense.columns.title'))
                    ->formatStateUsing(fn ($record) => $record->getTranslation('title', app()->getLocale()))
                    ->searchable(['title->en', 'title->ar'])
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->getTranslation('title', app()->getLocale())),

                TextColumn::make('expense_type')
                    ->label(__('expense.columns.type'))
                    ->badge()
                    ->sortable(),

                TextColumn::make('category.name')
                    ->label(__('expense.columns.category'))
                    ->formatStateUsing(fn ($record) => $record->category?->getTranslation('name', app()->getLocale()) ?? '-')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('event.title')
                    ->label(__('expense.columns.event'))
                    ->formatStateUsing(fn ($record) => $record->event?->getTranslation('title', app()->getLocale()) ?? '-')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('booking.booking_reference')
                    ->label(__('expense.columns.booking'))
                    ->sortable()
                    ->toggleable()
                    ->url(fn ($record) => $record->booking_id
                        ? route('filament.admin.resources.bookings.view', $record->booking_id)
                        : null),

                TextColumn::make('total_amount')
                    ->label(__('expense.columns.amount'))
                    ->money('OMR')
                    ->sortable()
                    ->summarize(Sum::make()->money('OMR')),

                TextColumn::make('expense_date')
                    ->label(__('expense.columns.date'))
                    ->date('Y-m-d')
                    ->sortable(),

                TextColumn::make('payment_status')
                    ->label(__('expense.columns.payment'))
                    ->badge()
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->label(__('expense.columns.method'))
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('vendor_name')
                    ->label(__('expense.columns.vendor'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label(__('expense.columns.status'))
                    ->badge()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(__('expense.columns.created_at'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('expense_type')
                    ->label(__('expense.filters.type'))
                    ->options(ExpenseType::toArray()),

                SelectFilter::make('category_id')
                    ->label(__('expense.filters.category'))
                    ->relationship('category', 'name')
                    ->getOptionLabelFromRecordUsing(fn (ExpenseCategory $record) => $record->getTranslation('name', app()->getLocale())),

                SelectFilter::make('event_id')
                    ->label(__('expense.filters.event'))
                    ->relationship('event', 'title')
                    ->getOptionLabelFromRecordUsing(fn (Event $record) => $record->getTranslation('title', app()->getLocale()))
                    ->searchable()
                    ->preload(),

                SelectFilter::make('payment_status')
                    ->label(__('expense.filters.payment_status'))
                    ->options(ExpensePaymentStatus::toArray()),

                SelectFilter::make('payment_method')
                    ->label(__('expense.filters.payment_method'))
                    ->options(ExpensePaymentMethod::toArray()),

                Filter::make('expense_date')
                    ->schema([
                        DatePicker::make('from')
                            ->label(__('expense.filters.date_from')),
                        DatePicker::make('until')
                            ->label(__('expense.filters.date_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('expense_date', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('expense_date', '<=', $date));
                    }),

                TernaryFilter::make('has_booking')
                    ->label(__('expense.filters.has_booking'))
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('booking_id'),
                        false: fn (Builder $query) => $query->whereNull('booking_id'),
                    ),

                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),

                    Action::make('mark_paid')
                        ->label(__('expense.actions.mark_paid'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (Expense $record) => $record->payment_status !== ExpensePaymentStatus::Paid)
                        ->requiresConfirmation()
                        ->action(function (Expense $record) {
                            $record->markAsPaid();

                            Notification::make()
                                ->success()
                                ->title(__('expense.notifications.marked_paid'))
                                ->send();
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),

                    BulkAction::make('bulk_mark_paid')
                        ->label(__('expense.actions.bulk_mark_paid'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->markAsPaid())
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('expense_date', 'desc')
            ->striped()
            ->emptyStateActions([
                \Filament\Actions\CreateAction::make(),
            ])
            ->emptyStateHeading(__('expense.empty_state.heading'))
            ->emptyStateDescription(__('expense.empty_state.description'));
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExpenses::route('/'),
            'create' => CreateExpense::route('/create'),
            'view' => ViewExpense::route('/{record}'),
            'edit' => EditExpense::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
