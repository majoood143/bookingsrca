<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VisitorReportExport implements FromArray, WithStyles, WithTitle, ShouldAutoSize
{
    protected array $boldRows = [];

    public function __construct(
        protected array $report,
        protected string $locale,
    ) {}

    public function title(): string
    {
        return __('visitor_report.document_title', [], $this->locale);
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

        $addBold([__('visitor_report.document_title', [], $this->locale)]);
        $add([__('visitor_report.document.period', [
            'from' => $this->report['from']->format('Y-m-d'),
            'to' => $this->report['to']->format('Y-m-d'),
        ], $this->locale)]);
        $add([__('visitor_report.document.generated_on', [], $this->locale) . ': ' . now()->format('Y-m-d H:i')]);
        $add([]);

        $add([__('visitor_report.stats.total_visitors', [], $this->locale), $this->report['totalVisitors']]);
        $add([__('visitor_report.stats.total_bookings', [], $this->locale), $this->report['totalBookings']]);
        $add([__('visitor_report.stats.checked_in', [], $this->locale), $this->report['checkedInCount']]);
        $add([__('visitor_report.stats.events_covered', [], $this->locale), $this->report['eventsCovered']]);
        $add([]);

        $addBold([__('visitor_report.sections.by_gender', [], $this->locale)]);
        $addBold([
            __('visitor_report.columns.gender', [], $this->locale),
            __('visitor_report.columns.count', [], $this->locale),
            __('visitor_report.columns.percentage', [], $this->locale),
        ]);
        foreach ($this->report['byGender'] as $row) {
            $add([$row['label'], $row['count'], $row['percentage'] . '%']);
        }
        $add([]);

        $addBold([__('visitor_report.sections.by_ticket', [], $this->locale)]);
        $addBold([
            __('visitor_report.columns.ticket_type', [], $this->locale),
            __('visitor_report.columns.count', [], $this->locale),
            __('visitor_report.columns.percentage', [], $this->locale),
        ]);
        foreach ($this->report['byTicket'] as $row) {
            $add([$row['label'], $row['count'], $row['percentage'] . '%']);
        }
        $add([]);

        $addBold([__('visitor_report.sections.by_time_slot', [], $this->locale)]);
        $addBold([
            __('visitor_report.columns.time_slot', [], $this->locale),
            __('visitor_report.columns.count', [], $this->locale),
            __('visitor_report.columns.percentage', [], $this->locale),
        ]);
        foreach ($this->report['byTimeSlot'] as $row) {
            $add([$row['label'], $row['count'], $row['percentage'] . '%']);
        }
        $add([]);

        $addBold([__('visitor_report.sections.by_country', [], $this->locale)]);
        $addBold([
            __('visitor_report.columns.country', [], $this->locale),
            __('visitor_report.columns.count', [], $this->locale),
            __('visitor_report.columns.percentage', [], $this->locale),
        ]);
        foreach ($this->report['byCountry'] as $row) {
            $add([$row['label'], $row['count'], $row['percentage'] . '%']);
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
