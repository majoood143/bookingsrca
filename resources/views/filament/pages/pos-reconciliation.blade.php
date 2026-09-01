<x-filament-panels::page>
    <x-filament::section>
        <form wire:submit.prevent>
            {{ $this->form }}
        </form>
    </x-filament::section>

    @php
        $report = $this->getReportData();
        $currencyLabel = view('partials.currency-label')->render();
    @endphp

    <p class="text-sm text-gray-500 dark:text-gray-400 -mt-2">
        {{ $report['from']->format('Y-m-d') }} &ndash; {{ $report['to']->format('Y-m-d') }}
    </p>

    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-6">
        @php
            $stats = [
                ['label' => __('Total Sales'), 'value' => $currencyLabel . ' ' . number_format($report['totalSales'], 3), 'color' => 'bg-emerald-100 dark:bg-emerald-900/40', 'text' => 'text-emerald-700 dark:text-emerald-300'],
                ['label' => __('pos.transaction.fields.tax_total'), 'value' => $currencyLabel . ' ' . number_format($report['totalTax'], 3), 'color' => 'bg-gray-100 dark:bg-gray-800', 'text' => 'text-gray-600 dark:text-gray-300'],
                ['label' => __('pos.reconciliation.discounts_given'), 'value' => $currencyLabel . ' ' . number_format($report['totalDiscount'], 3), 'color' => 'bg-amber-100 dark:bg-amber-900/40', 'text' => 'text-amber-700 dark:text-amber-300'],
                ['label' => __('pos.reconciliation.refunds_issued'), 'value' => $currencyLabel . ' ' . number_format($report['refundedTotal'], 3), 'color' => 'bg-red-100 dark:bg-red-900/40', 'text' => 'text-red-700 dark:text-red-300'],
                ['label' => __('Transactions'), 'value' => $report['transactionCount'], 'color' => 'bg-blue-100 dark:bg-blue-900/40', 'text' => 'text-blue-700 dark:text-blue-300'],
                ['label' => __('pos.transaction.actions.void'), 'value' => $report['voidedCount'], 'color' => 'bg-gray-100 dark:bg-gray-800', 'text' => 'text-gray-800 dark:text-gray-100'],
            ];
        @endphp
        @foreach ($stats as $stat)
            <div class="rounded-xl p-4 {{ $stat['color'] }} flex flex-col gap-1 shadow-sm">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide leading-tight">{{ $stat['label'] }}</span>
                <span class="text-2xl font-bold {{ $stat['text'] }}">{!! $stat['value'] !!}</span>
            </div>
        @endforeach
    </div>

    <x-filament::section :heading="__('pos.reconciliation.tickets_vs_merchandise')">
        <div class="grid grid-cols-2 gap-4">
            <div class="rounded-xl p-4 bg-indigo-50 dark:bg-indigo-900/30">
                <span class="text-xs uppercase text-gray-500 dark:text-gray-400">{{ __('Tickets') }}</span>
                <p class="text-xl font-bold text-indigo-700 dark:text-indigo-300">{!! $currencyLabel !!} {{ number_format($report['ticketRevenue'], 3) }}</p>
            </div>
            <div class="rounded-xl p-4 bg-teal-50 dark:bg-teal-900/30">
                <span class="text-xs uppercase text-gray-500 dark:text-gray-400">{{ __('Merchandise') }}</span>
                <p class="text-xl font-bold text-teal-700 dark:text-teal-300">{!! $currencyLabel !!} {{ number_format($report['merchandiseRevenue'], 3) }}</p>
            </div>
        </div>
    </x-filament::section>

    @foreach ([
        ['key' => 'byMethod', 'heading' => 'pos.reconciliation.sales_by_method', 'label' => __('Method'), 'countCol' => false],
        ['key' => 'byTerminal', 'heading' => 'pos.reconciliation.sales_by_terminal', 'label' => __('pos.transaction.fields.terminal'), 'countCol' => true],
        ['key' => 'byCashier', 'heading' => 'pos.reconciliation.sales_by_cashier', 'label' => __('pos.transaction.fields.cashier'), 'countCol' => true],
        ['key' => 'discountsGiven', 'heading' => 'pos.reconciliation.discounts_given', 'label' => __('pos.discount.fields.code'), 'countCol' => true],
    ] as $table)
        <x-filament::section :heading="__($table['heading'])">
            @if ($report[$table['key']]->isEmpty())
                <p class="text-sm text-gray-400 dark:text-gray-500 py-4 text-center">{{ __('No data.') }}</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="py-3 px-4 font-semibold text-gray-600 dark:text-gray-300">{{ $table['label'] }}</th>
                                @if ($table['countCol'])
                                    <th class="py-3 px-4 font-semibold text-gray-600 dark:text-gray-300 text-center">{{ __('Count') }}</th>
                                @endif
                                <th class="py-3 px-4 font-semibold text-amber-600 dark:text-amber-400 text-right">{{ __('Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach ($report[$table['key']] as $row)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                    <td class="py-3 px-4 font-medium text-gray-800 dark:text-gray-200">{{ $row['label'] }}</td>
                                    @if ($table['countCol'])
                                        <td class="py-3 px-4 text-center text-gray-700 dark:text-gray-300">{{ $row['count'] }}</td>
                                    @endif
                                    <td class="py-3 px-4 text-right font-semibold text-amber-600 dark:text-amber-400">{!! $currencyLabel !!} {{ number_format($row['amount'], 3) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>
    @endforeach
</x-filament-panels::page>
