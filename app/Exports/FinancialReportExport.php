<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FinancialReportExport implements FromArray, WithStyles, WithTitle, ShouldAutoSize
{
    protected array $boldRows = [];

    public function __construct(
        protected array $report,
        protected string $locale,
        protected string $currency,
    ) {}

    public function title(): string
    {
        return __('financial_report.document_title', [], $this->locale);
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

        $addBold([__('financial_report.document_title', [], $this->locale)]);
        $add([__('financial_report.document.period', [
            'from' => $this->report['from']->format('Y-m-d'),
            'to' => $this->report['to']->format('Y-m-d'),
        ], $this->locale)]);
        $add([__('financial_report.document.generated_on', [], $this->locale) . ': ' . now()->format('Y-m-d H:i')]);
        $add([]);

        $add([__('financial_report.stats.total_revenue', [], $this->locale), number_format($this->report['totalRevenue'], 3) . ' ' . $this->currency]);
        $add([__('financial_report.stats.total_paid', [], $this->locale), number_format($this->report['totalPaid'], 3) . ' ' . $this->currency]);
        $add([__('financial_report.stats.balance_due', [], $this->locale), number_format($this->report['balanceDue'], 3) . ' ' . $this->currency]);
        $add([__('financial_report.stats.total_bookings', [], $this->locale), $this->report['totalBookings']]);
        $add([__('financial_report.stats.avg_booking', [], $this->locale), number_format($this->report['avgBooking'], 3) . ' ' . $this->currency]);
        $add([]);

        $addBold([__('financial_report.sections.by_ticket', [], $this->locale)]);
        $addBold([
            __('financial_report.columns.ticket_type', [], $this->locale),
            __('financial_report.columns.bookings', [], $this->locale),
            __('financial_report.columns.revenue', [], $this->locale),
        ]);
        foreach ($this->report['byTicket'] as $row) {
            $add([$row['label'], $row['bookings'], number_format($row['revenue'], 3) . ' ' . $this->currency]);
        }
        $add([]);

        $addBold([__('financial_report.sections.by_event', [], $this->locale)]);
        $addBold([
            __('financial_report.columns.event', [], $this->locale),
            __('financial_report.columns.bookings', [], $this->locale),
            __('financial_report.columns.revenue', [], $this->locale),
        ]);
        foreach ($this->report['byEvent'] as $row) {
            $add([$row['label'], $row['bookings'], number_format($row['revenue'], 3) . ' ' . $this->currency]);
        }
        $add([]);

        $addBold([__('financial_report.sections.by_payment_method', [], $this->locale)]);
        $addBold([
            __('financial_report.columns.payment_method', [], $this->locale),
            __('financial_report.columns.revenue', [], $this->locale),
        ]);
        foreach ($this->report['byPaymentMethod'] as $row) {
            $add([$row['label'], number_format($row['amount'], 3) . ' ' . $this->currency]);
        }
        $add([]);

        $addBold([__('financial_report.sections.revenue_trend', [], $this->locale)]);
        $addBold([
            __('financial_report.columns.date', [], $this->locale),
            __('financial_report.columns.revenue', [], $this->locale),
        ]);
        foreach ($this->report['revenueTrend'] as $row) {
            $add([$row['date'], number_format($row['revenue'], 3) . ' ' . $this->currency]);
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
