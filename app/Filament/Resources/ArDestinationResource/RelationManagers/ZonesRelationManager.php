<?php

namespace App\Filament\Resources\ArDestinationResource\RelationManagers;

use App\Filament\Support\Localized;
use App\Models\ArZone;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ZonesRelationManager extends RelationManager
{
    protected static string $relationship = 'zones';

    public static function getTitle($ownerRecord, string $pageClass): string
    {
        return __('explore.zone.plural');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(2)->schema(Localized::fields(fn (string $locale, bool $isDefault) => TextInput::make("title.{$locale}")
                ->label(__('explore.fields.title', ['locale' => strtoupper($locale)]))
                ->required($isDefault)
                ->maxLength(255))),
            TextInput::make('sort_order')->label(__('explore.fields.sort_order'))->numeric()->default(0),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->description(__('explore.zone.helper', ['max' => ArZone::MAX_TARGETS]))
            ->columns([
                TextColumn::make('title')->label(__('explore.columns.title'))->weight('bold'),
                TextColumn::make('items_count')->counts('items')->label(__('explore.columns.items')),
                TextColumn::make('targets_status')
                    ->label(__('explore.columns.targets'))
                    ->badge()
                    ->getStateUsing(fn (ArZone $record) => $record->needsCompile() ? __('explore.columns.needs_compile') : __('explore.columns.compiled'))
                    ->color(fn (ArZone $record) => $record->needsCompile() ? 'warning' : 'success'),
                TextColumn::make('targets_compiled_at')->dateTime()->label('')->color('gray'),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->headerActions([CreateAction::make()])
            ->recordActions([
                Action::make('compile')
                    ->label(__('explore.compile.action'))
                    ->icon('heroicon-o-cpu-chip')
                    ->modalHeading(fn (ArZone $record) => __('explore.compile.heading', ['zone' => $record->title]))
                    ->modalSubmitAction(false)
                    ->modalContent(fn (ArZone $record) => view('filament.ar.compile-zone', ['zone' => $record])),
                Action::make('scanner')
                    ->label(__('explore.compile.open_scanner'))
                    ->icon('heroicon-o-camera')
                    ->color('gray')
                    ->url(fn (ArZone $record) => route('ar.scan', [$this->getOwnerRecord()->slug, $record]), shouldOpenInNewTab: true)
                    ->visible(fn (ArZone $record) => $record->isScannable() && $this->getOwnerRecord()->is_published),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
