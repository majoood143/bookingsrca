<x-filament-panels::page>
    <x-filament::section :heading="__('expenses_report.sections.filters')">
        <form wire:submit.prevent>
            {{ $this->form }}
        </form>
    </x-filament::section>

    @php
        $report = $this->getReportData();
        $from   = $report['from'];
        $to     = $report['to'];
        $currency = \App\Models\BookingSetting::get('currency_code') ?: __('expenses_report.currency');
    @endphp

    <p class="text-sm text-gray-500 dark:text-gray-400 -mt-2">
        {{ __('expenses_report.document.period', ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}
    </p>

    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-6">
        @php
            $stats = [
                ['label' => __('expenses_report.stats.total_amount'), 'value' => $currency . ' ' . number_format($report['totalAmount'], 3), 'color' => 'bg-amber-100 dark:bg-amber-900/40', 'text' => 'text-amber-700 dark:text-amber-300'],
                ['label' => __('expenses_report.stats.total_tax'), 'value' => $currency . ' ' . number_format($report['totalTax'], 3), 'color' => 'bg-gray-100 dark:bg-gray-800', 'text' => 'text-gray-600 dark:text-gray-300'],
                ['label' => __('expenses_report.stats.total_paid'), 'value' => $currency . ' ' . number_format($report['totalPaid'], 3), 'color' => 'bg-emerald-100 dark:bg-emerald-900/40', 'text' => 'text-emerald-700 dark:text-emerald-300'],
                ['label' => __('expenses_report.stats.total_pending'), 'value' => $currency . ' ' . number_format($report['totalPending'], 3), 'color' => 'bg-red-100 dark:bg-red-900/40', 'text' => 'text-red-700 dark:text-red-300'],
                ['label' => __('expenses_report.stats.expense_count'), 'value' => $report['expenseCount'], 'color' => 'bg-gray-100 dark:bg-gray-800', 'text' => 'text-gray-800 dark:text-gray-100'],
                ['label' => __('expenses_report.stats.avg_expense'), 'value' => $currency . ' ' . number_format($report['avgExpense'], 3), 'color' => 'bg-blue-100 dark:bg-blue-900/40', 'text' => 'text-blue-700 dark:text-blue-300'],
            ];
        @endphp
        @foreach ($stats as $stat)
            <div class="rounded-xl p-4 {{ $stat['color'] }} flex flex-col gap-1 shadow-sm">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide leading-tight">{{ $stat['label'] }}</span>
                <span class="text-2xl font-bold {{ $stat['text'] }}">{{ $stat['value'] }}</span>
            </div>
        @endforeach
    </div>

    @foreach ([
        ['key' => 'byCategory', 'heading' => 'expenses_report.sections.by_category', 'label' => 'expenses_report.columns.category'],
        ['key' => 'byType', 'heading' => 'expenses_report.sections.by_type', 'label' => 'expenses_report.columns.type'],
        ['key' => 'byEvent', 'heading' => 'expenses_report.sections.by_event', 'label' => 'expenses_report.columns.event'],
    ] as $table)
        <x-filament::section :heading="__($table['heading'])">
            @if ($report[$table['key']]->isEmpty())
                <p class="text-sm text-gray-400 dark:text-gray-500 py-4 text-center">{{ __('expenses_report.no_data') }}</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="py-3 px-4 font-semibold text-gray-600 dark:text-gray-300">{{ __($table['label']) }}</th>
                                <th class="py-3 px-4 font-semibold text-gray-600 dark:text-gray-300 text-center">{{ __('expenses_report.columns.count') }}</th>
                                <th class="py-3 px-4 font-semibold text-amber-600 dark:text-amber-400 text-right">{{ __('expenses_report.columns.amount') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach ($report[$table['key']] as $row)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                    <td class="py-3 px-4 font-medium text-gray-800 dark:text-gray-200">{{ $row['label'] }}</td>
                                    <td class="py-3 px-4 text-center text-gray-700 dark:text-gray-300">{{ $row['count'] }}</td>
                                    <td class="py-3 px-4 text-right font-semibold text-amber-600 dark:text-amber-400">{{ $currency }} {{ number_format($row['amount'], 3) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>
    @endforeach

    <x-filament::section :heading="__('expenses_report.sections.by_payment_status')">
        @if ($report['byPaymentStatus']->isEmpty())
            <p class="text-sm text-gray-400 dark:text-gray-500 py-4 text-center">{{ __('expenses_report.no_data') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="py-3 px-4 font-semibold text-gray-600 dark:text-gray-300">{{ __('expenses_report.columns.payment_status') }}</th>
                            <th class="py-3 px-4 font-semibold text-amber-600 dark:text-amber-400 text-right">{{ __('expenses_report.columns.amount') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($report['byPaymentStatus'] as $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="py-3 px-4 font-medium text-gray-800 dark:text-gray-200">{{ $row['label'] }}</td>
                                <td class="py-3 px-4 text-right font-semibold text-amber-600 dark:text-amber-400">{{ $currency }} {{ number_format($row['amount'], 3) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>
</x-filament-panels::page>
