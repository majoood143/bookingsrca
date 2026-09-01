<x-filament-panels::page>
    <x-filament::section :heading="__('pos_reports.sales.filters.section')">
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

    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        @foreach ([
            ['label' => __('pos_reports.sales.stats.total_revenue'), 'value' => $currencyLabel . ' ' . number_format($report['totalRevenue'], 3)],
            ['label' => __('pos_reports.sales.stats.total_quantity'), 'value' => $report['totalQuantity']],
            ['label' => __('pos_reports.sales.stats.order_count'), 'value' => $report['orderCount']],
            ['label' => __('pos_reports.sales.stats.avg_order_value'), 'value' => $currencyLabel . ' ' . number_format($report['avgOrderValue'], 3)],
        ] as $stat)
            <div class="rounded-xl p-4 bg-emerald-50 dark:bg-emerald-900/30 flex flex-col gap-1 shadow-sm">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ $stat['label'] }}</span>
                <span class="text-2xl font-bold text-emerald-700 dark:text-emerald-300">{!! $stat['value'] !!}</span>
            </div>
        @endforeach
    </div>

    @foreach ([
        ['byProduct', __('pos_reports.sales.sections.by_product'), __('pos_reports.sales.columns.product')],
        ['byCategory', __('pos_reports.sales.sections.by_category'), __('pos_reports.sales.columns.category')],
        ['byBrand', __('pos_reports.sales.sections.by_brand'), __('pos_reports.sales.columns.brand')],
        ['byChannel', __('pos_reports.sales.sections.by_channel'), __('pos_reports.sales.columns.channel')],
    ] as [$key, $sectionLabel, $colLabel])
        <x-filament::section :heading="$sectionLabel">
            @if ($report[$key]->isEmpty())
                <p class="text-sm text-gray-400 dark:text-gray-500 py-4 text-center">{{ __('pos_reports.sales.no_data') }}</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="py-3 px-4 font-semibold text-gray-600 dark:text-gray-300">{{ $colLabel }}</th>
                                <th class="py-3 px-4 font-semibold text-gray-600 dark:text-gray-300 text-center">{{ __('pos_reports.sales.columns.quantity') }}</th>
                                <th class="py-3 px-4 font-semibold text-emerald-600 dark:text-emerald-400 text-right">{{ __('pos_reports.sales.columns.revenue') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach ($report[$key] as $row)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                    <td class="py-3 px-4 font-medium text-gray-800 dark:text-gray-200">{{ $row['label'] }}</td>
                                    <td class="py-3 px-4 text-center">{{ $row['quantity'] }}</td>
                                    <td class="py-3 px-4 text-right font-semibold text-emerald-700 dark:text-emerald-400">{!! $currencyLabel !!} {{ number_format($row['revenue'], 3) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>
    @endforeach
</x-filament-panels::page>
