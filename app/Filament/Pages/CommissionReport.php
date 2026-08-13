<?php

namespace App\Filament\Pages;

use App\Exports\CommissionReportExport;
use App\Filament\Pages\Concerns\HasReportPeriodFilter;
use App\Models\BookingSetting;
use App\Models\Booking;
use App\Models\Event;
use App\Services\CommissionService;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
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

class CommissionReport extends Page implements HasForms
{
    use InteractsWithForms;
    use HasReportPeriodFilter;
    use HasPageShield;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calculator';

    protected static string|\UnitEnum|null $navigationGroup = 'Booking Management';

    protected static ?int $navigationSort = 14;

    protected string $view = 'filament.pages.commission-report';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        if (! (bool) BookingSetting::get('module_commission_enabled', true)) {
            return false;
        }

        // Re-implements HasPageShield::canAccess() rather than relying on it: a
        // canAccess() declared directly in this class always wins over the
        // trait's version (traits never win over the class body), so calling
        // parent::canAccess() here would skip the permission check entirely
        // and fall through to the base Page's "allow any panel user" default.
        $permission = static::getPagePermission();
        $user = \Filament\Facades\Filament::auth()?->user();

        return $permission && $user
            ? $user->can($permission)
            : parent::canAccess();
    }

    public function getTitle(): string
    {
        return __('commission_report.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('commission_report.navigation.label');
    }

    public function mount(): void
    {
        $this->form->fill([
            'period' => 'this_month',
            'event_id' => null,
            'date_from' => null,
            'date_to' => null,
            'language' => app()->getLocale(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(['default' => 1, 'sm' => 2, 'lg' => 5])
                    ->schema([
                        Select::make('period')
                            ->label(__('commission_report.filters.period'))
                            ->options([
                                'today' => __('commission_report.periods.today'),
                                'this_week' => __('commission_report.periods.this_week'),
                                'this_month' => __('commission_report.periods.this_month'),
                                'last_month' => __('commission_report.periods.last_month'),
                                'this_year' => __('commission_report.periods.this_year'),
                                'custom' => __('commission_report.periods.custom'),
                            ])
                            ->default('this_month')
                            ->selectablePlaceholder(false)
                            ->live(),

                        Select::make('event_id')
                            ->label(__('commission_report.filters.event'))
                            ->options(function () {
                                $locale = app()->getLocale();

                                return Event::query()
                                    ->orderByDesc('start_date')
                                    ->get()
                                    ->mapWithKeys(fn ($e) => [$e->id => $e->getTranslation('title', $locale)]);
                            })
                            ->placeholder(__('commission_report.filters.all_events'))
                            ->searchable()
                            ->live(),

                        DatePicker::make('date_from')
                            ->label(__('commission_report.filters.date_from'))
                            ->visible(fn (callable $get) => $get('period') === 'custom')
                            ->live(),

                        DatePicker::make('date_to')
                            ->label(__('commission_report.filters.date_to'))
                            ->visible(fn (callable $get) => $get('period') === 'custom')
                            ->live(),

                        Select::make('language')
                            ->label(__('commission_report.filters.language'))
                            ->options([
                                'en' => __('commission_report.languages.en'),
                                'ar' => __('commission_report.languages.ar'),
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

        $eventId = $this->data['event_id'] ?? null;
        $locale = $this->getReportLanguage();
        $service = app(CommissionService::class);

        $bookings = Booking::query()
            ->with('event')
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->whereBetween('created_at', [$from, $to])
            ->when($eventId, fn ($q) => $q->where('event_id', $eventId))
            ->get()
            ->filter(fn (Booking $booking) => $booking->event !== null);

        $rows = $bookings->map(function (Booking $booking) use ($service) {
            $resolved = $service->resolveAndCalculate(
                $booking->event,
                (float) $booking->total_price,
                $booking->created_at->toDateString(),
            );

            return [
                'booking' => $booking,
                'setting' => $resolved['setting'],
                'revenue' => (float) $booking->total_price,
                'commission' => $resolved['amount'],
            ];
        });

        $totalRevenue = (float) $rows->sum('revenue');
        $totalCommission = (float) $rows->sum('commission');
        $bookingCount = $rows->count();
        $coveredCount = $rows->filter(fn ($row) => $row['setting'] !== null)->count();
        $avgRate = $totalRevenue > 0 ? ($totalCommission / $totalRevenue) * 100 : 0;

        $byEvent = $rows->groupBy(fn ($row) => $row['booking']->event_id)
            ->map(function ($group) use ($service, $locale) {
                $event = $group->first()['booking']->event;
                $currentSetting = $service->resolveForEvent($event);

                return [
                    'label' => $event?->getTranslation('title', $locale) ?? '—',
                    'count' => $group->count(),
                    'revenue' => (float) $group->sum('revenue'),
                    'commission' => (float) $group->sum('commission'),
                    'rate' => $currentSetting?->formatted_rate ?? __('commission_report.no_rate', [], $locale),
                ];
            })
            ->sortByDesc('commission')
            ->values();

        $commissionTrend = $rows->groupBy(fn ($row) => $row['booking']->created_at->format('Y-m-d'))
            ->map(fn ($group, $date) => [
                'date' => $this->forceLtr($date),
                'revenue' => (float) $group->sum('revenue'),
                'commission' => (float) $group->sum('commission'),
            ])
            ->sortKeys()
            ->values();

        return [
            'from' => $from,
            'to' => $to,
            'totalRevenue' => $totalRevenue,
            'totalCommission' => $totalCommission,
            'bookingCount' => $bookingCount,
            'coveredCount' => $coveredCount,
            'uncoveredCount' => $bookingCount - $coveredCount,
            'avgRate' => $avgRate,
            'byEvent' => $byEvent,
            'commissionTrend' => $commissionTrend,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadPdf')
                ->label(__('commission_report.actions.download_pdf'))
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->action(function () {
                    $report = $this->getReportData();
                    $locale = $this->getReportLanguage();
                    $period = $this->data['period'] ?? 'this_month';
                    $filename = __('commission_report.export.filename', [], $locale) . '-' . $period . '-' . now()->format('Y-m-d') . '.pdf';

                    $pdf = Pdf::view('reports.commission-report-pdf', [
                        ...$report,
                        'locale' => $locale,
                    ])
                        ->headerView('reports.partials.pdf-header', [
                            'title' => __('commission_report.document_title', [], $locale),
                            'periodLabel' => __('commission_report.document.period', [
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
                        ->withBrowsershot(fn ($browsershot) => $browsershot->waitForFunction('window.pdfReady === true', null, 15000));

                    return Response::streamDownload(
                        fn () => print($pdf->generatePdfContent()),
                        $filename,
                        ['Content-Type' => 'application/pdf'],
                    );
                }),

            Action::make('downloadCsv')
                ->label(__('commission_report.actions.download_csv'))
                ->icon('heroicon-o-table-cells')
                ->color('gray')
                ->action(function () {
                    $report = $this->getReportData();
                    $locale = $this->getReportLanguage();
                    $period = $this->data['period'] ?? 'this_month';
                    $filename = __('commission_report.export.filename', [], $locale) . '-' . $period . '-' . now()->format('Y-m-d') . '.csv';
                    $currency = BookingSetting::get('currency_code') ?: __('commission_report.currency', [], $locale);

                    return Response::streamDownload(function () use ($report, $locale, $currency) {
                        $out = fopen('php://output', 'w');
                        fwrite($out, "\xEF\xBB\xBF");

                        fputcsv($out, [__('commission_report.document_title', [], $locale)]);
                        fputcsv($out, [__('commission_report.document.period', [
                            'from' => $report['from']->format('Y-m-d'),
                            'to' => $report['to']->format('Y-m-d'),
                        ], $locale)]);
                        fputcsv($out, [__('commission_report.document.generated_on', [], $locale) . ': ' . now()->format('Y-m-d H:i')]);
                        fputcsv($out, []);

                        fputcsv($out, [__('commission_report.stats.total_revenue', [], $locale), number_format($report['totalRevenue'], 3) . ' ' . $currency]);
                        fputcsv($out, [__('commission_report.stats.total_commission', [], $locale), number_format($report['totalCommission'], 3) . ' ' . $currency]);
                        fputcsv($out, [__('commission_report.stats.booking_count', [], $locale), $report['bookingCount']]);
                        fputcsv($out, [__('commission_report.stats.avg_rate', [], $locale), number_format($report['avgRate'], 2) . '%']);
                        fputcsv($out, []);

                        fputcsv($out, [__('commission_report.sections.by_event', [], $locale)]);
                        fputcsv($out, [
                            __('commission_report.columns.event', [], $locale),
                            __('commission_report.columns.count', [], $locale),
                            __('commission_report.columns.rate', [], $locale),
                            __('commission_report.columns.revenue', [], $locale),
                            __('commission_report.columns.commission', [], $locale),
                        ]);
                        foreach ($report['byEvent'] as $row) {
                            fputcsv($out, [
                                $row['label'],
                                $row['count'],
                                $row['rate'],
                                number_format($row['revenue'], 3) . ' ' . $currency,
                                number_format($row['commission'], 3) . ' ' . $currency,
                            ]);
                        }
                        fputcsv($out, []);

                        fputcsv($out, [__('commission_report.sections.trend', [], $locale)]);
                        fputcsv($out, [
                            __('commission_report.columns.date', [], $locale),
                            __('commission_report.columns.revenue', [], $locale),
                            __('commission_report.columns.commission', [], $locale),
                        ]);
                        foreach ($report['commissionTrend'] as $row) {
                            fputcsv($out, [
                                $row['date'],
                                number_format($row['revenue'], 3) . ' ' . $currency,
                                number_format($row['commission'], 3) . ' ' . $currency,
                            ]);
                        }

                        fclose($out);
                    }, $filename, ['Content-Type' => 'text/csv']);
                }),

            Action::make('downloadExcel')
                ->label(__('commission_report.actions.download_excel'))
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->action(function () {
                    $report = $this->getReportData();
                    $locale = $this->getReportLanguage();
                    $period = $this->data['period'] ?? 'this_month';
                    $filename = __('commission_report.export.filename', [], $locale) . '-' . $period . '-' . now()->format('Y-m-d') . '.xlsx';
                    $currency = BookingSetting::get('currency_code') ?: __('commission_report.currency', [], $locale);

                    return Excel::download(new CommissionReportExport($report, $locale, $currency), $filename);
                }),
        ];
    }
}
