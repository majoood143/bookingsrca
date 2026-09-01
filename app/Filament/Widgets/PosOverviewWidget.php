<?php

namespace App\Filament\Widgets;

use App\Models\BookingSetting;
use App\Models\Expense;
use App\Models\PosOrder;
use App\Models\PosProduct;
use App\Models\PosTransaction;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\HtmlString;

class PosOverviewWidget extends BaseWidget
{
    use HasWidgetShield {
        canView as protected shieldCanView;
    }

    protected static ?int $sort = 7;

    public static function canView(): bool
    {
        return (bool) BookingSetting::get('module_pos_enabled', true) && static::shieldCanView();
    }

    protected function money(float $amount): HtmlString
    {
        return new HtmlString(view('partials.currency-amount', ['amount' => $amount])->render());
    }

    protected function getStats(): array
    {
        $stockAlertCount = PosProduct::where('is_active', true)
            ->whereNotNull('low_stock_threshold')
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->count();

        $salesOrderCount = PosOrder::whereIn('status', ['pending_payment', 'paid'])->count();

        $pendingShipments = PosOrder::whereIn('status', ['pending_payment', 'paid'])
            ->whereIn('fulfillment_status', ['pending', 'preparing', 'ready'])
            ->count()
            + PosTransaction::where('shipping_status', 'pending')->count();

        $sellReturn = (float) PosTransaction::whereIn('status', ['refunded', 'partially_refunded'])->sum('grand_total');

        $invoiceDue = (float) PosOrder::where('status', 'pending_payment')->sum('grand_total');

        $expense = (float) Expense::approved()->sum('amount');

        $totalSales = (float) PosTransaction::where('status', 'completed')->sum('grand_total')
            + (float) PosOrder::whereIn('status', ['pending_payment', 'paid'])->sum('grand_total');

        $net = $totalSales - $invoiceDue - $expense;

        return [
            Stat::make(__('widgets.pos_overview.stock_alert'), $stockAlertCount)
                ->description(__('widgets.pos_overview.stock_alert_desc'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($stockAlertCount > 0 ? 'danger' : 'success'),

            Stat::make(__('widgets.pos_overview.sales_order'), $salesOrderCount)
                ->description(__('widgets.pos_overview.sales_order_desc'))
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('primary'),

            Stat::make(__('widgets.pos_overview.pending_shipments'), $pendingShipments)
                ->description(__('widgets.pos_overview.pending_shipments_desc'))
                ->descriptionIcon('heroicon-m-truck')
                ->color($pendingShipments > 0 ? 'warning' : 'success'),

            Stat::make(__('widgets.pos_overview.sell_return'), $this->money($sellReturn))
                ->description(__('widgets.pos_overview.sell_return_desc'))
                ->descriptionIcon('heroicon-m-arrow-uturn-left')
                ->color($sellReturn > 0 ? 'danger' : 'success'),

            Stat::make(__('widgets.pos_overview.invoice_due'), $this->money($invoiceDue))
                ->description(__('widgets.pos_overview.invoice_due_desc'))
                ->descriptionIcon('heroicon-m-document-currency-dollar')
                ->color($invoiceDue > 0 ? 'danger' : 'success'),

            Stat::make(__('widgets.pos_overview.net'), $this->money($net))
                ->description(__('widgets.pos_overview.net_desc'))
                ->descriptionIcon('heroicon-m-scale')
                ->color($net >= 0 ? 'success' : 'danger'),

            Stat::make(__('widgets.pos_overview.total_sales'), $this->money($totalSales))
                ->description(__('widgets.pos_overview.total_sales_desc'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }
}
