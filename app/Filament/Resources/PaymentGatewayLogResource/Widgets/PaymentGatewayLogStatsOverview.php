<?php

namespace App\Filament\Resources\PaymentGatewayLogResource\Widgets;

use App\Models\PaymentGatewayLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class PaymentGatewayLogStatsOverview extends BaseWidget
{
    protected static ?int $sort = -1;

    protected function getStats(): array
    {
        $since = Carbon::now()->subDays(7);

        $recent = PaymentGatewayLog::query()->where('created_at', '>=', $since);

        $total = (clone $recent)->count();
        $success = (clone $recent)->outcome('success')->count();
        $failed = (clone $recent)->outcome('failed')->count();
        $errors = (clone $recent)->outcome('error')->count();

        $successRate = $total > 0 ? round(($success / $total) * 100, 1) : 0;

        $errorsToday = PaymentGatewayLog::query()
            ->outcome('error')
            ->whereDate('created_at', Carbon::today())
            ->count();

        return [
            Stat::make(__('payment_gateway_log.widgets.total_last_7_days'), number_format($total))
                ->description(__('payment_gateway_log.widgets.total_desc'))
                ->descriptionIcon('heroicon-m-shield-exclamation')
                ->color('primary'),

            Stat::make(__('payment_gateway_log.widgets.success_rate'), $successRate . '%')
                ->description(__('payment_gateway_log.widgets.success_desc', ['count' => $success]))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($successRate >= 80 ? 'success' : ($successRate >= 50 ? 'warning' : 'danger')),

            Stat::make(__('payment_gateway_log.widgets.failed'), number_format($failed))
                ->description(__('payment_gateway_log.widgets.failed_desc'))
                ->descriptionIcon('heroicon-m-x-circle')
                ->color($failed > 0 ? 'warning' : 'success'),

            Stat::make(__('payment_gateway_log.widgets.errors'), number_format($errors))
                ->description(__('payment_gateway_log.widgets.errors_desc', ['count' => $errorsToday]))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($errorsToday > 0 ? 'danger' : ($errors > 0 ? 'warning' : 'success')),
        ];
    }
}
