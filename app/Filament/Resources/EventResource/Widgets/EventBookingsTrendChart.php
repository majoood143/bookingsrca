<?php

namespace App\Filament\Resources\EventResource\Widgets;

use App\Models\Event;
use App\Support\EventInsightsReport;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class EventBookingsTrendChart extends ChartWidget
{
    public ?Event $record = null;

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public ?string $filter = 'bookings';

    public function getHeading(): string
    {
        return __('widgets.event_insights.trend_heading');
    }

    protected function getFilters(): ?array
    {
        return [
            'bookings' => __('widgets.revenue_chart.filter_bookings'),
            'revenue'  => __('widgets.revenue_chart.filter_revenue'),
        ];
    }

    protected function getData(): array
    {
        $report = EventInsightsReport::build($this->record, Carbon::now()->subDays(30), Carbon::now());

        if ($this->filter === 'revenue') {
            return [
                'datasets' => [
                    [
                        'label'           => __('widgets.revenue_chart.dataset_revenue'),
                        'data'            => $report['trendRevenue'],
                        'borderColor'     => '#f59e0b',
                        'backgroundColor' => 'rgba(245,158,11,0.15)',
                        'fill'            => true,
                        'tension'         => 0.4,
                    ],
                ],
                'labels' => $report['trendDates'],
            ];
        }

        return [
            'datasets' => [
                [
                    'label'           => __('widgets.revenue_chart.dataset_bookings'),
                    'data'            => $report['trendCounts'],
                    'borderColor'     => '#6366f1',
                    'backgroundColor' => 'rgba(99,102,241,0.15)',
                    'fill'            => true,
                    'tension'         => 0.4,
                ],
            ],
            'labels' => $report['trendDates'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
