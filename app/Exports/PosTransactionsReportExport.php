<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PosTransactionsReportExport implements FromArray, WithStyles, WithTitle, ShouldAutoSize
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
        return __('pos_reports.transactions.document_title', [], $this->locale);
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

        $addBold([__('pos_reports.transactions.document_title', [], $this->locale)]);
        $add([__('pos_reports.document.period', [
            'from' => $this->report['from']->format('Y-m-d'),
            'to' => $this->report['to']->format('Y-m-d'),
        ], $this->locale)]);
        $add([__('pos_reports.document.generated_on', [], $this->locale) . ': ' . now()->format('Y-m-d H:i')]);
        $add([]);

        $add([__('pos_reports.transactions.stats.total_amount', [], $this->locale), number_format($this->report['totalAmount'], 3) . ' ' . $this->currency]);
        $add([__('pos_reports.transactions.stats.transaction_count', [], $this->locale), $this->report['transactionCount']]);
        $add([]);

        $addBold([__('pos_reports.transactions.sections.by_method', [], $this->locale)]);
        $addBold([
            __('pos_reports.transactions.columns.method', [], $this->locale),
            __('pos_reports.transactions.columns.count', [], $this->locale),
            __('pos_reports.transactions.columns.amount', [], $this->locale),
        ]);
        foreach ($this->report['byMethod'] as $row) {
            $add([$row['label'], $row['count'], number_format($row['amount'], 3) . ' ' . $this->currency]);
        }
        $add([]);

        $addBold([__('pos_reports.transactions.sections.by_status', [], $this->locale)]);
        $addBold([
            __('pos_reports.transactions.columns.status', [], $this->locale),
            __('pos_reports.transactions.columns.count', [], $this->locale),
            __('pos_reports.transactions.columns.amount', [], $this->locale),
        ]);
        foreach ($this->report['byStatus'] as $row) {
            $add([$row['label'], $row['count'], number_format($row['amount'], 3) . ' ' . $this->currency]);
        }
        $add([]);

        $addBold([__('pos_reports.transactions.sections.list', [], $this->locale)]);
        $addBold([
            __('pos_reports.transactions.columns.date', [], $this->locale),
            __('pos_reports.transactions.columns.method', [], $this->locale),
            __('pos_reports.transactions.columns.source', [], $this->locale),
            __('pos_reports.transactions.columns.status', [], $this->locale),
            __('pos_reports.transactions.columns.reference', [], $this->locale),
            __('pos_reports.transactions.columns.amount', [], $this->locale),
        ]);
        foreach ($this->report['list'] as $row) {
            $add([
                $row['date'],
                $row['method'],
                $row['source'],
                $row['status'],
                $row['reference'],
                number_format($row['amount'], 3) . ' ' . $this->currency,
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
