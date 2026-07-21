<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseCategoryResource\Pages\CreateExpenseCategory;
use App\Filament\Resources\ExpenseCategoryResource\Pages\EditExpenseCategory;
use App\Filament\Resources\ExpenseCategoryResource\Pages\ListExpenseCategories;
use App\Models\ExpenseCategory;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ExpenseCategoryResource extends Resource
{
    protected static ?string $model = ExpenseCategory::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-tag';
    protected static ?int $navigationSort = 8;

    public static function canAccess(): bool
    {
        return parent::canAccess() && (bool) \App\Models\BookingSetting::get('module_expenses_enabled', true);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('expense_category.navigation.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('expense_category.navigation.plural');
    }

    public static function getModelLabel(): string
    {
        return __('expense_category.navigation.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('expense_category.navigation.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('expense_category.sections.information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name.en')
                                    ->label(__('expense_category.fields.name_en'))
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('name.ar')
                                    ->label(__('expense_category.fields.name_ar'))
                                    ->required()
                                    ->maxLength(255)
                                    ->extraInputAttributes(['dir' => 'rtl']),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Textarea::make('description.en')
                                    ->label(__('expense_category.fields.description_en'))
                                    ->rows(2),

                                Textarea::make('description.ar')
                                    ->label(__('expense_category.fields.description_ar'))
                                    ->rows(2)
                                    ->extraInputAttributes(['dir' => 'rtl']),
                            ]),

                        Grid::make(3)
                            ->schema([
                                ColorPicker::make('color')
                                    ->label(__('expense_category.fields.color'))
                                    ->default('#6366f1'),

                                TextInput::make('icon')
                                    ->label(__('expense_category.fields.icon'))
                                    ->default('heroicon-o-banknotes')
                                    ->maxLength(50),

                                TextInput::make('order')
                                    ->label(__('expense_category.fields.order'))
                                    ->numeric()
                                    ->default(0),
                            ]),

                        Toggle::make('is_active')
                            ->label(__('expense_category.fields.is_active'))
                            ->default(true)
                            ->inline(false),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ColorColumn::make('color')
                    ->label(__('expense_category.columns.color')),

                TextColumn::make('name')
                    ->label(__('expense_category.columns.name'))
                    ->getStateUsing(fn ($record) => $record->getTranslation('name', app()->getLocale()))
                    ->searchable(['name->en', 'name->ar'])
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('expenses_count')
                    ->label(__('expense_category.columns.expenses_count'))
                    ->counts('expenses')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('order')
                    ->label(__('expense_category.columns.order'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_active')
                    ->label(__('expense_category.columns.is_active'))
                    ->boolean(),
            ])
            ->defaultSort('order')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('expense_category.filters.is_active')),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make()
                        ->before(function (ExpenseCategory $record) {
                            if (!$record->canBeDeleted()) {
                                Notification::make()
                                    ->danger()
                                    ->title(__('expense_category.notifications.cannot_delete'))
                                    ->body(__('expense_category.notifications.has_expenses'))
                                    ->send();

                                return false;
                            }
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                CreateAction::make()
                    ->label(__('expense_category.actions.create_first')),
            ])
            ->emptyStateHeading(__('expense_category.empty_state.heading'))
            ->emptyStateDescription(__('expense_category.empty_state.description'));
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExpenseCategories::route('/'),
            'create' => CreateExpenseCategory::route('/create'),
            'edit' => EditExpenseCategory::route('/{record}/edit'),
        ];
    }
}
