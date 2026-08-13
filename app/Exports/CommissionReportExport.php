<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CommissionReportExport implements FromArray, WithStyles, WithTitle, ShouldAutoSize
{
    protected array $boldRows = [];

    public function __construct(
        protected array $report,
        protected string $locale,
        protected string $currency,
    ) {}

    public function title(): string
    {
        return __('commission_report.document_title', [], $this->locale);
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

        $addBold([__('commission_report.document_title', [], $this->locale)]);
        $add([__('commission_report.document.period', [
            'from' => $this->report['from']->format('Y-m-d'),
            'to' => $this->report['to']->format('Y-m-d'),
        ], $this->locale)]);
        $add([__('commission_report.document.generated_on', [], $this->locale) . ': ' . now()->format('Y-m-d H:i')]);
        $add([]);

        $add([__('commission_report.stats.total_revenue', [], $this->locale), number_format($this->report['totalRevenue'], 3) . ' ' . $this->currency]);
        $add([__('commission_report.stats.total_commission', [], $this->locale), number_format($this->report['totalCommission'], 3) . ' ' . $this->currency]);
        $add([__('commission_report.stats.booking_count', [], $this->locale), $this->report['bookingCount']]);
        $add([__('commission_report.stats.avg_rate', [], $this->locale), number_format($this->report['avgRate'], 2) . '%']);
        $add([]);

        $addBold([__('commission_report.sections.by_event', [], $this->locale)]);
        $addBold([
            __('commission_report.columns.event', [], $this->locale),
            __('commission_report.columns.count', [], $this->locale),
            __('commission_report.columns.rate', [], $this->locale),
            __('commission_report.columns.revenue', [], $this->locale),
            __('commission_report.columns.commission', [], $this->locale),
        ]);
        foreach ($this->report['byEvent'] as $row) {
            $add([
                $row['label'],
                $row['count'],
                $row['rate'],
                number_format($row['revenue'], 3) . ' ' . $this->currency,
                number_format($row['commission'], 3) . ' ' . $this->currency,
            ]);
        }
        $add([]);

        $addBold([__('commission_report.sections.trend', [], $this->locale)]);
        $addBold([
            __('commission_report.columns.date', [], $this->locale),
            __('commission_report.columns.revenue', [], $this->locale),
            __('commission_report.columns.commission', [], $this->locale),
        ]);
        foreach ($this->report['commissionTrend'] as $row) {
            $add([
                $row['date'],
                number_format($row['revenue'], 3) . ' ' . $this->currency,
                number_format($row['commission'], 3) . ' ' . $this->currency,
            ]);
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
