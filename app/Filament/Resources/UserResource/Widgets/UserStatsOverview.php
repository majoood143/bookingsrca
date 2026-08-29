<?php

namespace App\Filament\Resources\UserResource\Widgets;

use App\Models\User;
use App\Support\UserInsightsReport;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;

class UserStatsOverview extends BaseWidget
{
    public ?User $record = null;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $report = UserInsightsReport::build($this->record, Carbon::now()->subDays(30), Carbon::now());

        return [
            Stat::make(__('widgets.user_insights.total_bookings'), number_format($report['totalBookings']))
                ->description(__('widgets.user_insights.attendees', ['count' => $report['totalAttendees']]))
                ->descriptionIcon('heroicon-m-users')
                ->chart($report['trendCounts'])
                ->color('primary'),

            Stat::make(__('widgets.user_insights.revenue'), new HtmlString(view('partials.currency-amount', ['amount' => (float) $report['totalRevenue']])->render()))
                ->description(__('widgets.user_insights.avg_ticket_price', ['amount' => number_format($report['avgTicketPrice'], 3)]))
                ->descriptionIcon('heroicon-m-banknotes')
                ->chart($report['trendRevenue'])
                ->color('success'),

            Stat::make(__('widgets.user_insights.ticket_types'), number_format($report['byTicketType']->count()))
                ->description(__('widgets.user_insights.ticket_types_desc'))
                ->descriptionIcon('heroicon-m-ticket')
                ->color('info'),

            Stat::make(__('widgets.user_insights.confirmed'), number_format($report['confirmedCount']))
                ->description(__('widgets.user_insights.pending_desc', ['count' => $report['pendingCount']]))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('widgets.user_insights.checked_in'), number_format($report['checkedInCount']))
                ->description(__('widgets.user_insights.cancelled_desc', ['count' => $report['cancelledCount']]))
                ->descriptionIcon('heroicon-m-qr-code')
                ->color('gray'),
        ];
    }
}
