<?php

namespace App\Filament\Pages;

use App\Exports\PosSalesReportExport;
use App\Filament\Pages\Concerns\HasReportPeriodFilter;
use App\Models\BookingSetting;
use App\Models\PosOrderItem;
use App\Models\PosTransactionItem;
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

class PosSalesReport extends Page implements HasForms
{
    use InteractsWithForms;
    use HasReportPeriodFilter;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static string|\UnitEnum|null $navigationGroup = 'Point of Sale';

    protected static ?int $navigationSort = 47;

    protected string $view = 'filament.pages.pos-sales-report';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return (bool) BookingSetting::get('module_pos_enabled', true)
            && (bool) auth()->user()?->hasAnyRole(['super_admin', 'pos_manager']);
    }

    public function getTitle(): string
    {
        return __('pos_reports.sales.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('pos_reports.sales.title');
    }

    public function mount(): void
    {
        $this->form->fill([
            'period' => 'this_month',
            'channel' => null,
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
                            ->label(__('pos_reports.sales.filters.period'))
                            ->options(collect(['today', 'this_week', 'this_month', 'last_month', 'this_year', 'custom'])
                                ->mapWithKeys(fn ($p) => [$p => __('pos_reports.periods.' . $p)]))
                            ->default('this_month')
                            ->selectablePlaceholder(false)
                            ->live(),

                        Select::make('channel')
                            ->label(__('pos_reports.sales.filters.channel'))
                            ->options([
                                'counter' => __('pos_reports.sales.channels.counter'),
                                'online' => __('pos_reports.sales.channels.online'),
                            ])
                            ->placeholder(__('pos_reports.sales.filters.all_channels'))
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
                            ->label(__('pos_reports.sales.filters.language'))
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
        $channel = $this->data['channel'] ?? null;

        $rows = collect();

        if ($channel !== 'online') {
            $rows = $rows->concat(
                PosTransactionItem::query()
                    ->with(['itemable.category', 'itemable.brand', 'transaction'])
                    ->where('itemable_type', \App\Models\PosProduct::class)
                    ->whereHas('transaction', fn ($q) => $q->where('status', 'completed')->whereBetween('created_at', [$from, $to]))
                    ->get()
                    ->map(fn (PosTransactionItem $item) => [
                        'product' => $item->itemable?->display_name ?? '—',
                        'category' => $item->itemable?->category?->full_name ?? '—',
                        'brand' => $item->itemable?->brand?->display_name ?? '—',
                        'channel' => 'counter',
                        'quantity' => $item->quantity,
                        'revenue' => (float) $item->line_total,
                        'date' => $item->transaction->created_at->format('Y-m-d'),
                    ])
            );
        }

        if ($channel !== 'counter') {
            $rows = $rows->concat(
                PosOrderItem::query()
                    ->with(['product.category', 'product.brand', 'order'])
                    ->whereHas('order', fn ($q) => $q->where('status', 'paid')->whereBetween('paid_at', [$from, $to]))
                    ->get()
                    ->map(fn (PosOrderItem $item) => [
                        'product' => $item->product?->display_name ?? $item->product_name,
                        'category' => $item->product?->category?->full_name ?? '—',
                        'brand' => $item->product?->brand?->display_name ?? '—',
                        'channel' => 'online',
                        'quantity' => $item->quantity,
                        'revenue' => (float) $item->line_total,
                        'date' => $item->order->paid_at->format('Y-m-d'),
                    ])
            );
        }

        $totalRevenue = (float) $rows->sum('revenue');
        $totalQuantity = (int) $rows->sum('quantity');
        $orderCount = $rows->count();

        $byProduct = $rows->groupBy('product')
            ->map(fn ($group, $label) => ['label' => $label, 'quantity' => $group->sum('quantity'), 'revenue' => (float) $group->sum('revenue')])
            ->sortByDesc('revenue')->values();

        $byCategory = $rows->groupBy('category')
            ->map(fn ($group, $label) => ['label' => $label, 'quantity' => $group->sum('quantity'), 'revenue' => (float) $group->sum('revenue')])
            ->sortByDesc('revenue')->values();

        $byBrand = $rows->groupBy('brand')
            ->map(fn ($group, $label) => ['label' => $label, 'quantity' => $group->sum('quantity'), 'revenue' => (float) $group->sum('revenue')])
            ->sortByDesc('revenue')->values();

        $byChannel = $rows->groupBy('channel')
            ->map(fn ($group, $label) => [
                'label' => __('pos_reports.sales.channels.' . $label),
                'quantity' => $group->sum('quantity'),
                'revenue' => (float) $group->sum('revenue'),
            ])->values();

        $trend = $rows->groupBy('date')
            ->map(fn ($group, $date) => ['date' => $this->forceLtr($date), 'quantity' => $group->sum('quantity'), 'revenue' => (float) $group->sum('revenue')])
            ->sortKeys()->values();

        return [
            'from' => $from,
            'to' => $to,
            'totalRevenue' => $totalRevenue,
            'totalQuantity' => $totalQuantity,
            'orderCount' => $orderCount,
            'avgOrderValue' => $orderCount > 0 ? $totalRevenue / $orderCount : 0,
            'byProduct' => $byProduct,
            'byCategory' => $byCategory,
            'byBrand' => $byBrand,
            'byChannel' => $byChannel,
            'trend' => $trend,
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
                    $filename = __('pos_reports.sales.filename', [], $locale) . '-' . now()->format('Y-m-d') . '.pdf';

                    $pdf = Pdf::view('reports.pos-sales-report-pdf', [...$report, 'locale' => $locale])
                        ->headerView('reports.partials.pdf-header', [
                            'title' => __('pos_reports.sales.document_title', [], $locale),
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
                    $filename = __('pos_reports.sales.filename', [], $locale) . '-' . now()->format('Y-m-d') . '.xlsx';
                    $currency = BookingSetting::get('currency_code') ?: __('pos_reports.currency', [], $locale);

                    return Excel::download(new PosSalesReportExport($report, $locale, $currency), $filename);
                }),
        ];
    }
}
