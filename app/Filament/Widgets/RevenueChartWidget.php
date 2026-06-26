<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RevenueChartWidget extends ChartWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public ?string $filter = 'revenue';

    public function getHeading(): string
    {
        return __('widgets.revenue_chart.heading');
    }

    protected function getFilters(): ?array
    {
        return [
            'revenue'  => __('widgets.revenue_chart.filter_revenue'),
            'bookings' => __('widgets.revenue_chart.filter_bookings'),
        ];
    }

    protected function getData(): array
    {
        $days = collect(range(29, 0))->map(fn ($d) => Carbon::today()->subDays($d));

        $labels = $days->map(fn ($d) => $d->format('M j'))->toArray();

        if ($this->filter === 'revenue') {
            $values = $days->map(fn ($d) =>
                round(
                    Booking::whereIn('status', ['confirmed', 'checked_in'])
                        ->whereDate('created_at', $d)
                        ->sum('total_price'),
                    2
                )
            )->toArray();

            return [
                'datasets' => [
                    [
                        'label'           => __('widgets.revenue_chart.dataset_revenue'),
                        'data'            => $values,
                        'borderColor'     => '#f59e0b',
                        'backgroundColor' => 'rgba(245,158,11,0.15)',
                        'fill'            => true,
                        'tension'         => 0.4,
                    ],
                ],
                'labels' => $labels,
            ];
        }

        $values = $days->map(fn ($d) =>
            Booking::whereDate('created_at', $d)->count()
        )->toArray();

        return [
            'datasets' => [
                [
                    'label'           => __('widgets.revenue_chart.dataset_bookings'),
                    'data'            => $values,
                    'borderColor'     => '#6366f1',
                    'backgroundColor' => 'rgba(99,102,241,0.15)',
                    'fill'            => true,
                    'tension'         => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
