<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArDestinationResource\Pages\CreateArDestination;
use App\Filament\Resources\ArDestinationResource\Pages\EditArDestination;
use App\Filament\Resources\ArDestinationResource\Pages\ListArDestinations;
use App\Filament\Resources\ArDestinationResource\RelationManagers\ZonesRelationManager;
use App\Filament\Support\Localized;
use App\Models\ArDestination;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ArDestinationResource extends Resource
{
    protected static ?string $model = ArDestination::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-map';

    protected static ?int $navigationSort = 80;

    public static function getNavigationGroup(): ?string
    {
        return __('explore.nav.group');
    }

    public static function getModelLabel(): string
    {
        return __('explore.destination.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('explore.destination.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columnSpanFull()->schema([
                Grid::make(2)->schema(Localized::fields(fn (string $locale, bool $isDefault) => TextInput::make("title.{$locale}")
                    ->label(__('explore.fields.title', ['locale' => strtoupper($locale)]))
                    ->required($isDefault)
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, $set, $get) => $isDefault && blank($get('slug')) ? $set('slug', Str::slug($state)) : null))),

                TextInput::make('slug')
                    ->label(__('explore.fields.slug'))
                    ->required()
                    ->alphaDash()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                Grid::make(2)->schema(Localized::fields(fn (string $locale) => Textarea::make("summary.{$locale}")
                    ->label(__('explore.fields.summary', ['locale' => strtoupper($locale)]))
                    ->rows(2)
                    ->maxLength(500))),

                Grid::make(2)->schema(Localized::fields(fn (string $locale) => Textarea::make("description.{$locale}")
                    ->label(__('explore.fields.description', ['locale' => strtoupper($locale)]))
                    ->rows(6))),

                SpatieMediaLibraryFileUpload::make('cover')
                    ->label(__('explore.fields.cover'))
                    ->collection('cover')
                    ->disk('public')
                    ->image(),

                SpatieMediaLibraryFileUpload::make('gallery')
                    ->label(__('explore.fields.gallery'))
                    ->collection('gallery')
                    ->disk('public')
                    ->image()
                    ->multiple()
                    ->reorderable(),

                Grid::make(2)->schema([
                    TextInput::make('sort_order')->label(__('explore.fields.sort_order'))->numeric()->default(0),
                    Toggle::make('is_published')->label(__('explore.fields.is_published'))->inline(false),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('cover')->collection('cover')->label(''),
                TextColumn::make('title')->label(__('explore.columns.title'))->searchable()->weight('bold'),
                TextColumn::make('items_count')->counts('items')->label(__('explore.columns.items')),
                IconColumn::make('is_published')->label(__('explore.fields.is_published'))->boolean(),
                TextColumn::make('sort_order')->label(__('explore.fields.sort_order'))->sortable(),
            ])
            ->defaultSort('sort_order')
            ->recordActions([
                Action::make('view_public')
                    ->label(__('explore.nav.explore'))
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn (ArDestination $record) => route('ar.destination', $record->slug), shouldOpenInNewTab: true)
                    ->visible(fn (ArDestination $record) => $record->is_published),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [ZonesRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListArDestinations::route('/'),
            'create' => CreateArDestination::route('/create'),
            'edit' => EditArDestination::route('/{record}/edit'),
        ];
    }
}
