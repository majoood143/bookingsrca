<x-filament-panels::page>
    <x-filament::section :heading="__('commission_report.sections.filters')">
        <form wire:submit.prevent>
            {{ $this->form }}
        </form>
    </x-filament::section>

    @php
        $report = $this->getReportData();
        $from   = $report['from'];
        $to     = $report['to'];
    @endphp

    <p class="text-sm text-gray-500 dark:text-gray-400 -mt-2">
        {{ __('commission_report.document.period', ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}
    </p>

    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-4">
        @php
            $stats = [
                ['label' => __('commission_report.stats.total_revenue'), 'value' => view('partials.currency-amount', ['amount' => $report['totalRevenue']])->render(), 'color' => 'bg-gray-100 dark:bg-gray-800', 'text' => 'text-gray-800 dark:text-gray-100'],
                ['label' => __('commission_report.stats.total_commission'), 'value' => view('partials.currency-amount', ['amount' => $report['totalCommission']])->render(), 'color' => 'bg-violet-100 dark:bg-violet-900/40', 'text' => 'text-violet-700 dark:text-violet-300'],
                ['label' => __('commission_report.stats.booking_count'), 'value' => $report['bookingCount'], 'color' => 'bg-emerald-100 dark:bg-emerald-900/40', 'text' => 'text-emerald-700 dark:text-emerald-300'],
                ['label' => __('commission_report.stats.avg_rate'), 'value' => number_format($report['avgRate'], 2) . '%', 'color' => 'bg-blue-100 dark:bg-blue-900/40', 'text' => 'text-blue-700 dark:text-blue-300'],
            ];
        @endphp
        @foreach ($stats as $stat)
            <div class="rounded-xl p-4 {{ $stat['color'] }} flex flex-col gap-1 shadow-sm">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide leading-tight">{{ $stat['label'] }}</span>
                <span class="text-2xl font-bold {{ $stat['text'] }}">{!! $stat['value'] !!}</span>
            </div>
        @endforeach
    </div>

    @if ($report['uncoveredCount'] > 0)
        <div class="rounded-xl p-3 bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 text-sm text-amber-700 dark:text-amber-300">
            {{ __('commission_report.no_rate_warning', ['count' => $report['uncoveredCount']]) }}
        </div>
    @endif

    <x-filament::section :heading="__('commission_report.sections.by_event')">
        @if ($report['byEvent']->isEmpty())
            <p class="text-sm text-gray-400 dark:text-gray-500 py-4 text-center">{{ __('commission_report.no_data') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="py-3 px-4 font-semibold text-gray-600 dark:text-gray-300">{{ __('commission_report.columns.event') }}</th>
                            <th class="py-3 px-4 font-semibold text-gray-600 dark:text-gray-300 text-center">{{ __('commission_report.columns.count') }}</th>
                            <th class="py-3 px-4 font-semibold text-gray-600 dark:text-gray-300 text-center">{{ __('commission_report.columns.rate') }}</th>
                            <th class="py-3 px-4 font-semibold text-gray-600 dark:text-gray-300 text-right">{{ __('commission_report.columns.revenue') }}</th>
                            <th class="py-3 px-4 font-semibold text-violet-600 dark:text-violet-400 text-right">{{ __('commission_report.columns.commission') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($report['byEvent'] as $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="py-3 px-4 font-medium text-gray-800 dark:text-gray-200">{{ $row['label'] }}</td>
                                <td class="py-3 px-4 text-center text-gray-700 dark:text-gray-300">{{ $row['count'] }}</td>
                                <td class="py-3 px-4 text-center text-gray-700 dark:text-gray-300">{{ $row['rate'] }}</td>
                                <td class="py-3 px-4 text-right text-gray-700 dark:text-gray-300">@include('partials.currency-amount', ['amount' => $row['revenue']])</td>
                                <td class="py-3 px-4 text-right font-semibold text-violet-600 dark:text-violet-400">@include('partials.currency-amount', ['amount' => $row['commission']])</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>

    <x-filament::section :heading="__('commission_report.sections.trend')">
        @if ($report['commissionTrend']->isEmpty())
            <p class="text-sm text-gray-400 dark:text-gray-500 py-4 text-center">{{ __('commission_report.no_data') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="py-3 px-4 font-semibold text-gray-600 dark:text-gray-300">{{ __('commission_report.columns.date') }}</th>
                            <th class="py-3 px-4 font-semibold text-gray-600 dark:text-gray-300 text-right">{{ __('commission_report.columns.revenue') }}</th>
                            <th class="py-3 px-4 font-semibold text-violet-600 dark:text-violet-400 text-right">{{ __('commission_report.columns.commission') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($report['commissionTrend'] as $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="py-3 px-4 font-medium text-gray-800 dark:text-gray-200">{{ $row['date'] }}</td>
                                <td class="py-3 px-4 text-right text-gray-700 dark:text-gray-300">@include('partials.currency-amount', ['amount' => $row['revenue']])</td>
                                <td class="py-3 px-4 text-right font-semibold text-violet-600 dark:text-violet-400">@include('partials.currency-amount', ['amount' => $row['commission']])</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>
</x-filament-panels::page>
