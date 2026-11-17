<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class TotalBalanceDueWidget extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 5;

    protected function paidAmountSubquery(): Builder
    {
        return DB::table('booking_payments')
            ->selectRaw('COALESCE(SUM(amount), 0)')
            ->whereColumn('booking_payments.booking_id', 'bookings.id');
    }

    protected function getStats(): array
    {
        // Count and sum in the database instead of loading every booking with a balance.
        $balanceDue = DB::query()
            ->fromSub(
                Booking::query()
                    ->whereIn('status', ['confirmed', 'checked_in'])
                    ->whereRaw('total_price > ('.$this->paidAmountSubquery()->toSql().')')
                    ->select('total_price')
                    ->selectSub($this->paidAmountSubquery(), 'paid_amount'),
                'due'
            )
            ->selectRaw('COUNT(*) as bookings_count, COALESCE(SUM(total_price - paid_amount), 0) as total_due')
            ->first();

        $totalBalanceDue = (float) $balanceDue->total_due;

        return [
            Stat::make(
                __('widgets.total_balance_due.heading'),
                new HtmlString(view('partials.currency-amount', ['amount' => (float) $totalBalanceDue])->render())
            )
                ->description(__('widgets.total_balance_due.description', ['count' => (int) $balanceDue->bookings_count]))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($totalBalanceDue > 0 ? 'danger' : 'success'),
        ];
    }
}
