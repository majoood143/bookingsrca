<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\BookingAttendee;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class BookingOverviewWidget extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        $totalBookings = Booking::count();
        $todayBookings = Booking::whereDate('created_at', $today)->count();
        $pendingBookings = Booking::where('status', 'pending')->count();
        $confirmedBookings = Booking::where('status', 'confirmed')->count();
        $cancelledBookings = Booking::where('status', 'cancelled')->count();
        $checkedInToday = Booking::where('status', 'checked_in')
            ->whereDate('updated_at', $today)
            ->count();

        $revenueThisMonth = Booking::whereIn('status', ['confirmed', 'checked_in'])
            ->where('created_at', '>=', $thisMonth)
            ->sum('total_price');

        $revenueLastMonth = Booking::whereIn('status', ['confirmed', 'checked_in'])
            ->whereBetween('created_at', [$lastMonth, $lastMonthEnd])
            ->sum('total_price');

        $revenueChange = $revenueLastMonth > 0
            ? round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100, 1)
            : 0;

        $totalAttendees = BookingAttendee::count();
        $checkedInAttendees = BookingAttendee::where('checked_in', true)->count();

        $bookingsTrend = collect(range(6, 0))->map(fn ($d) =>
            Booking::whereDate('created_at', Carbon::today()->subDays($d))->count()
        )->toArray();

        $revenueTrend = collect(range(6, 0))->map(fn ($d) =>
            (int) Booking::whereIn('status', ['confirmed', 'checked_in'])
                ->whereDate('created_at', Carbon::today()->subDays($d))
                ->sum('total_price')
        )->toArray();

        return [
            Stat::make(__('widgets.booking_overview.total_bookings'), number_format($totalBookings))
                ->description(__('widgets.booking_overview.today_new', ['count' => $todayBookings]))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart($bookingsTrend)
                ->color('primary'),

            Stat::make(__('widgets.booking_overview.revenue_month'), 'OMR' . number_format($revenueThisMonth, 3))
                ->description(
                    ($revenueChange >= 0 ? '+' : '') .
                    __('widgets.booking_overview.vs_last_month', ['change' => $revenueChange])
                )
                ->descriptionIcon($revenueChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart($revenueTrend)
                ->color($revenueChange >= 0 ? 'success' : 'danger'),

            Stat::make(__('widgets.booking_overview.pending'), number_format($pendingBookings))
                ->description(__('widgets.booking_overview.pending_desc'))
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingBookings > 0 ? 'warning' : 'success'),

            Stat::make(__('widgets.booking_overview.confirmed'), number_format($confirmedBookings))
                ->description(__('widgets.booking_overview.cancelled_desc', ['count' => $cancelledBookings]))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('widgets.booking_overview.total_attendees'), number_format($totalAttendees))
                ->description(__('widgets.booking_overview.checked_in_desc', ['count' => $checkedInAttendees]))
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            Stat::make(__('widgets.booking_overview.checked_in_today'), number_format($checkedInToday))
                ->description(__('widgets.booking_overview.scanned_today'))
                ->descriptionIcon('heroicon-m-qr-code')
                ->color('gray'),
        ];
    }
}
