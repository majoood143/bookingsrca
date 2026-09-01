<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PosSalesReportExport implements FromArray, WithStyles, WithTitle, ShouldAutoSize
{
    protected array $boldRows = [];

    public function __construct(
        protected array $report,
        protected string $locale,
        protected string $currency,
    ) {
    }

    public function title(): string
    {
        return __('pos_reports.sales.document_title', [], $this->locale);
    }

    public function array(): array
    {
        $rows = [];
        $this->boldRows = [];

        $addBold = function (array $row) use (&$rows) {
            $this->boldRows[] = count($rows) + 1;
            $rows[] = $row;
        };
        $add = function (array $row) use (&$rows) {
            $rows[] = $row;
        };

        $addBold([__('pos_reports.sales.document_title', [], $this->locale)]);
        $add([__('pos_reports.document.period', [
            'from' => $this->report['from']->format('Y-m-d'),
            'to' => $this->report['to']->format('Y-m-d'),
        ], $this->locale)]);
        $add([__('pos_reports.document.generated_on', [], $this->locale) . ': ' . now()->format('Y-m-d H:i')]);
        $add([]);

        $add([__('pos_reports.sales.stats.total_revenue', [], $this->locale), number_format($this->report['totalRevenue'], 3) . ' ' . $this->currency]);
        $add([__('pos_reports.sales.stats.total_quantity', [], $this->locale), $this->report['totalQuantity']]);
        $add([__('pos_reports.sales.stats.order_count', [], $this->locale), $this->report['orderCount']]);
        $add([__('pos_reports.sales.stats.avg_order_value', [], $this->locale), number_format($this->report['avgOrderValue'], 3) . ' ' . $this->currency]);
        $add([]);

        foreach ([
            'byProduct' => ['pos_reports.sales.sections.by_product', 'pos_reports.sales.columns.product'],
            'byCategory' => ['pos_reports.sales.sections.by_category', 'pos_reports.sales.columns.category'],
            'byBrand' => ['pos_reports.sales.sections.by_brand', 'pos_reports.sales.columns.brand'],
            'byChannel' => ['pos_reports.sales.sections.by_channel', 'pos_reports.sales.columns.channel'],
        ] as $key => [$sectionKey, $labelKey]) {
            $addBold([__($sectionKey, [], $this->locale)]);
            $addBold([
                __($labelKey, [], $this->locale),
                __('pos_reports.sales.columns.quantity', [], $this->locale),
                __('pos_reports.sales.columns.revenue', [], $this->locale),
            ]);
            foreach ($this->report[$key] as $row) {
                $add([$row['label'], $row['quantity'], number_format($row['revenue'], 3) . ' ' . $this->currency]);
            }
            $add([]);
        }

        $addBold([__('pos_reports.sales.sections.trend', [], $this->locale)]);
        $addBold([
            __('pos_reports.sales.columns.date', [], $this->locale),
            __('pos_reports.sales.columns.quantity', [], $this->locale),
            __('pos_reports.sales.columns.revenue', [], $this->locale),
        ]);
        foreach ($this->report['trend'] as $row) {
            $add([$row['date'], $row['quantity'], number_format($row['revenue'], 3) . ' ' . $this->currency]);
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $styles = [];
        foreach ($this->boldRows as $rowNum) {
            $styles[$rowNum] = ['font' => ['bold' => true]];
        }

        return $styles;
    }
}
