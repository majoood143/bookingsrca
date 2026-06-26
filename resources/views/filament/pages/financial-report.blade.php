<x-filament-panels::page>
    <x-filament::section :heading="__('financial_report.sections.filters')">
        <form wire:submit.prevent>
            {{ $this->form }}
        </form>
    </x-filament::section>

    @php
        $report = $this->getReportData();
        $from   = $report['from'];
        $to     = $report['to'];
        $currency = __('financial_report.currency');
    @endphp

    <p class="text-sm text-gray-500 dark:text-gray-400 -mt-2">
        {{ __('financial_report.document.period', ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}
    </p>

    <div class="grid grid-cols-2 gap-4 sm:grid-cols-5">
        @php
            $stats = [
                ['label' => __('financial_report.stats.total_revenue'), 'value' => $currency . ' ' . number_format($report['totalRevenue'], 3), 'color' => 'bg-amber-100 dark:bg-amber-900/40', 'text' => 'text-amber-700 dark:text-amber-300'],
                ['label' => __('financial_report.stats.total_paid'), 'value' => $currency . ' ' . number_format($report['totalPaid'], 3), 'color' => 'bg-emerald-100 dark:bg-emerald-900/40', 'text' => 'text-emerald-700 dark:text-emerald-300'],
                ['label' => __('financial_report.stats.balance_due'), 'value' => $currency . ' ' . number_format($report['balanceDue'], 3), 'color' => 'bg-red-100 dark:bg-red-900/40', 'text' => 'text-red-700 dark:text-red-300'],
                ['label' => __('financial_report.stats.total_bookings'), 'value' => $report['totalBookings'], 'color' => 'bg-gray-100 dark:bg-gray-800', 'text' => 'text-gray-800 dark:text-gray-100'],
                ['label' => __('financial_report.stats.avg_booking'), 'value' => $currency . ' ' . number_format($report['avgBooking'], 3), 'color' => 'bg-blue-100 dark:bg-blue-900/40', 'text' => 'text-blue-700 dark:text-blue-300'],
            ];
        @endphp
        @foreach ($stats as $stat)
            <div class="rounded-xl p-4 {{ $stat['color'] }} flex flex-col gap-1 shadow-sm">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide leading-tight">{{ $stat['label'] }}</span>
                <span class="text-2xl font-bold {{ $stat['text'] }}">{{ $stat['value'] }}</span>
            </div>
        @endforeach
    </div>

    <x-filament::section :heading="__('financial_report.sections.by_ticket')">
        @if ($report['byTicket']->isEmpty())
            <p class="text-sm text-gray-400 dark:text-gray-500 py-4 text-center">{{ __('financial_report.no_data') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="py-3 px-4 font-semibold text-gray-600 dark:text-gray-300">{{ __('financial_report.columns.ticket_type') }}</th>
                            <th class="py-3 px-4 font-semibold text-gray-600 dark:text-gray-300 text-center">{{ __('financial_report.columns.bookings') }}</th>
                            <th class="py-3 px-4 font-semibold text-emerald-600 dark:text-emerald-400 text-right">{{ __('financial_report.columns.revenue') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($report['byTicket'] as $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="py-3 px-4 font-medium text-gray-800 dark:text-gray-200">{{ $row['label'] }}</td>
                                <td class="py-3 px-4 text-center text-gray-700 dark:text-gray-300">{{ $row['bookings'] }}</td>
                                <td class="py-3 px-4 text-right font-semibold text-emerald-600 dark:text-emerald-400">{{ $currency }} {{ number_format($row['revenue'], 3) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>

    <x-filament::section :heading="__('financial_report.sections.by_event')">
        @if ($report['byEvent']->isEmpty())
            <p class="text-sm text-gray-400 dark:text-gray-500 py-4 text-center">{{ __('financial_report.no_data') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="py-3 px-4 font-semibold text-gray-600 dark:text-gray-300">{{ __('financial_report.columns.event') }}</th>
                            <th class="py-3 px-4 font-semibold text-gray-600 dark:text-gray-300 text-center">{{ __('financial_report.columns.bookings') }}</th>
                            <th class="py-3 px-4 font-semibold text-emerald-600 dark:text-emerald-400 text-right">{{ __('financial_report.columns.revenue') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($report['byEvent'] as $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="py-3 px-4 font-medium text-gray-800 dark:text-gray-200">{{ $row['label'] }}</td>
                                <td class="py-3 px-4 text-center text-gray-700 dark:text-gray-300">{{ $row['bookings'] }}</td>
                                <td class="py-3 px-4 text-right font-semibold text-emerald-600 dark:text-emerald-400">{{ $currency }} {{ number_format($row['revenue'], 3) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>

    <x-filament::section :heading="__('financial_report.sections.by_payment_method')">
        @if ($report['byPaymentMethod']->isEmpty())
            <p class="text-sm text-gray-400 dark:text-gray-500 py-4 text-center">{{ __('financial_report.no_data') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="py-3 px-4 font-semibold text-gray-600 dark:text-gray-300">{{ __('financial_report.columns.payment_method') }}</th>
                            <th class="py-3 px-4 font-semibold text-emerald-600 dark:text-emerald-400 text-right">{{ __('financial_report.columns.revenue') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($report['byPaymentMethod'] as $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="py-3 px-4 font-medium text-gray-800 dark:text-gray-200">{{ $row['label'] }}</td>
                                <td class="py-3 px-4 text-right font-semibold text-emerald-600 dark:text-emerald-400">{{ $currency }} {{ number_format($row['amount'], 3) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>
</x-filament-panels::page>
