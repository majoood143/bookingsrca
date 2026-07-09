<?php

namespace App\Filament\Pages;

use App\Exports\FinancialReportExport;
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

class FinancialReport extends Page implements HasForms
{
    use InteractsWithForms;
    use HasReportPeriodFilter;
    use HasPageShield;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static string|\UnitEnum|null $navigationGroup = 'Booking Management';

    protected static ?int $navigationSort = 12;

    protected string $view = 'filament.pages.financial-report';

    public ?array $data = [];

    public function getTitle(): string
    {
        return __('financial_report.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('financial_report.navigation.label');
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
                            ->label(__('financial_report.filters.period'))
                            ->options([
                                'today'      => __('financial_report.periods.today'),
                                'this_week'  => __('financial_report.periods.this_week'),
                                'this_month' => __('financial_report.periods.this_month'),
                                'last_month' => __('financial_report.periods.last_month'),
                                'this_year'  => __('financial_report.periods.this_year'),
                                'custom'     => __('financial_report.periods.custom'),
                            ])
                            ->default('this_month')
                            ->selectablePlaceholder(false)
                            ->live(),

                        Select::make('event_id')
                            ->label(__('financial_report.filters.event'))
                            ->options(function () {
                                $locale = app()->getLocale();
                                return Event::query()
                                    ->orderByDesc('start_date')
                                    ->get()
                                    ->mapWithKeys(fn($e) => [
                                        $e->id => $e->getTranslation('title', $locale),
                                    ]);
                            })
                            ->placeholder(__('financial_report.filters.all_events'))
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function (callable $set) {
                                $set('event_date', null);
                                $set('time_slot_id', null);
                            }),

                        DatePicker::make('event_date')
                            ->label(__('financial_report.filters.event_date'))
                            ->native(false)
                            ->placeholder(__('financial_report.filters.all_dates'))
                            ->disabled(fn(callable $get) => !$get('event_id'))
                            ->minDate(fn(callable $get) => $this->getEventDateBounds($get('event_id'))[0])
                            ->maxDate(fn(callable $get) => $this->getEventDateBounds($get('event_id'))[1])
                            ->disabledDates(fn(callable $get) => $this->getDisabledEventDates($get('event_id')))
                            ->live()
                            ->afterStateUpdated(fn(callable $set) => $set('time_slot_id', null)),

                        Select::make('time_slot_id')
                            ->label(__('financial_report.filters.time_slot'))
                            ->options(fn(callable $get) => $this->getTimeSlotOptions($get('event_id'), $get('event_date')))
                            ->placeholder(__('financial_report.filters.all_slots'))
                            ->disabled(fn(callable $get) => !$get('event_id') || !$get('event_date'))
                            ->live(),

                        DatePicker::make('date_from')
                            ->label(__('financial_report.filters.date_from'))
                            ->visible(fn(callable $get) => $get('period') === 'custom')
                            ->live(),

                        DatePicker::make('date_to')
                            ->label(__('financial_report.filters.date_to'))
                            ->visible(fn(callable $get) => $get('period') === 'custom')
                            ->live(),

                        Select::make('language')
                            ->label(__('financial_report.filters.language'))
                            ->options([
                                'en' => __('financial_report.languages.en'),
                                'ar' => __('financial_report.languages.ar'),
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
            ->with(['event', 'ticketType', 'payments'])
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->whereBetween('event_date', [$from->toDateString(), $to->toDateString()])
            ->when($eventId, fn($q) => $q->where('event_id', $eventId))
            ->when($eventDate, fn($q) => $q->where('event_date', $eventDate))
            ->when($timeSlotId, fn($q) => $q->where('time_slot_id', $timeSlotId))
            ->get();

        $totalRevenue = (float) $bookings->sum('total_price');
        $totalPaid = (float) $bookings->sum(fn($b) => $b->total_paid);
        $balanceDue = max(0, $totalRevenue - $totalPaid);
        $totalBookings = $bookings->count();
        $avgBooking = $totalBookings > 0 ? $totalRevenue / $totalBookings : 0;

        $byTicket = $bookings->groupBy('ticket_type_id')->map(function ($group) use ($locale) {
            $first = $group->first();
            return [
                'label' => $first->ticketType?->getTranslation('name', $locale) ?? '—',
                'bookings' => $group->count(),
                'revenue' => (float) $group->sum('total_price'),
            ];
        })->sortByDesc('revenue')->values();

        $byEvent = $bookings->groupBy('event_id')->map(function ($group) use ($locale) {
            $first = $group->first();
            return [
                'label' => $first->event?->getTranslation('title', $locale) ?? '—',
                'bookings' => $group->count(),
                'revenue' => (float) $group->sum('total_price'),
            ];
        })->sortByDesc('revenue')->values();

        $paymentMethodTotals = [];
        foreach ($bookings as $booking) {
            foreach ($booking->payments as $payment) {
                $method = $payment->payment_method ?: 'cash';
                $paymentMethodTotals[$method] = ($paymentMethodTotals[$method] ?? 0) + (float) $payment->amount;
            }
        }
        $byPaymentMethod = collect($paymentMethodTotals)
            ->map(fn($amount, $method) => [
                'label' => __('financial_report.payment_methods.' . $method, [], $locale) !== 'financial_report.payment_methods.' . $method
                    ? __('financial_report.payment_methods.' . $method, [], $locale)
                    : ucfirst($method),
                'amount' => $amount,
            ])
            ->sortByDesc('amount')
            ->values();

        $revenueTrend = $bookings->groupBy(fn($b) => $b->event_date->format('Y-m-d'))
            ->map(fn($group, $date) => [
                'date' => $this->forceLtr($date),
                'revenue' => (float) $group->sum('total_price'),
            ])
            ->sortKeys()
            ->values();

        return [
            'from' => $from,
            'to' => $to,
            'totalRevenue' => $totalRevenue,
            'totalPaid' => $totalPaid,
            'balanceDue' => $balanceDue,
            'totalBookings' => $totalBookings,
            'avgBooking' => $avgBooking,
            'byTicket' => $byTicket,
            'byEvent' => $byEvent,
            'byPaymentMethod' => $byPaymentMethod,
            'revenueTrend' => $revenueTrend,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadPdf')
                ->label(__('financial_report.actions.download_pdf'))
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->action(function () {
                    $report = $this->getReportData();
                    $locale = $this->getReportLanguage();
                    $period = $this->data['period'] ?? 'this_month';
                    $filename = __('financial_report.export.filename', [], $locale) . '-' . $period . '-' . now()->format('Y-m-d') . '.pdf';

                    $pdf = Pdf::view('reports.financial-report-pdf', [
                        ...$report,
                        'locale' => $locale,
                    ])
                        ->headerView('reports.partials.pdf-header', [
                            'title' => __('financial_report.document_title', [], $locale),
                            'periodLabel' => __('financial_report.document.period', [
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
                ->label(__('financial_report.actions.download_csv'))
                ->icon('heroicon-o-table-cells')
                ->color('gray')
                ->action(function () {
                    $report = $this->getReportData();
                    $locale = $this->getReportLanguage();
                    $period = $this->data['period'] ?? 'this_month';
                    $filename = __('financial_report.export.filename', [], $locale) . '-' . $period . '-' . now()->format('Y-m-d') . '.csv';
                    $currency = __('financial_report.currency', [], $locale);

                    return Response::streamDownload(function () use ($report, $locale, $currency) {
                        $out = fopen('php://output', 'w');
                        fwrite($out, "\xEF\xBB\xBF");

                        fputcsv($out, [__('financial_report.document_title', [], $locale)]);
                        fputcsv($out, [__('financial_report.document.period', [
                            'from' => $report['from']->format('Y-m-d'),
                            'to' => $report['to']->format('Y-m-d'),
                        ], $locale)]);
                        fputcsv($out, [__('financial_report.document.generated_on', [], $locale) . ': ' . now()->format('Y-m-d H:i')]);
                        fputcsv($out, []);

                        fputcsv($out, [__('financial_report.stats.total_revenue', [], $locale), number_format($report['totalRevenue'], 3) . ' ' . $currency]);
                        fputcsv($out, [__('financial_report.stats.total_paid', [], $locale), number_format($report['totalPaid'], 3) . ' ' . $currency]);
                        fputcsv($out, [__('financial_report.stats.balance_due', [], $locale), number_format($report['balanceDue'], 3) . ' ' . $currency]);
                        fputcsv($out, [__('financial_report.stats.total_bookings', [], $locale), $report['totalBookings']]);
                        fputcsv($out, [__('financial_report.stats.avg_booking', [], $locale), number_format($report['avgBooking'], 3) . ' ' . $currency]);
                        fputcsv($out, []);

                        fputcsv($out, [__('financial_report.sections.by_ticket', [], $locale)]);
                        fputcsv($out, [__('financial_report.columns.ticket_type', [], $locale), __('financial_report.columns.bookings', [], $locale), __('financial_report.columns.revenue', [], $locale)]);
                        foreach ($report['byTicket'] as $row) {
                            fputcsv($out, [$row['label'], $row['bookings'], number_format($row['revenue'], 3) . ' ' . $currency]);
                        }
                        fputcsv($out, []);

                        fputcsv($out, [__('financial_report.sections.by_event', [], $locale)]);
                        fputcsv($out, [__('financial_report.columns.event', [], $locale), __('financial_report.columns.bookings', [], $locale), __('financial_report.columns.revenue', [], $locale)]);
                        foreach ($report['byEvent'] as $row) {
                            fputcsv($out, [$row['label'], $row['bookings'], number_format($row['revenue'], 3) . ' ' . $currency]);
                        }
                        fputcsv($out, []);

                        fputcsv($out, [__('financial_report.sections.by_payment_method', [], $locale)]);
                        fputcsv($out, [__('financial_report.columns.payment_method', [], $locale), __('financial_report.columns.revenue', [], $locale)]);
                        foreach ($report['byPaymentMethod'] as $row) {
                            fputcsv($out, [$row['label'], number_format($row['amount'], 3) . ' ' . $currency]);
                        }
                        fputcsv($out, []);

                        fputcsv($out, [__('financial_report.sections.revenue_trend', [], $locale)]);
                        fputcsv($out, [__('financial_report.columns.date', [], $locale), __('financial_report.columns.revenue', [], $locale)]);
                        foreach ($report['revenueTrend'] as $row) {
                            fputcsv($out, [$row['date'], number_format($row['revenue'], 3) . ' ' . $currency]);
                        }

                        fclose($out);
                    }, $filename, ['Content-Type' => 'text/csv']);
                }),

            Action::make('downloadExcel')
                ->label(__('financial_report.actions.download_excel'))
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->action(function () {
                    $report = $this->getReportData();
                    $locale = $this->getReportLanguage();
                    $period = $this->data['period'] ?? 'this_month';
                    $filename = __('financial_report.export.filename', [], $locale) . '-' . $period . '-' . now()->format('Y-m-d') . '.xlsx';
                    $currency = __('financial_report.currency', [], $locale);

                    return Excel::download(new FinancialReportExport($report, $locale, $currency), $filename);
                }),
        ];
    }
}
