<?php

namespace App\Filament\Pages;

use App\Exports\PosTransactionsReportExport;
use App\Filament\Pages\Concerns\HasReportPeriodFilter;
use App\Models\BookingSetting;
use App\Models\PosPayment;
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

class PosTransactionsReport extends Page implements HasForms
{
    use InteractsWithForms;
    use HasReportPeriodFilter;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static string|\UnitEnum|null $navigationGroup = 'Point of Sale';

    protected static ?int $navigationSort = 48;

    protected string $view = 'filament.pages.pos-transactions-report';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return (bool) BookingSetting::get('module_pos_enabled', true)
            && (bool) auth()->user()?->hasAnyRole(['super_admin', 'pos_manager']);
    }

    public function getTitle(): string
    {
        return __('pos_reports.transactions.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('pos_reports.transactions.title');
    }

    public function mount(): void
    {
        $this->form->fill([
            'period' => 'this_month',
            'date_from' => null,
            'date_to' => null,
            'language' => app()->getLocale(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(['default' => 1, 'sm' => 2, 'lg' => 4])
                    ->schema([
                        Select::make('period')
                            ->label(__('pos_reports.transactions.filters.period'))
                            ->options(collect(['today', 'this_week', 'this_month', 'last_month', 'this_year', 'custom'])
                                ->mapWithKeys(fn ($p) => [$p => __('pos_reports.periods.' . $p)]))
                            ->default('this_month')
                            ->selectablePlaceholder(false)
                            ->live(),

                        DatePicker::make('date_from')
                            ->label(__('pos_reports.date_from'))
                            ->visible(fn (callable $get) => $get('period') === 'custom')
                            ->live(),

                        DatePicker::make('date_to')
                            ->label(__('pos_reports.date_to'))
                            ->visible(fn (callable $get) => $get('period') === 'custom')
                            ->live(),

                        Select::make('language')
                            ->label(__('pos_reports.transactions.filters.language'))
                            ->options([
                                'en' => __('pos_reports.languages.en'),
                                'ar' => __('pos_reports.languages.ar'),
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
        $locale = $this->getReportLanguage();

        $rows = PosPayment::query()
            ->with(['transaction', 'order'])
            ->whereBetween('created_at', [$from, $to])
            ->get()
            ->filter(fn (PosPayment $payment) => ($payment->transaction && $payment->transaction->status === 'completed')
                || ($payment->order && $payment->order->status === 'paid'))
            ->map(function (PosPayment $payment) use ($locale) {
                $isOnline = $payment->order_id !== null;

                return [
                    'method' => $payment->method,
                    'amount' => (float) $payment->amount,
                    'reference' => $payment->reference,
                    'source' => $isOnline
                        ? __('pos_reports.transactions.sources.online', [], $locale)
                        : __('pos_reports.transactions.sources.counter', [], $locale),
                    'status' => $isOnline ? $payment->order->status : $payment->transaction->status,
                    'date' => $payment->created_at->format('Y-m-d H:i'),
                ];
            })
            ->values();

        $totalAmount = (float) $rows->sum('amount');
        $transactionCount = $rows->count();

        $byMethod = $rows->groupBy('method')
            ->map(fn ($group, $label) => ['label' => ucfirst((string) $label), 'count' => $group->count(), 'amount' => (float) $group->sum('amount')])
            ->sortByDesc('amount')->values();

        $byStatus = $rows->groupBy('status')
            ->map(fn ($group, $label) => ['label' => ucfirst((string) $label), 'count' => $group->count(), 'amount' => (float) $group->sum('amount')])
            ->values();

        return [
            'from' => $from,
            'to' => $to,
            'totalAmount' => $totalAmount,
            'transactionCount' => $transactionCount,
            'byMethod' => $byMethod,
            'byStatus' => $byStatus,
            'list' => $rows->sortByDesc('date')->values(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadPdf')
                ->label(__('pos_reports.actions.download_pdf'))
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->action(function () {
                    $report = $this->getReportData();
                    $locale = $this->getReportLanguage();
                    $filename = __('pos_reports.transactions.filename', [], $locale) . '-' . now()->format('Y-m-d') . '.pdf';

                    $pdf = Pdf::view('reports.pos-transactions-report-pdf', [...$report, 'locale' => $locale])
                        ->headerView('reports.partials.pdf-header', [
                            'title' => __('pos_reports.transactions.document_title', [], $locale),
                            'periodLabel' => __('pos_reports.document.period', [
                                'from' => $report['from']->format('Y-m-d'),
                                'to' => $report['to']->format('Y-m-d'),
                            ], $locale),
                            'locale' => $locale,
                            'logoBase64' => $this->getLogoBase64(),
                        ])
                        ->footerView('reports.partials.pdf-footer', ['locale' => $locale])
                        ->format(Format::A4)
                        ->margins(38, 12, 18, 12, 'mm');

                    return Response::streamDownload(
                        fn () => print($pdf->generatePdfContent()),
                        $filename,
                        ['Content-Type' => 'application/pdf'],
                    );
                }),

            Action::make('downloadExcel')
                ->label(__('pos_reports.actions.download_excel'))
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->action(function () {
                    $report = $this->getReportData();
                    $locale = $this->getReportLanguage();
                    $filename = __('pos_reports.transactions.filename', [], $locale) . '-' . now()->format('Y-m-d') . '.xlsx';
                    $currency = BookingSetting::get('currency_code') ?: __('pos_reports.currency', [], $locale);

                    return Excel::download(new PosTransactionsReportExport($report, $locale, $currency), $filename);
                }),
        ];
    }
}
