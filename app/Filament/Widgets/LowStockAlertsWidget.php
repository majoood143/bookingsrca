<?php

namespace App\Filament\Widgets;

use App\Models\TicketType;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LowStockAlertsWidget extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 6;

    protected function getHeading(): string
    {
        return __('widgets.low_stock_alerts.heading');
    }

    protected function getStats(): array
    {
        $soldOut = TicketType::where('is_active', true)
            ->whereColumn('quantity_sold', '>=', 'quantity_available')
            ->count();

        $critical = TicketType::where('is_active', true)
            ->whereRaw('quantity_available > 0')
            ->whereRaw('((quantity_sold / quantity_available) * 100) >= 90')
            ->whereRaw('quantity_sold < quantity_available')
            ->count();

        $lowStock = TicketType::where('is_active', true)
            ->whereRaw('quantity_available > 0')
            ->whereRaw('((quantity_sold / quantity_available) * 100) >= 75')
            ->whereRaw('((quantity_sold / quantity_available) * 100) < 90')
            ->count();

        $totalActive = TicketType::where('is_active', true)->count();

        $totalRevenue = TicketType::all()
            ->sum(fn (TicketType $t): float => $t->quantity_sold * $t->price);

        $totalSold = TicketType::sum('quantity_sold');
        $totalAvailable = TicketType::sum('quantity_available');
        $overallPct = $totalAvailable > 0
            ? round(($totalSold / $totalAvailable) * 100, 1)
            : 0;

        return [
            Stat::make(__('widgets.low_stock_alerts.sold_out'), $soldOut)
                ->description($soldOut > 0
                    ? __('widgets.low_stock_alerts.sold_out_desc')
                    : __('widgets.low_stock_alerts.no_sold_out')
                )
                ->descriptionIcon($soldOut > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($soldOut > 0 ? 'danger' : 'success'),

            Stat::make(__('widgets.low_stock_alerts.critical'), $critical)
                ->description(__('widgets.low_stock_alerts.critical_desc'))
                ->descriptionIcon('heroicon-m-fire')
                ->color($critical > 0 ? 'danger' : 'success'),

            Stat::make(__('widgets.low_stock_alerts.low_stock'), $lowStock)
                ->description(__('widgets.low_stock_alerts.low_stock_desc'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color($lowStock > 0 ? 'warning' : 'success'),

            Stat::make(__('widgets.low_stock_alerts.overall_sales'), $overallPct . '%')
                ->description(__('widgets.low_stock_alerts.overall_desc', [
                    'sold'   => $totalSold,
                    'total'  => $totalAvailable,
                    'active' => $totalActive,
                ]))
                ->descriptionIcon('heroicon-m-ticket')
                ->color(match (true) {
                    $overallPct >= 90 => 'danger',
                    $overallPct >= 70 => 'warning',
                    default           => 'success',
                }),

            Stat::make(__('widgets.low_stock_alerts.total_revenue'), 'OMR' . number_format($totalRevenue, 3))
                ->description(__('widgets.low_stock_alerts.revenue_desc'))
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make(__('widgets.low_stock_alerts.active_types'), $totalActive)
                ->description(__('widgets.low_stock_alerts.active_types_desc'))
                ->descriptionIcon('heroicon-m-squares-2x2')
                ->color('primary'),
        ];
    }
}
