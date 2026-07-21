<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExpensesReportExport implements FromArray, WithStyles, WithTitle, ShouldAutoSize
{
    protected array $boldRows = [];

    public function __construct(
        protected array $report,
        protected string $locale,
        protected string $currency,
    ) {}

    public function title(): string
    {
        return __('expenses_report.document_title', [], $this->locale);
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

        $addBold([__('expenses_report.document_title', [], $this->locale)]);
        $add([__('expenses_report.document.period', [
            'from' => $this->report['from']->format('Y-m-d'),
            'to' => $this->report['to']->format('Y-m-d'),
        ], $this->locale)]);
        $add([__('expenses_report.document.generated_on', [], $this->locale) . ': ' . now()->format('Y-m-d H:i')]);
        $add([]);

        $add([__('expenses_report.stats.total_amount', [], $this->locale), number_format($this->report['totalAmount'], 3) . ' ' . $this->currency]);
        $add([__('expenses_report.stats.total_tax', [], $this->locale), number_format($this->report['totalTax'], 3) . ' ' . $this->currency]);
        $add([__('expenses_report.stats.total_paid', [], $this->locale), number_format($this->report['totalPaid'], 3) . ' ' . $this->currency]);
        $add([__('expenses_report.stats.total_pending', [], $this->locale), number_format($this->report['totalPending'], 3) . ' ' . $this->currency]);
        $add([__('expenses_report.stats.expense_count', [], $this->locale), $this->report['expenseCount']]);
        $add([__('expenses_report.stats.avg_expense', [], $this->locale), number_format($this->report['avgExpense'], 3) . ' ' . $this->currency]);
        $add([]);

        $addBold([__('expenses_report.sections.by_category', [], $this->locale)]);
        $addBold([
            __('expenses_report.columns.category', [], $this->locale),
            __('expenses_report.columns.count', [], $this->locale),
            __('expenses_report.columns.amount', [], $this->locale),
        ]);
        foreach ($this->report['byCategory'] as $row) {
            $add([$row['label'], $row['count'], number_format($row['amount'], 3) . ' ' . $this->currency]);
        }
        $add([]);

        $addBold([__('expenses_report.sections.by_type', [], $this->locale)]);
        $addBold([
            __('expenses_report.columns.type', [], $this->locale),
            __('expenses_report.columns.count', [], $this->locale),
            __('expenses_report.columns.amount', [], $this->locale),
        ]);
        foreach ($this->report['byType'] as $row) {
            $add([$row['label'], $row['count'], number_format($row['amount'], 3) . ' ' . $this->currency]);
        }
        $add([]);

        $addBold([__('expenses_report.sections.by_event', [], $this->locale)]);
        $addBold([
            __('expenses_report.columns.event', [], $this->locale),
            __('expenses_report.columns.count', [], $this->locale),
            __('expenses_report.columns.amount', [], $this->locale),
        ]);
        foreach ($this->report['byEvent'] as $row) {
            $add([$row['label'], $row['count'], number_format($row['amount'], 3) . ' ' . $this->currency]);
        }
        $add([]);

        $addBold([__('expenses_report.sections.by_payment_status', [], $this->locale)]);
        $addBold([
            __('expenses_report.columns.payment_status', [], $this->locale),
            __('expenses_report.columns.amount', [], $this->locale),
        ]);
        foreach ($this->report['byPaymentStatus'] as $row) {
            $add([$row['label'], number_format($row['amount'], 3) . ' ' . $this->currency]);
        }
        $add([]);

        $addBold([__('expenses_report.sections.expense_trend', [], $this->locale)]);
        $addBold([
            __('expenses_report.columns.date', [], $this->locale),
            __('expenses_report.columns.amount', [], $this->locale),
        ]);
        foreach ($this->report['expenseTrend'] as $row) {
            $add([$row['date'], number_format($row['amount'], 3) . ' ' . $this->currency]);
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
