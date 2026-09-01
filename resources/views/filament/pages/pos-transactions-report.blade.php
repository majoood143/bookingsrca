<x-filament-panels::page>
    <x-filament::section :heading="__('pos_reports.transactions.filters.section')">
        <form wire:submit.prevent>
            {{ $this->form }}
        </form>
    </x-filament::section>

    @php
        $report = $this->getReportData();
        $currencyLabel = view('partials.currency-label')->render();
    @endphp

    <p class="text-sm text-gray-500 dark:text-gray-400 -mt-2">
        {{ __('pos_reports.document.period', ['from' => $report['from']->format('Y-m-d'), 'to' => $report['to']->format('Y-m-d')]) }}
    </p>

    <div class="grid grid-cols-2 gap-4 sm:grid-cols-2">
        <div class="rounded-xl p-4 bg-blue-50 dark:bg-blue-900/30 flex flex-col gap-1 shadow-sm">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ __('pos_reports.transactions.stats.total_amount') }}</span>
            <span class="text-2xl font-bold text-blue-700 dark:text-blue-300">{!! $currencyLabel !!} {{ number_format($report['totalAmount'], 3) }}</span>
        </div>
        <div class="rounded-xl p-4 bg-blue-50 dark:bg-blue-900/30 flex flex-col gap-1 shadow-sm">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ __('pos_reports.transactions.stats.transaction_count') }}</span>
            <span class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{ $report['transactionCount'] }}</span>
        </div>
    </div>

    @foreach ([
        ['byMethod', __('pos_reports.transactions.sections.by_method'), __('pos_reports.transactions.columns.method')],
        ['byStatus', __('pos_reports.transactions.sections.by_status'), __('pos_reports.transactions.columns.status')],
    ] as [$key, $sectionLabel, $colLabel])
        <x-filament::section :heading="$sectionLabel">
            @if ($report[$key]->isEmpty())
                <p class="text-sm text-gray-400 dark:text-gray-500 py-4 text-center">{{ __('pos_reports.transactions.no_data') }}</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="py-3 px-4 font-semibold text-gray-600 dark:text-gray-300">{{ $colLabel }}</th>
                                <th class="py-3 px-4 font-semibold text-gray-600 dark:text-gray-300 text-center">{{ __('pos_reports.transactions.columns.count') }}</th>
                                <th class="py-3 px-4 font-semibold text-blue-600 dark:text-blue-400 text-right">{{ __('pos_reports.transactions.columns.amount') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach ($report[$key] as $row)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                    <td class="py-3 px-4 font-medium text-gray-800 dark:text-gray-200">{{ $row['label'] }}</td>
                                    <td class="py-3 px-4 text-center">{{ $row['count'] }}</td>
                                    <td class="py-3 px-4 text-right font-semibold text-blue-700 dark:text-blue-400">{!! $currencyLabel !!} {{ number_format($row['amount'], 3) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>
    @endforeach
</x-filament-panels::page>
