<?php

namespace App\Filament\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Actions\Action;
use App\Models\TicketType;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class TicketStockWidget extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    protected function getTableHeading(): string
    {
        return __('widgets.ticket_stock.heading');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                TicketType::query()
                    ->with('event')
                    ->where('is_active', 1)
                    ->orderByRaw('(quantity_available - quantity_sold) ASC')
            )
            ->columns([
                TextColumn::make('event.title')
                    ->label(__('widgets.ticket_stock.event'))
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                TextColumn::make('name')
                    ->label(__('widgets.ticket_stock.ticket_type'))
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('price')
                    ->label(__('widgets.ticket_stock.price'))
                    ->money('OMR')
                    ->sortable(),

                TextColumn::make('quantity_sold')
                    ->label(__('widgets.ticket_stock.sold'))
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('quantity_available')
                    ->label(__('widgets.ticket_stock.total'))
                    ->sortable(),

                TextColumn::make('remaining')
                    ->label(__('widgets.ticket_stock.remaining'))
                    ->state(fn (TicketType $record): int => max(0, $record->quantity_available - $record->quantity_sold))
                    ->badge()
                    ->color(function (TicketType $record): string {
                        $remaining = max(0, $record->quantity_available - $record->quantity_sold);
                        $pct = $record->quantity_available > 0
                            ? ($remaining / $record->quantity_available) * 100
                            : 0;

                        return match (true) {
                            $remaining === 0 => 'danger',
                            $pct <= 10       => 'danger',
                            $pct <= 25       => 'warning',
                            default          => 'success',
                        };
                    }),

                TextColumn::make('sales_pct')
                    ->label(__('widgets.ticket_stock.sold_pct'))
                    ->state(function (TicketType $record): string {
                        if ($record->quantity_available <= 0) {
                            return '—';
                        }
                        $pct = ($record->quantity_sold / $record->quantity_available) * 100;
                        return number_format($pct, 1) . '%';
                    })
                    ->color(function (TicketType $record): string {
                        if ($record->quantity_available <= 0) {
                            return 'gray';
                        }
                        $pct = ($record->quantity_sold / $record->quantity_available) * 100;
                        return match (true) {
                            $pct >= 90 => 'danger',
                            $pct >= 75 => 'warning',
                            default    => 'success',
                        };
                    }),

                TextColumn::make('revenue')
                    ->label(__('widgets.ticket_stock.revenue'))
                    ->state(fn (TicketType $record): float => $record->quantity_sold * $record->price)
                    ->money('OMR')
                    ->weight('bold')
                    ->color('success'),

                IconColumn::make('is_active')
                    ->label(__('widgets.ticket_stock.active'))
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->label(__('widgets.ticket_stock.filter_status'))
                    ->options([
                        '1' => __('widgets.ticket_stock.filter_active'),
                        '0' => __('widgets.ticket_stock.filter_inactive'),
                    ]),

                Filter::make('low_stock')
                    ->label(__('widgets.ticket_stock.filter_low_stock'))
                    ->query(fn (Builder $query) =>
                        $query->whereRaw('quantity_available > 0')
                              ->whereRaw('(quantity_sold / quantity_available) >= 0.75')
                    ),

                Filter::make('sold_out')
                    ->label(__('widgets.ticket_stock.filter_sold_out'))
                    ->query(fn (Builder $query) =>
                        $query->whereColumn('quantity_sold', '>=', 'quantity_available')
                    ),
            ])
            ->recordActions([
                Action::make('edit')
                    ->label(__('widgets.ticket_stock.action_edit'))
                    ->icon('heroicon-m-pencil-square')
                    ->url(fn (TicketType $record): string =>
                        route('filament.admin.resources.ticket-types.edit', $record)
                    ),
            ])
            ->paginated([10, 25, 50]);
    }
}
