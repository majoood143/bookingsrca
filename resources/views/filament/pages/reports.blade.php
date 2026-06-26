<x-filament-panels::page>
    {{-- Filters --}}
    <x-filament::section :heading="__('reports.sections.filters')">
        <form wire:submit.prevent>
            {{ $this->form }}
        </form>
    </x-filament::section>

    @php
        $report = $this->getReportData();
        $from   = $report['from'];
        $to     = $report['to'];
    @endphp

    {{-- Period Label --}}
    <p class="text-sm text-gray-500 dark:text-gray-400 -mt-2">
        {{ __('reports.period_label', ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}
    </p>

    {{-- Summary Stats --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-7">

        @php
            $stats = [
                ['label' => __('reports.stats.total_bookings'),  'value' => $report['totalBookings'],  'color' => 'bg-gray-100 dark:bg-gray-800',         'text' => 'text-gray-800 dark:text-gray-100'],
                ['label' => __('reports.stats.confirmed'),       'value' => $report['confirmedCount'], 'color' => 'bg-green-100 dark:bg-green-900/40',    'text' => 'text-green-700 dark:text-green-300'],
                ['label' => __('reports.stats.pending'),         'value' => $report['pendingCount'],   'color' => 'bg-amber-100 dark:bg-amber-900/40',    'text' => 'text-amber-700 dark:text-amber-300'],
                ['label' => __('reports.stats.cancelled'),       'value' => $report['cancelledCount'], 'color' => 'bg-red-100 dark:bg-red-900/40',        'text' => 'text-red-700 dark:text-red-300'],
                ['label' => __('reports.stats.checked_in'),      'value' => $report['checkedInCount'], 'color' => 'bg-blue-100 dark:bg-blue-900/40',      'text' => 'text-blue-700 dark:text-blue-300'],
                ['label' => __('reports.stats.total_attendees'), 'value' => $report['totalAttendees'], 'color' => 'bg-purple-100 dark:bg-purple-900/40',  'text' => 'text-purple-700 dark:text-purple-300'],
                ['label' => __('reports.stats.total_revenue'),   'value' => 'OMR' . number_format((float)$report['totalRevenue'], 3), 'color' => 'bg-emerald-100 dark:bg-emerald-900/40', 'text' => 'text-emerald-700 dark:text-emerald-300'],
            ];
        @endphp

        @foreach ($stats as $stat)
            <div class="rounded-xl p-4 {{ $stat['color'] }} flex flex-col gap-1 shadow-sm">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide leading-tight">
                    {{ $stat['label'] }}
                </span>
                <span class="text-2xl font-bold {{ $stat['text'] }}">
                    {{ $stat['value'] }}
                </span>
            </div>
        @endforeach
    </div>

    {{-- Breakdown by Event --}}
    <x-filament::section :heading="__('reports.sections.by_event')">
        @if ($report['byEvent']->isEmpty())
            <p class="text-sm text-gray-400 dark:text-gray-500 py-4 text-center">
                {{ __('reports.no_data') }}
            </p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="py-3 px-4 font-semibold text-gray-600 dark:text-gray-300">{{ __('reports.columns.event') }}</th>
                            <th class="py-3 px-4 font-semibold text-gray-600 dark:text-gray-300 text-center">{{ __('reports.columns.total') }}</th>
                            <th class="py-3 px-4 font-semibold text-green-600 dark:text-green-400 text-center">{{ __('reports.columns.confirmed') }}</th>
                            <th class="py-3 px-4 font-semibold text-amber-600 dark:text-amber-400 text-center">{{ __('reports.columns.pending') }}</th>
                            <th class="py-3 px-4 font-semibold text-red-600 dark:text-red-400 text-center">{{ __('reports.columns.cancelled') }}</th>
                            <th class="py-3 px-4 font-semibold text-blue-600 dark:text-blue-400 text-center">{{ __('reports.columns.checked_in') }}</th>
                            <th class="py-3 px-4 font-semibold text-gray-600 dark:text-gray-300 text-center">{{ __('reports.columns.attendees') }}</th>
                            <th class="py-3 px-4 font-semibold text-emerald-600 dark:text-emerald-400 text-right">{{ __('reports.columns.revenue') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($report['byEvent'] as $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="py-3 px-4 font-medium text-gray-800 dark:text-gray-200">{{ $row['event'] }}</td>
                                <td class="py-3 px-4 text-center text-gray-700 dark:text-gray-300">{{ $row['total'] }}</td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300">
                                        {{ $row['confirmed'] }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                                        {{ $row['pending'] }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300">
                                        {{ $row['cancelled'] }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                                        {{ $row['checked_in'] }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center text-gray-700 dark:text-gray-300">{{ $row['attendees'] }}</td>
                                <td class="py-3 px-4 text-right font-semibold text-emerald-600 dark:text-emerald-400">
                                    OMR {{ number_format((float)$row['revenue'], 3) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>

    {{-- Breakdown by Ticket Type --}}
    <x-filament::section :heading="__('reports.sections.by_ticket')">
        @if ($report['byTicket']->isEmpty())
            <p class="text-sm text-gray-400 dark:text-gray-500 py-4 text-center">
                {{ __('reports.no_data') }}
            </p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="py-3 px-4 font-semibold text-gray-600 dark:text-gray-300">{{ __('reports.columns.ticket_type') }}</th>
                            <th class="py-3 px-4 font-semibold text-gray-600 dark:text-gray-300 text-center">{{ __('reports.columns.bookings') }}</th>
                            <th class="py-3 px-4 font-semibold text-gray-600 dark:text-gray-300 text-center">{{ __('reports.columns.attendees') }}</th>
                            <th class="py-3 px-4 font-semibold text-emerald-600 dark:text-emerald-400 text-right">{{ __('reports.columns.revenue') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($report['byTicket'] as $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="py-3 px-4 font-medium text-gray-800 dark:text-gray-200">{{ $row['ticket_type'] }}</td>
                                <td class="py-3 px-4 text-center text-gray-700 dark:text-gray-300">{{ $row['bookings'] }}</td>
                                <td class="py-3 px-4 text-center text-gray-700 dark:text-gray-300">{{ $row['attendees'] }}</td>
                                <td class="py-3 px-4 text-right font-semibold text-emerald-600 dark:text-emerald-400">
                                    OMR{{ number_format((float)$row['revenue'], 3) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>
</x-filament-panels::page>
