<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArItemResource\Pages\CreateArItem;
use App\Filament\Resources\ArItemResource\Pages\EditArItem;
use App\Filament\Resources\ArItemResource\Pages\ListArItems;
use App\Filament\Support\Localized;
use App\Models\ArItem;
use App\Models\ArZone;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ArItemResource extends Resource
{
    protected static ?string $model = ArItem::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-viewfinder-circle';

    protected static ?int $navigationSort = 81;

    public static function getNavigationGroup(): ?string
    {
        return __('explore.nav.group');
    }

    public static function getModelLabel(): string
    {
        return __('explore.item_resource.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('explore.item_resource.plural');
    }

    /** Zones grouped under their destination, for the select and the filter. */
    private static function zoneOptions(): array
    {
        return ArZone::with('destination')->orderBy('sort_order')->get()
            ->groupBy(fn (ArZone $zone) => $zone->destination->title)
            ->map(fn (Collection $zones) => $zones->mapWithKeys(fn (ArZone $zone) => [$zone->id => $zone->title]))
            ->toArray();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('ar_item_tabs')->columnSpanFull()->tabs([
                Tab::make(__('explore.tabs.content'))->icon('heroicon-o-document-text')->schema([
                    Select::make('ar_zone_id')
                        ->label(__('explore.fields.zone'))
                        ->options(fn () => self::zoneOptions())
                        ->required()
                        ->searchable()
                        ->native(false),

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
                        ->rows(3)
                        ->maxLength(500))),

                    Grid::make(2)->schema(Localized::fields(fn (string $locale) => Textarea::make("body.{$locale}")
                        ->label(__('explore.fields.body', ['locale' => strtoupper($locale)]))
                        ->rows(10))),

                    Grid::make(2)->schema([
                        TextInput::make('sort_order')->label(__('explore.fields.sort_order'))->numeric()->default(0),
                        Toggle::make('is_published')->label(__('explore.fields.is_published'))->inline(false),
                    ]),
                ]),

                Tab::make(__('explore.tabs.media'))->icon('heroicon-o-photo')->schema([
                    SpatieMediaLibraryFileUpload::make('target')
                        ->label(__('explore.fields.target'))
                        ->helperText(__('explore.fields.target_helper'))
                        ->collection('target')
                        ->disk('public')
                        ->image()
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),

                    SpatieMediaLibraryFileUpload::make('gallery')
                        ->label(__('explore.fields.gallery'))
                        ->collection('gallery')
                        ->disk('public')
                        ->image()
                        ->multiple()
                        ->reorderable(),
                ]),

                // One narration per app language; each field only sees and replaces its own language's file.
                Tab::make(__('explore.tabs.audio'))->icon('heroicon-o-speaker-wave')->schema(
                    array_map(fn (string $locale) => SpatieMediaLibraryFileUpload::make("audio_{$locale}")
                        ->label(__('explore.fields.audio', ['locale' => strtoupper($locale)]))
                        ->collection('audio')
                        ->disk('public')
                        ->customProperties(['locale' => $locale])
                        ->filterMediaUsing(fn (Collection $media) => $media->filter(fn (Media $m) => $m->getCustomProperty('locale') === $locale))
                        ->acceptedFileTypes(['audio/mpeg', 'audio/mp4', 'audio/x-m4a', 'audio/aac', 'audio/ogg', 'audio/wav', 'audio/webm'])
                        ->maxSize(12288), config('translatable.locales'))
                ),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('target')->collection('target')->label(''),
                TextColumn::make('title')->label(__('explore.columns.title'))->searchable()->weight('bold'),
                TextColumn::make('zone.destination.title')->label(__('explore.fields.destination')),
                TextColumn::make('zone.title')->label(__('explore.fields.zone')),
                IconColumn::make('is_published')->label(__('explore.fields.is_published'))->boolean(),
                TextColumn::make('sort_order')->label(__('explore.fields.sort_order'))->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                SelectFilter::make('ar_zone_id')->label(__('explore.fields.zone'))->options(fn () => self::zoneOptions()),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListArItems::route('/'),
            'create' => CreateArItem::route('/create'),
            'edit' => EditArItem::route('/{record}/edit'),
        ];
    }
}
