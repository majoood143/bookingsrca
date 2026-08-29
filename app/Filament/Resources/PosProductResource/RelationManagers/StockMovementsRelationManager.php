<?php

namespace App\Filament\Resources\PosProductResource\RelationManagers;

use App\Models\PosProduct;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StockMovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'stockMovements';

    public static function getTitle($ownerRecord, string $pageClass): string
    {
        return __('pos.product.stock_movements.label');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label(__('pos.product.stock_movements.date'))
                    ->dateTime()
                    ->sortable(),

                BadgeColumn::make('type')
                    ->label(__('pos.product.stock_movements.type')),

                TextColumn::make('quantity')
                    ->label(__('pos.product.stock_movements.quantity'))
                    ->color(fn (int $state) => $state < 0 ? 'danger' : 'success')
                    ->formatStateUsing(fn (int $state) => ($state > 0 ? '+' : '') . $state),

                TextColumn::make('reference')
                    ->label(__('pos.product.stock_movements.reference'))
                    ->placeholder('—'),

                TextColumn::make('user.name')
                    ->label(__('pos.product.stock_movements.user'))
                    ->placeholder('—'),
            ])
            ->headerActions([
                Action::make('adjustStock')
                    ->label(__('Adjust Stock'))
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->schema([
                        Select::make('type')
                            ->options(['restock' => __('Restock'), 'adjustment' => __('Adjustment'), 'return' => __('Return')])
                            ->required()
                            ->default('restock'),

                        TextInput::make('quantity')
                            ->numeric()
                            ->required()
                            ->helperText(__('Positive to add stock, negative to remove.')),

                        Textarea::make('reference'),
                    ])
                    ->action(function (array $data) {
                        /** @var PosProduct $product */
                        $product = $this->getOwnerRecord();

                        $product->applyStockMovement(
                            $data['type'],
                            (int) $data['quantity'],
                            $data['reference'] ?? null,
                            auth()->id(),
                        );

                        Notification::make()->success()->title(__('Stock updated.'))->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
