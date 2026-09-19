<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TranslationResource\Pages\CreateTranslation;
use App\Filament\Resources\TranslationResource\Pages\EditTranslation;
use App\Filament\Resources\TranslationResource\Pages\ListTranslations;
use App\Models\Translation;
use App\Support\TranslationCatalog;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Unique;

class TranslationResource extends Resource
{
    protected static ?string $model = Translation::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-language';
    protected static ?int $navigationSort = 95;

    public static function getNavigationGroup(): ?string
    {
        return __('booking_setting.navigation.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('translation.navigation.plural');
    }

    public static function getModelLabel(): string
    {
        return __('translation.navigation.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('translation.navigation.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('translation.sections.key'))
                    ->schema([
                        Select::make('catalog_key')
                            ->label(__('translation.fields.catalog_key'))
                            ->helperText(__('translation.fields.catalog_key_helper'))
                            ->searchable()
                            ->getSearchResultsUsing(fn (string $search) => TranslationCatalog::options($search))
                            ->getOptionLabelUsing(fn (string $value) => TranslationCatalog::label(...TranslationCatalog::split($value)))
                            ->live()
                            ->dehydrated(false)
                            ->visibleOn('create')
                            ->afterStateUpdated(function (?string $state, Set $set) {
                                if (! $state) {
                                    return;
                                }

                                [$group, $key] = TranslationCatalog::split($state);

                                $set('group', $group);
                                $set('key', $key);
                                $set('text.en', TranslationCatalog::default($group, $key, 'en'));
                                $set('text.ar', TranslationCatalog::default($group, $key, 'ar'));
                            }),

                        Grid::make(2)->schema([
                            TextInput::make('group')
                                ->label(__('translation.fields.group'))
                                ->helperText(__('translation.fields.group_helper'))
                                ->datalist(fn () => TranslationCatalog::groups())
                                ->live(onBlur: true)
                                ->required()
                                ->maxLength(255)
                                ->disabledOn('edit'),

                            TextInput::make('key')
                                ->label(__('translation.fields.key'))
                                ->live(onBlur: true)
                                ->required()
                                ->maxLength(255)
                                ->disabledOn('edit')
                                ->unique(
                                    ignoreRecord: true,
                                    modifyRuleUsing: fn (Unique $rule, Get $get) => $rule->where('group', $get('group')),
                                ),
                        ]),
                    ]),

                Section::make(__('translation.sections.text'))
                    ->schema([
                        Grid::make(2)->schema([
                            Textarea::make('text.en')
                                ->label(__('translation.fields.text_en'))
                                ->helperText(fn (Get $get) => static::defaultHint($get, 'en'))
                                ->required(fn (Get $get) => blank($get('text.ar')))
                                ->rows(4),

                            Textarea::make('text.ar')
                                ->label(__('translation.fields.text_ar'))
                                ->helperText(fn (Get $get) => static::defaultHint($get, 'ar'))
                                ->required(fn (Get $get) => blank($get('text.en')))
                                ->extraInputAttributes(['dir' => 'rtl'])
                                ->rows(4),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('group')
                    ->label(__('translation.columns.group'))
                    ->formatStateUsing(fn (string $state) => $state === TranslationCatalog::JSON_GROUP ? 'JSON' : $state)
                    ->badge()
                    ->sortable(),

                TextColumn::make('key')
                    ->label(__('translation.columns.key'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('text.en')
                    ->label(__('translation.columns.text_en'))
                    ->limit(60)
                    ->searchable(query: fn (Builder $query, string $search) => $query->where('text', 'like', "%{$search}%")),

                TextColumn::make('text.ar')
                    ->label(__('translation.columns.text_ar'))
                    ->limit(60),

                TextColumn::make('updated_at')
                    ->label(__('translation.columns.updated_at'))
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('group')
                    ->label(__('translation.columns.group'))
                    ->options(fn () => Translation::query()->distinct()->orderBy('group')->pluck('group', 'group')->all()),
            ])
            ->defaultSort('group')
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    static::resetAction(DeleteAction::make()),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    static::resetAction(DeleteBulkAction::make()),
                ]),
            ])
            ->emptyStateHeading(__('translation.empty_state.heading'))
            ->emptyStateDescription(__('translation.empty_state.description'));
    }

    /**
     * Deleting an override is what "reset to default" means: the lang file value applies again.
     */
    public static function resetAction(DeleteAction|DeleteBulkAction $action): DeleteAction|DeleteBulkAction
    {
        return $action
            ->label(__('translation.actions.reset'))
            ->icon('heroicon-o-arrow-uturn-left')
            ->modalHeading(__('translation.actions.reset_heading'))
            ->modalDescription(__('translation.actions.reset_description'))
            ->successNotificationTitle(__('translation.actions.reset_done'));
    }

    protected static function defaultHint(Get $get, string $locale): ?string
    {
        $default = TranslationCatalog::default($get('group'), $get('key'), $locale);

        return $default === null ? null : __('translation.fields.default', ['text' => $default]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTranslations::route('/'),
            'create' => CreateTranslation::route('/create'),
            'edit' => EditTranslation::route('/{record}/edit'),
        ];
    }
}
