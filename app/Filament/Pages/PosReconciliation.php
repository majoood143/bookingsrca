<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\HasReportPeriodFilter;
use App\Models\BookingAttendee;
use App\Models\PosPayment;
use App\Models\PosProduct;
use App\Models\PosTransaction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class PosReconciliation extends Page implements HasForms
{
    use InteractsWithForms;
    use HasReportPeriodFilter;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calculator';

    protected static string|\UnitEnum|null $navigationGroup = 'Point of Sale';

    protected static ?int $navigationSort = 45;

    protected string $view = 'filament.pages.pos-reconciliation';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return (bool) \App\Models\BookingSetting::get('module_pos_enabled', true)
            && (bool) auth()->user()?->hasAnyRole(['super_admin', 'pos_manager']);
    }

    public function getTitle(): string
    {
        return __('pos.reconciliation.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('pos.reconciliation.title');
    }

    public function mount(): void
    {
        $this->form->fill([
            'period' => 'today',
            'date_from' => null,
            'date_to' => null,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(['default' => 1, 'sm' => 3])
                    ->schema([
                        Select::make('period')
                            ->label(__('Period'))
                            ->options([
                                'today' => __('Today'),
                                'this_week' => __('This Week'),
                                'this_month' => __('This Month'),
                                'last_month' => __('Last Month'),
                                'custom' => __('Custom'),
                            ])
                            ->default('today')
                            ->selectablePlaceholder(false)
                            ->live(),

                        DatePicker::make('date_from')
                            ->label(__('From'))
                            ->visible(fn ($get) => $get('period') === 'custom')
                            ->live(),

                        DatePicker::make('date_to')
                            ->label(__('To'))
                            ->visible(fn ($get) => $get('period') === 'custom')
                            ->live(),
                    ]),
            ])
            ->statePath('data');
    }

    public function getReportData(): array
    {
        [$from, $to] = $this->getDateRange();

        $transactions = PosTransaction::with(['terminal', 'cashier', 'payments', 'discount'])
            ->whereBetween('created_at', [$from, $to])
            ->get();

        $completedOrRefunded = $transactions->whereIn('status', ['completed', 'refunded', 'partially_refunded']);

        $totalSales = (float) $completedOrRefunded->where('status', 'completed')->sum('grand_total');
        $totalDiscount = (float) $completedOrRefunded->where('status', 'completed')->sum('discount_total');
        $totalTax = (float) $completedOrRefunded->where('status', 'completed')->sum('tax_total');
        $refundedTotal = (float) $transactions->where('status', 'refunded')->sum('grand_total');
        $voidedCount = $transactions->where('status', 'voided')->count();

        $byMethod = PosPayment::whereIn('transaction_id', $completedOrRefunded->pluck('id'))
            ->get()
            ->groupBy('method')
            ->map(fn ($group, $method) => [
                'label' => __('pos.screen.' . $method) ?: $method,
                'amount' => (float) $group->sum('amount'),
            ])
            ->sortByDesc('amount')
            ->values();

        $byTerminal = $completedOrRefunded->where('status', 'completed')
            ->groupBy('terminal_id')
            ->map(fn ($group) => [
                'label' => $group->first()->terminal?->name ?? '—',
                'count' => $group->count(),
                'amount' => (float) $group->sum('grand_total'),
            ])
            ->sortByDesc('amount')
            ->values();

        $byCashier = $completedOrRefunded->where('status', 'completed')
            ->groupBy('cashier_id')
            ->map(fn ($group) => [
                'label' => $group->first()->cashier?->name ?? '—',
                'count' => $group->count(),
                'amount' => (float) $group->sum('grand_total'),
            ])
            ->sortByDesc('amount')
            ->values();

        $transactionIds = $completedOrRefunded->where('status', 'completed')->pluck('id');

        $ticketRevenue = (float) \App\Models\PosTransactionItem::whereIn('transaction_id', $transactionIds)
            ->where('itemable_type', BookingAttendee::class)
            ->sum('line_total');

        $merchandiseRevenue = (float) \App\Models\PosTransactionItem::whereIn('transaction_id', $transactionIds)
            ->where('itemable_type', PosProduct::class)
            ->sum('line_total');

        $discountsGiven = $completedOrRefunded->where('status', 'completed')
            ->whereNotNull('discount_id')
            ->groupBy('discount_id')
            ->map(fn ($group) => [
                'label' => $group->first()->discount?->code ?? __('(manager discretion)'),
                'count' => $group->count(),
                'amount' => (float) $group->sum('discount_total'),
            ])
            ->sortByDesc('amount')
            ->values();

        return [
            'from' => $from,
            'to' => $to,
            'totalSales' => $totalSales,
            'totalDiscount' => $totalDiscount,
            'totalTax' => $totalTax,
            'refundedTotal' => $refundedTotal,
            'voidedCount' => $voidedCount,
            'transactionCount' => $completedOrRefunded->where('status', 'completed')->count(),
            'byMethod' => $byMethod,
            'byTerminal' => $byTerminal,
            'byCashier' => $byCashier,
            'ticketRevenue' => $ticketRevenue,
            'merchandiseRevenue' => $merchandiseRevenue,
            'discountsGiven' => $discountsGiven,
        ];
    }
}
