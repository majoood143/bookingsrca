<?php

namespace App\Filament\Resources\PosOrderResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public static function getTitle($ownerRecord, string $pageClass): string
    {
        return __('pos.order.fields.items');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product_name')->label(__('pos.order.receipt.item')),
                TextColumn::make('quantity')->label(__('pos.order.receipt.qty')),
                TextColumn::make('unit_price')->label(__('pos.order.receipt.unit_price')),
                TextColumn::make('line_total')->label(__('pos.order.receipt.line_total'))->weight('bold'),
            ]);
    }
}
