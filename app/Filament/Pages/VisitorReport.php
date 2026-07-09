<?php

namespace App\Filament\Pages;

use App\Exports\VisitorReportExport;
use App\Filament\Pages\Concerns\HasReportPeriodFilter;
use App\Models\Booking;
use App\Models\Event;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\LaravelPdf\Enums\Format;
use Spatie\LaravelPdf\Facades\Pdf;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class VisitorReport extends Page implements HasForms
{
    use InteractsWithForms;
    use HasReportPeriodFilter;
    use HasPageShield;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|\UnitEnum|null $navigationGroup = 'Booking Management';

    protected static ?int $navigationSort = 11;

    protected string $view = 'filament.pages.visitor-report';

    public ?array $data = [];

    public function getTitle(): string
    {
        return __('visitor_report.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('visitor_report.navigation.label');
    }

    public function mount(): void
    {
        $this->form->fill([
            'period'       => 'this_month',
            'event_id'     => null,
            'event_date'   => null,
            'time_slot_id' => null,
            'date_from'    => null,
            'date_to'      => null,
            'language'     => app()->getLocale(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(['default' => 1, 'sm' => 2, 'lg' => 7])
                    ->schema([
                        Select::make('period')
                            ->label(__('visitor_report.filters.period'))
                            ->options([
                                'today'      => __('visitor_report.periods.today'),
                                'this_week'  => __('visitor_report.periods.this_week'),
                                'this_month' => __('visitor_report.periods.this_month'),
                                'last_month' => __('visitor_report.periods.last_month'),
                                'this_year'  => __('visitor_report.periods.this_year'),
                                'custom'     => __('visitor_report.periods.custom'),
                            ])
                            ->default('this_month')
                            ->selectablePlaceholder(false)
                            ->live(),

                        Select::make('event_id')
                            ->label(__('visitor_report.filters.event'))
                            ->options(function () {
                                $locale = app()->getLocale();
                                return Event::query()
                                    ->orderByDesc('start_date')
                                    ->get()
                                    ->mapWithKeys(fn($e) => [
                                        $e->id => $e->getTranslation('title', $locale),
                                    ]);
                            })
                            ->placeholder(__('visitor_report.filters.all_events'))
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function (callable $set) {
                                $set('event_date', null);
                                $set('time_slot_id', null);
                            }),

                        DatePicker::make('event_date')
                            ->label(__('visitor_report.filters.event_date'))
                            ->native(false)
                            ->placeholder(__('visitor_report.filters.all_dates'))
                            ->disabled(fn(callable $get) => !$get('event_id'))
                            ->minDate(fn(callable $get) => $this->getEventDateBounds($get('event_id'))[0])
                            ->maxDate(fn(callable $get) => $this->getEventDateBounds($get('event_id'))[1])
                            ->disabledDates(fn(callable $get) => $this->getDisabledEventDates($get('event_id')))
                            ->live()
                            ->afterStateUpdated(fn(callable $set) => $set('time_slot_id', null)),

                        Select::make('time_slot_id')
                            ->label(__('visitor_report.filters.time_slot'))
                            ->options(fn(callable $get) => $this->getTimeSlotOptions($get('event_id'), $get('event_date')))
                            ->placeholder(__('visitor_report.filters.all_slots'))
                            ->disabled(fn(callable $get) => !$get('event_id') || !$get('event_date'))
                            ->live(),

                        DatePicker::make('date_from')
                            ->label(__('visitor_report.filters.date_from'))
                            ->visible(fn(callable $get) => $get('period') === 'custom')
                            ->live(),

                        DatePicker::make('date_to')
                            ->label(__('visitor_report.filters.date_to'))
                            ->visible(fn(callable $get) => $get('period') === 'custom')
                            ->live(),

                        Select::make('language')
                            ->label(__('visitor_report.filters.language'))
                            ->options([
                                'en' => __('visitor_report.languages.en'),
                                'ar' => __('visitor_report.languages.ar'),
                            ])
                            ->default(app()->getLocale())
                            ->selectablePlaceholder(false)
                            ->live(),
                    ]),
            ])
            ->statePath('data');
    }

    public function getReportData(): array
    {
        [$from, $to] = $this->getDateRange();

        $eventId    = $this->data['event_id'] ?? null;
        $eventDate  = $this->data['event_date'] ?? null;
        $timeSlotId = $this->data['time_slot_id'] ?? null;
        $locale = $this->getReportLanguage();

        $bookings = Booking::query()
            ->with(['event', 'timeSlot', 'attendees.ticketType', 'ticketType'])
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->whereBetween('event_date', [$from->toDateString(), $to->toDateString()])
            ->when($eventId, fn($q) => $q->where('event_id', $eventId))
            ->when($eventDate, fn($q) => $q->where('event_date', $eventDate))
            ->when($timeSlotId, fn($q) => $q->where('time_slot_id', $timeSlotId))
            ->get();

        $nationalityOptions = trans('booking.options.nationality', [], $locale);

        $totalVisitors = 0;
        $eventIds = [];
        $genderCounts = ['male' => 0, 'female' => 0, 'unspecified' => 0];
        $ticketTypeCounts = [];
        $timeSlotCounts = [];
        $countryCounts = [];

        foreach ($bookings as $booking) {
            $eventIds[$booking->event_id] = true;

            foreach ($booking->attendees as $attendee) {
                $totalVisitors++;

                $gender = in_array($attendee->gender, ['male', 'female']) ? $attendee->gender : 'unspecified';
                $genderCounts[$gender]++;

                $ticketType = $attendee->ticketType ?? $booking->ticketType;
                $ttKey = $ticketType?->id ?? 0;
                if (!isset($ticketTypeCounts[$ttKey])) {
                    $ticketTypeCounts[$ttKey] = [
                        'label' => $ticketType?->getTranslation('name', $locale) ?? __('visitor_report.columns.other', [], $locale),
                        'count' => 0,
                    ];
                }
                $ticketTypeCounts[$ttKey]['count']++;

                $tsKey = $booking->time_slot_id ?? 0;
                if (!isset($timeSlotCounts[$tsKey])) {
                    $timeSlotCounts[$tsKey] = [
                        'label' => $booking->timeSlot
                            ? $this->forceLtr($booking->timeSlot->getTimeRange())
                            : __('visitor_report.columns.other', [], $locale),
                        'count' => 0,
                    ];
                }
                $timeSlotCounts[$tsKey]['count']++;

                $countryCode = $attendee->nationality ?: '__unspecified__';
                if (!isset($countryCounts[$countryCode])) {
                    $countryCounts[$countryCode] = [
                        'label' => $countryCode === '__unspecified__'
                            ? __('visitor_report.gender.unspecified', [], $locale)
                            : ($nationalityOptions[$countryCode] ?? $countryCode),
                        'count' => 0,
                    ];
                }
                $countryCounts[$countryCode]['count']++;
            }
        }

        $checkedInCount = $bookings->where('status', 'checked_in')->count();

        $byTicket = collect($ticketTypeCounts)
            ->sortByDesc('count')
            ->values()
            ->map(fn($row) => [
                'label' => $row['label'],
                'count' => $row['count'],
                'percentage' => $totalVisitors > 0 ? round($row['count'] / $totalVisitors * 100, 1) : 0,
            ]);

        $byTimeSlot = collect($timeSlotCounts)
            ->sortByDesc('count')
            ->values()
            ->map(fn($row) => [
                'label' => $row['label'],
                'count' => $row['count'],
                'percentage' => $totalVisitors > 0 ? round($row['count'] / $totalVisitors * 100, 1) : 0,
            ]);

        $sortedCountries = collect($countryCounts)->sortByDesc('count')->values();
        $topCountries = $sortedCountries->take(10);
        $otherCountriesCount = $sortedCountries->skip(10)->sum('count');
        $byCountry = $topCountries->map(fn($row) => [
            'label' => $row['label'],
            'count' => $row['count'],
            'percentage' => $totalVisitors > 0 ? round($row['count'] / $totalVisitors * 100, 1) : 0,
        ]);
        if ($otherCountriesCount > 0) {
            $byCountry->push([
                'label' => __('visitor_report.columns.other', [], $locale),
                'count' => $otherCountriesCount,
                'percentage' => $totalVisitors > 0 ? round($otherCountriesCount / $totalVisitors * 100, 1) : 0,
            ]);
        }

        $byGender = collect([
            ['key' => 'male', 'label' => __('visitor_report.gender.male', [], $locale), 'count' => $genderCounts['male']],
            ['key' => 'female', 'label' => __('visitor_report.gender.female', [], $locale), 'count' => $genderCounts['female']],
            ['key' => 'unspecified', 'label' => __('visitor_report.gender.unspecified', [], $locale), 'count' => $genderCounts['unspecified']],
        ])->map(fn($row) => [
            ...$row,
            'percentage' => $totalVisitors > 0 ? round($row['count'] / $totalVisitors * 100, 1) : 0,
        ])->filter(fn($row) => $row['count'] > 0)->values();

        return [
            'from' => $from,
            'to' => $to,
            'totalVisitors' => $totalVisitors,
            'totalBookings' => $bookings->count(),
            'checkedInCount' => $checkedInCount,
            'eventsCovered' => count($eventIds),
            'byGender' => $byGender,
            'byTicket' => $byTicket,
            'byTimeSlot' => $byTimeSlot,
            'byCountry' => $byCountry,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadPdf')
                ->label(__('visitor_report.actions.download_pdf'))
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->action(function () {
                    $report = $this->getReportData();
                    $locale = $this->getReportLanguage();
                    $period = $this->data['period'] ?? 'this_month';
                    $filename = __('visitor_report.export.filename', [], $locale) . '-' . $period . '-' . now()->format('Y-m-d') . '.pdf';

                    $pdf = Pdf::view('reports.visitor-report-pdf', [
                        ...$report,
                        'locale' => $locale,
                    ])
                        ->headerView('reports.partials.pdf-header', [
                            'title' => __('visitor_report.document_title', [], $locale),
                            'periodLabel' => __('visitor_report.document.period', [
                                'from' => $report['from']->format('Y-m-d'),
                                'to' => $report['to']->format('Y-m-d'),
                            ], $locale),
                            'locale' => $locale,
                            'logoBase64' => $this->getLogoBase64(),
                        ])
                        ->footerView('reports.partials.pdf-footer', [
                            'locale' => $locale,
                        ])
                        ->format(Format::A4)
                        ->margins(38, 12, 18, 12, 'mm')
                        ->withBrowsershot(fn($browsershot) => $browsershot->waitForFunction('window.pdfReady === true', null, 15000));

                    return Response::streamDownload(
                        fn() => print($pdf->generatePdfContent()),
                        $filename,
                        ['Content-Type' => 'application/pdf'],
                    );
                }),

                

            Action::make('downloadCsv')
                ->label(__('visitor_report.actions.download_csv'))
                ->icon('heroicon-o-table-cells')
                ->color('gray')
                ->action(function () {
                    $report = $this->getReportData();
                    $locale = $this->getReportLanguage();
                    $period = $this->data['period'] ?? 'this_month';
                    $filename = __('visitor_report.export.filename', [], $locale) . '-' . $period . '-' . now()->format('Y-m-d') . '.csv';

                    return Response::streamDownload(function () use ($report, $locale) {
                        $out = fopen('php://output', 'w');
                        fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel/Arabic

                        fputcsv($out, [__('visitor_report.document_title', [], $locale)]);
                        fputcsv($out, [__('visitor_report.document.period', [
                            'from' => $report['from']->format('Y-m-d'),
                            'to' => $report['to']->format('Y-m-d'),
                        ], $locale)]);
                        fputcsv($out, [__('visitor_report.document.generated_on', [], $locale) . ': ' . now()->format('Y-m-d H:i')]);
                        fputcsv($out, []);

                        fputcsv($out, [__('visitor_report.stats.total_visitors', [], $locale), $report['totalVisitors']]);
                        fputcsv($out, [__('visitor_report.stats.total_bookings', [], $locale), $report['totalBookings']]);
                        fputcsv($out, [__('visitor_report.stats.checked_in', [], $locale), $report['checkedInCount']]);
                        fputcsv($out, [__('visitor_report.stats.events_covered', [], $locale), $report['eventsCovered']]);
                        fputcsv($out, []);

                        fputcsv($out, [__('visitor_report.sections.by_gender', [], $locale)]);
                        fputcsv($out, [__('visitor_report.columns.gender', [], $locale), __('visitor_report.columns.count', [], $locale), __('visitor_report.columns.percentage', [], $locale)]);
                        foreach ($report['byGender'] as $row) {
                            fputcsv($out, [$row['label'], $row['count'], $row['percentage'] . '%']);
                        }
                        fputcsv($out, []);

                        fputcsv($out, [__('visitor_report.sections.by_ticket', [], $locale)]);
                        fputcsv($out, [__('visitor_report.columns.ticket_type', [], $locale), __('visitor_report.columns.count', [], $locale), __('visitor_report.columns.percentage', [], $locale)]);
                        foreach ($report['byTicket'] as $row) {
                            fputcsv($out, [$row['label'], $row['count'], $row['percentage'] . '%']);
                        }
                        fputcsv($out, []);

                        fputcsv($out, [__('visitor_report.sections.by_time_slot', [], $locale)]);
                        fputcsv($out, [__('visitor_report.columns.time_slot', [], $locale), __('visitor_report.columns.count', [], $locale), __('visitor_report.columns.percentage', [], $locale)]);
                        foreach ($report['byTimeSlot'] as $row) {
                            fputcsv($out, [$row['label'], $row['count'], $row['percentage'] . '%']);
                        }
                        fputcsv($out, []);

                        fputcsv($out, [__('visitor_report.sections.by_country', [], $locale)]);
                        fputcsv($out, [__('visitor_report.columns.country', [], $locale), __('visitor_report.columns.count', [], $locale), __('visitor_report.columns.percentage', [], $locale)]);
                        foreach ($report['byCountry'] as $row) {
                            fputcsv($out, [$row['label'], $row['count'], $row['percentage'] . '%']);
                        }

                        fclose($out);
                    }, $filename, ['Content-Type' => 'text/csv']);
                }),

            Action::make('downloadExcel')
                ->label(__('visitor_report.actions.download_excel'))
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->action(function () {
                    $report = $this->getReportData();
                    $locale = $this->getReportLanguage();
                    $period = $this->data['period'] ?? 'this_month';
                    $filename = __('visitor_report.export.filename', [], $locale) . '-' . $period . '-' . now()->format('Y-m-d') . '.xlsx';

                    return Excel::download(new VisitorReportExport($report, $locale), $filename);
                }),
        ];
    }
}
