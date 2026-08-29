<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class TotalBalanceDueWidget extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 5;

    protected function paidAmountSubquery(): \Illuminate\Database\Query\Builder
    {
        return DB::table('booking_payments')
            ->selectRaw('COALESCE(SUM(amount), 0)')
            ->whereColumn('booking_payments.booking_id', 'bookings.id');
    }

    protected function getStats(): array
    {
        $balanceDueBookings = Booking::query()
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->whereRaw('total_price > (' . $this->paidAmountSubquery()->toSql() . ')')
            ->addSelect('bookings.*')
            ->selectSub($this->paidAmountSubquery(), 'paid_amount')
            ->get();

        $totalBalanceDue = $balanceDueBookings->sum(
            fn (Booking $booking) => max(0, (float) $booking->total_price - (float) $booking->paid_amount)
        );

        return [
            Stat::make(
                __('widgets.total_balance_due.heading'),
                new HtmlString(view('partials.currency-amount', ['amount' => (float) $totalBalanceDue])->render())
            )
                ->description(__('widgets.total_balance_due.description', ['count' => $balanceDueBookings->count()]))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($totalBalanceDue > 0 ? 'danger' : 'success'),
        ];
    }
}
