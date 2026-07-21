<?php

namespace App\Filament\Pages;

use App\Enums\ExpensePaymentStatus;
use App\Enums\ExpenseStatus;
use App\Enums\ExpenseType;
use App\Exports\ExpensesReportExport;
use App\Filament\Pages\Concerns\HasReportPeriodFilter;
use App\Models\BookingSetting;
use App\Models\Event;
use App\Models\Expense;
use App\Models\ExpenseCategory;
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

class ExpensesReport extends Page implements HasForms
{
    use InteractsWithForms;
    use HasReportPeriodFilter;
    use HasPageShield;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-receipt-percent';

    protected static string|\UnitEnum|null $navigationGroup = 'Booking Management';

    protected static ?int $navigationSort = 13;

    protected string $view = 'filament.pages.expenses-report';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return parent::canAccess() && (bool) BookingSetting::get('module_expenses_enabled', true);
    }

    public function getTitle(): string
    {
        return __('expenses_report.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('expenses_report.navigation.label');
    }

    public function mount(): void
    {
        $this->form->fill([
            'period' => 'this_month',
            'event_id' => null,
            'category_id' => null,
            'expense_type' => null,
            'payment_status' => null,
            'date_from' => null,
            'date_to' => null,
            'language' => app()->getLocale(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(['default' => 1, 'sm' => 2, 'lg' => 7])
                    ->schema([
                        Select::make('period')
                            ->label(__('expenses_report.filters.period'))
                            ->options([
                                'today' => __('expenses_report.periods.today'),
                                'this_week' => __('expenses_report.periods.this_week'),
                                'this_month' => __('expenses_report.periods.this_month'),
                                'last_month' => __('expenses_report.periods.last_month'),
                                'this_year' => __('expenses_report.periods.this_year'),
                                'custom' => __('expenses_report.periods.custom'),
                            ])
                            ->default('this_month')
                            ->selectablePlaceholder(false)
                            ->live(),

                        Select::make('event_id')
                            ->label(__('expenses_report.filters.event'))
                            ->options(function () {
                                $locale = app()->getLocale();

                                return Event::query()
                                    ->orderByDesc('start_date')
                                    ->get()
                                    ->mapWithKeys(fn ($e) => [$e->id => $e->getTranslation('title', $locale)]);
                            })
                            ->placeholder(__('expenses_report.filters.all_events'))
                            ->searchable()
                            ->live(),

                        Select::make('category_id')
                            ->label(__('expenses_report.filters.category'))
                            ->options(function () {
                                $locale = app()->getLocale();

                                return ExpenseCategory::query()
                                    ->ordered()
                                    ->get()
                                    ->mapWithKeys(fn ($c) => [$c->id => $c->getTranslation('name', $locale)]);
                            })
                            ->placeholder(__('expenses_report.filters.all_categories'))
                            ->searchable()
                            ->live(),

                        Select::make('expense_type')
                            ->label(__('expenses_report.filters.type'))
                            ->options(ExpenseType::toArray())
                            ->placeholder(__('expenses_report.filters.all_types'))
                            ->live(),

                        Select::make('payment_status')
                            ->label(__('expenses_report.filters.payment_status'))
                            ->options(ExpensePaymentStatus::toArray())
                            ->placeholder(__('expenses_report.filters.all_statuses'))
                            ->live(),

                        DatePicker::make('date_from')
                            ->label(__('expenses_report.filters.date_from'))
                            ->visible(fn (callable $get) => $get('period') === 'custom')
                            ->live(),

                        DatePicker::make('date_to')
                            ->label(__('expenses_report.filters.date_to'))
                            ->visible(fn (callable $get) => $get('period') === 'custom')
                            ->live(),

                        Select::make('language')
                            ->label(__('expenses_report.filters.language'))
                            ->options([
                                'en' => __('expenses_report.languages.en'),
                                'ar' => __('expenses_report.languages.ar'),
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
        $categoryId = $this->data['category_id'] ?? null;
        $expenseType = $this->data['expense_type'] ?? null;
        $paymentStatus = $this->data['payment_status'] ?? null;
        $locale = $this->getReportLanguage();

        $expenses = Expense::query()
            ->with(['event', 'category'])
            ->where('status', ExpenseStatus::Approved->value)
            ->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()])
            ->when($eventId, fn ($q) => $q->where('event_id', $eventId))
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->when($expenseType, fn ($q) => $q->where('expense_type', $expenseType))
            ->when($paymentStatus, fn ($q) => $q->where('payment_status', $paymentStatus))
            ->get();

        $totalAmount = (float) $expenses->sum('total_amount');
        $totalTax = (float) $expenses->sum('tax_amount');
        $totalPending = (float) $expenses->whereIn('payment_status', [
            ExpensePaymentStatus::Pending,
            ExpensePaymentStatus::Partial,
        ])->sum('total_amount');
        $totalPaid = (float) $expenses->where('payment_status', ExpensePaymentStatus::Paid)->sum('total_amount');
        $expenseCount = $expenses->count();
        $avgExpense = $expenseCount > 0 ? $totalAmount / $expenseCount : 0;

        $byCategory = $expenses->groupBy('category_id')->map(function ($group) use ($locale) {
            $first = $group->first();

            return [
                'label' => $first->category?->getTranslation('name', $locale) ?? __('expenses_report.uncategorized', [], $locale),
                'count' => $group->count(),
                'amount' => (float) $group->sum('total_amount'),
            ];
        })->sortByDesc('amount')->values();

        $byType = $expenses->groupBy('expense_type')->map(function ($group) use ($locale) {
            $first = $group->first();

            return [
                'label' => $first->expense_type->getLabel(),
                'count' => $group->count(),
                'amount' => (float) $group->sum('total_amount'),
            ];
        })->sortByDesc('amount')->values();

        $byEvent = $expenses->whereNotNull('event_id')->groupBy('event_id')->map(function ($group) use ($locale) {
            $first = $group->first();

            return [
                'label' => $first->event?->getTranslation('title', $locale) ?? '—',
                'count' => $group->count(),
                'amount' => (float) $group->sum('total_amount'),
            ];
        })->sortByDesc('amount')->values();

        $byPaymentStatus = $expenses->groupBy('payment_status')->map(function ($group) {
            $first = $group->first();

            return [
                'label' => $first->payment_status->getLabel(),
                'amount' => (float) $group->sum('total_amount'),
            ];
        })->sortByDesc('amount')->values();

        $expenseTrend = $expenses->groupBy(fn ($e) => $e->expense_date->format('Y-m-d'))
            ->map(fn ($group, $date) => [
                'date' => $this->forceLtr($date),
                'amount' => (float) $group->sum('total_amount'),
            ])
            ->sortKeys()
            ->values();

        return [
            'from' => $from,
            'to' => $to,
            'totalAmount' => $totalAmount,
            'totalTax' => $totalTax,
            'totalPending' => $totalPending,
            'totalPaid' => $totalPaid,
            'expenseCount' => $expenseCount,
            'avgExpense' => $avgExpense,
            'byCategory' => $byCategory,
            'byType' => $byType,
            'byEvent' => $byEvent,
            'byPaymentStatus' => $byPaymentStatus,
            'expenseTrend' => $expenseTrend,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadPdf')
                ->label(__('expenses_report.actions.download_pdf'))
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->action(function () {
                    $report = $this->getReportData();
                    $locale = $this->getReportLanguage();
                    $period = $this->data['period'] ?? 'this_month';
                    $filename = __('expenses_report.export.filename', [], $locale) . '-' . $period . '-' . now()->format('Y-m-d') . '.pdf';

                    $pdf = Pdf::view('reports.expenses-report-pdf', [
                        ...$report,
                        'locale' => $locale,
                    ])
                        ->headerView('reports.partials.pdf-header', [
                            'title' => __('expenses_report.document_title', [], $locale),
                            'periodLabel' => __('expenses_report.document.period', [
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
                ->label(__('expenses_report.actions.download_csv'))
                ->icon('heroicon-o-table-cells')
                ->color('gray')
                ->action(function () {
                    $report = $this->getReportData();
                    $locale = $this->getReportLanguage();
                    $period = $this->data['period'] ?? 'this_month';
                    $filename = __('expenses_report.export.filename', [], $locale) . '-' . $period . '-' . now()->format('Y-m-d') . '.csv';
                    $currency = BookingSetting::get('currency_code') ?: __('expenses_report.currency', [], $locale);

                    return Response::streamDownload(function () use ($report, $locale, $currency) {
                        $out = fopen('php://output', 'w');
                        fwrite($out, "\xEF\xBB\xBF");

                        fputcsv($out, [__('expenses_report.document_title', [], $locale)]);
                        fputcsv($out, [__('expenses_report.document.period', [
                            'from' => $report['from']->format('Y-m-d'),
                            'to' => $report['to']->format('Y-m-d'),
                        ], $locale)]);
                        fputcsv($out, [__('expenses_report.document.generated_on', [], $locale) . ': ' . now()->format('Y-m-d H:i')]);
                        fputcsv($out, []);

                        fputcsv($out, [__('expenses_report.stats.total_amount', [], $locale), number_format($report['totalAmount'], 3) . ' ' . $currency]);
                        fputcsv($out, [__('expenses_report.stats.total_tax', [], $locale), number_format($report['totalTax'], 3) . ' ' . $currency]);
                        fputcsv($out, [__('expenses_report.stats.total_paid', [], $locale), number_format($report['totalPaid'], 3) . ' ' . $currency]);
                        fputcsv($out, [__('expenses_report.stats.total_pending', [], $locale), number_format($report['totalPending'], 3) . ' ' . $currency]);
                        fputcsv($out, [__('expenses_report.stats.expense_count', [], $locale), $report['expenseCount']]);
                        fputcsv($out, [__('expenses_report.stats.avg_expense', [], $locale), number_format($report['avgExpense'], 3) . ' ' . $currency]);
                        fputcsv($out, []);

                        fputcsv($out, [__('expenses_report.sections.by_category', [], $locale)]);
                        fputcsv($out, [__('expenses_report.columns.category', [], $locale), __('expenses_report.columns.count', [], $locale), __('expenses_report.columns.amount', [], $locale)]);
                        foreach ($report['byCategory'] as $row) {
                            fputcsv($out, [$row['label'], $row['count'], number_format($row['amount'], 3) . ' ' . $currency]);
                        }
                        fputcsv($out, []);

                        fputcsv($out, [__('expenses_report.sections.by_type', [], $locale)]);
                        fputcsv($out, [__('expenses_report.columns.type', [], $locale), __('expenses_report.columns.count', [], $locale), __('expenses_report.columns.amount', [], $locale)]);
                        foreach ($report['byType'] as $row) {
                            fputcsv($out, [$row['label'], $row['count'], number_format($row['amount'], 3) . ' ' . $currency]);
                        }
                        fputcsv($out, []);

                        fputcsv($out, [__('expenses_report.sections.by_event', [], $locale)]);
                        fputcsv($out, [__('expenses_report.columns.event', [], $locale), __('expenses_report.columns.count', [], $locale), __('expenses_report.columns.amount', [], $locale)]);
                        foreach ($report['byEvent'] as $row) {
                            fputcsv($out, [$row['label'], $row['count'], number_format($row['amount'], 3) . ' ' . $currency]);
                        }
                        fputcsv($out, []);

                        fputcsv($out, [__('expenses_report.sections.by_payment_status', [], $locale)]);
                        fputcsv($out, [__('expenses_report.columns.payment_status', [], $locale), __('expenses_report.columns.amount', [], $locale)]);
                        foreach ($report['byPaymentStatus'] as $row) {
                            fputcsv($out, [$row['label'], number_format($row['amount'], 3) . ' ' . $currency]);
                        }
                        fputcsv($out, []);

                        fputcsv($out, [__('expenses_report.sections.expense_trend', [], $locale)]);
                        fputcsv($out, [__('expenses_report.columns.date', [], $locale), __('expenses_report.columns.amount', [], $locale)]);
                        foreach ($report['expenseTrend'] as $row) {
                            fputcsv($out, [$row['date'], number_format($row['amount'], 3) . ' ' . $currency]);
                        }

                        fclose($out);
                    }, $filename, ['Content-Type' => 'text/csv']);
                }),

            Action::make('downloadExcel')
                ->label(__('expenses_report.actions.download_excel'))
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->action(function () {
                    $report = $this->getReportData();
                    $locale = $this->getReportLanguage();
                    $period = $this->data['period'] ?? 'this_month';
                    $filename = __('expenses_report.export.filename', [], $locale) . '-' . $period . '-' . now()->format('Y-m-d') . '.xlsx';
                    $currency = BookingSetting::get('currency_code') ?: __('expenses_report.currency', [], $locale);

                    return Excel::download(new ExpensesReportExport($report, $locale, $currency), $filename);
                }),
        ];
    }
}
