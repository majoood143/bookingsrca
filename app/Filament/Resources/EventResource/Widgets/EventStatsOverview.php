<?php

namespace App\Filament\Resources\EventResource\Widgets;

use App\Models\Event;
use App\Support\EventInsightsReport;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class EventStatsOverview extends BaseWidget
{
    public ?Event $record = null;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $report = EventInsightsReport::build($this->record, Carbon::now()->subDays(30), Carbon::now());

        return [
            Stat::make(__('widgets.event_insights.total_bookings'), number_format($report['totalBookings']))
                ->description(__('widgets.event_insights.attendees', ['count' => $report['totalAttendees']]))
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make(__('widgets.event_insights.revenue'), 'OMR' . number_format($report['totalRevenue'], 3))
                ->description(__('widgets.event_insights.avg_ticket_price', ['amount' => number_format($report['avgTicketPrice'], 3)]))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make(__('widgets.event_insights.confirmed'), number_format($report['confirmedCount']))
                ->description(__('widgets.event_insights.pending_desc', ['count' => $report['pendingCount']]))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('widgets.event_insights.checked_in'), number_format($report['checkedInCount']))
                ->description(__('widgets.event_insights.cancelled_desc', ['count' => $report['cancelledCount']]))
                ->descriptionIcon('heroicon-m-qr-code')
                ->color('info'),

            Stat::make(
                __('widgets.event_insights.capacity'),
                $report['capacityPct'] !== null ? number_format($report['capacityPct'], 1) . '%' : __('widgets.event_insights.unlimited')
            )
                ->description($report['maxAttendees'] ? __('widgets.event_insights.remaining', ['count' => $report['remainingCapacity']]) : '')
                ->descriptionIcon('heroicon-m-chart-pie')
                ->color(match (true) {
                    $report['capacityPct'] === null => 'gray',
                    $report['capacityPct'] >= 90 => 'danger',
                    $report['capacityPct'] >= 70 => 'warning',
                    default => 'success',
                }),
        ];
    }
}
