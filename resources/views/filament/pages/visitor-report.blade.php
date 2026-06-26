<x-filament-panels::page>
    <x-filament::section :heading="__('visitor_report.sections.filters')">
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
        {{ __('visitor_report.document.period', ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}
    </p>

    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        @php
            $stats = [
                ['label' => __('visitor_report.stats.total_visitors'), 'value' => $report['totalVisitors'], 'color' => 'bg-emerald-100 dark:bg-emerald-900/40', 'text' => 'text-emerald-700 dark:text-emerald-300'],
                ['label' => __('visitor_report.stats.total_bookings'), 'value' => $report['totalBookings'], 'color' => 'bg-gray-100 dark:bg-gray-800', 'text' => 'text-gray-800 dark:text-gray-100'],
                ['label' => __('visitor_report.stats.checked_in'), 'value' => $report['checkedInCount'], 'color' => 'bg-blue-100 dark:bg-blue-900/40', 'text' => 'text-blue-700 dark:text-blue-300'],
                ['label' => __('visitor_report.stats.events_covered'), 'value' => $report['eventsCovered'], 'color' => 'bg-purple-100 dark:bg-purple-900/40', 'text' => 'text-purple-700 dark:text-purple-300'],
            ];
        @endphp
        @foreach ($stats as $stat)
            <div class="rounded-xl p-4 {{ $stat['color'] }} flex flex-col gap-1 shadow-sm">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide leading-tight">{{ $stat['label'] }}</span>
                <span class="text-2xl font-bold {{ $stat['text'] }}">{{ $stat['value'] }}</span>
            </div>
        @endforeach
    </div>

    @php
        $tables = [
            ['heading' => __('visitor_report.sections.by_gender'), 'rows' => $report['byGender'], 'labelCol' => __('visitor_report.columns.gender')],
            ['heading' => __('visitor_report.sections.by_ticket'), 'rows' => $report['byTicket'], 'labelCol' => __('visitor_report.columns.ticket_type')],
            ['heading' => __('visitor_report.sections.by_time_slot'), 'rows' => $report['byTimeSlot'], 'labelCol' => __('visitor_report.columns.time_slot')],
            ['heading' => __('visitor_report.sections.by_country'), 'rows' => $report['byCountry'], 'labelCol' => __('visitor_report.columns.country')],
        ];
    @endphp

    @foreach ($tables as $t)
        <x-filament::section :heading="$t['heading']">
            @if ($t['rows']->isEmpty())
                <p class="text-sm text-gray-400 dark:text-gray-500 py-4 text-center">{{ __('visitor_report.no_data') }}</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="py-3 px-4 font-semibold text-gray-600 dark:text-gray-300">{{ $t['labelCol'] }}</th>
                                <th class="py-3 px-4 font-semibold text-gray-600 dark:text-gray-300 text-center">{{ __('visitor_report.columns.count') }}</th>
                                <th class="py-3 px-4 font-semibold text-gray-600 dark:text-gray-300 text-center">{{ __('visitor_report.columns.percentage') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach ($t['rows'] as $row)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                    <td class="py-3 px-4 font-medium text-gray-800 dark:text-gray-200">{{ $row['label'] }}</td>
                                    <td class="py-3 px-4 text-center text-gray-700 dark:text-gray-300">{{ $row['count'] }}</td>
                                    <td class="py-3 px-4 text-center text-gray-700 dark:text-gray-300">{{ $row['percentage'] }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>
    @endforeach
</x-filament-panels::page>
