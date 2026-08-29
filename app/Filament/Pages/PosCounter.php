<?php

namespace App\Filament\Pages;

use App\Models\Event;
use App\Models\PosDiscount;
use App\Models\PosProduct;
use App\Models\PosShift;
use App\Models\PosTerminal;
use App\Models\TicketType;
use App\Models\TimeSlot;
use App\Services\Printing\ThermalPrintService;
use App\Services\PosShiftService;
use App\Services\PosTransactionService;
use Exception;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

/**
 * The cashier-facing sales-counter screen (§3.1 of the POS spec). Deliberately
 * NOT a Filament Resource form — a cashier under queue pressure needs large
 * tap targets and a live-updating cart, so this drives its own Blade view
 * with plain Livewire interactivity instead of the form-schema builder.
 * All money-moving logic lives in PosShiftService/PosTransactionService;
 * this class only collects cashier input and displays results.
 */
class PosCounter extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shopping-cart';

    protected static string|\UnitEnum|null $navigationGroup = 'Point of Sale';

    protected static ?int $navigationSort = 39;

    protected string $view = 'filament.pages.pos-counter';

    public ?int $terminalId = null;

    // Shift-open form state
    public float $openingFloat = 0;

    // Cart state — kept as plain arrays (not a model) so every add/remove is
    // an instant client-perceived update with no DB round trip until checkout.
    public array $ticketCart = []; // [key => {event_id, event_name, time_slot_id, slot_label, ticket_type_id, ticket_name, quantity, unit_price}]
    public array $productCart = []; // [product_id => {product_id, name, quantity, unit_price, tax_rate}]

    // Ticket-add sub-form
    public ?int $selectedEventId = null;
    public ?int $selectedTimeSlotId = null;
    public ?int $selectedTicketTypeId = null;
    public int $ticketQuantity = 1;

    // Product-add sub-form
    public string $productSearch = '';
    public int $productQuantity = 1;

    // Discount
    public string $discountCode = '';

    // Payment
    public string $paymentMethod = 'cash';
    public float $amountTendered = 0;

    // Shift-close form state
    public float $countedCash = 0;

    public static function canAccess(): bool
    {
        return (bool) \App\Models\BookingSetting::get('module_pos_enabled', true)
            && (bool) auth()->user()?->hasAnyRole(['super_admin', 'pos_manager', 'cashier']);
    }

    public function getTitle(): string
    {
        return __('pos.screen.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('pos.screen.title');
    }

    public function mount(): void
    {
        $terminal = PosTerminal::where('is_active', true)->orderBy('name')->first();
        $this->terminalId = $terminal?->id;
    }

    public function currentTerminal(): ?PosTerminal
    {
        return $this->terminalId ? PosTerminal::find($this->terminalId) : null;
    }

    public function currentShift(): ?PosShift
    {
        return $this->currentTerminal()?->openShift;
    }

    public function activeEvents(): Collection
    {
        return Event::where('status', 'published')->orderBy('start_date')->get();
    }

    public function availableTimeSlots(): Collection
    {
        if (!$this->selectedEventId) {
            return collect();
        }

        return TimeSlot::where('event_id', $this->selectedEventId)
            ->where('is_active', true)
            ->whereIn('booking_channel', ['both', 'online'])
            ->where('date', '>=', now()->format('Y-m-d'))
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();
    }

    public function availableTicketTypes(): Collection
    {
        if (!$this->selectedEventId) {
            return collect();
        }

        return TicketType::where('event_id', $this->selectedEventId)
            ->where('is_active', true)
            ->get();
    }

    public function searchProducts(): Collection
    {
        return PosProduct::where('is_active', true)
            ->when($this->productSearch, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->productSearch}%")
                        ->orWhere('barcode', $this->productSearch)
                        ->orWhere('sku', 'like', "%{$this->productSearch}%");
                });
            })
            ->orderBy('name')
            ->limit(20)
            ->get();
    }

    // ── Shift lifecycle ─────────────────────────────────────────────────

    public function openShift(): void
    {
        try {
            app(PosShiftService::class)->open($this->currentTerminal(), auth()->user(), $this->openingFloat);
            $this->openingFloat = 0;
        } catch (Exception $e) {
            Notification::make()->danger()->title($e->getMessage())->send();
        }
    }

    public function closeShift(): void
    {
        try {
            $shift = $this->requireOpenShift();
            $closed = app(PosShiftService::class)->close($shift, $this->countedCash);

            if (!app(PosShiftService::class)->isVarianceWithinTolerance($closed)) {
                Notification::make()->warning()->title(__('pos.screen.variance_warning'))->send();
            }

            $this->countedCash = 0;
        } catch (Exception $e) {
            Notification::make()->danger()->title($e->getMessage())->send();
        }
    }

    protected function requireOpenShift(): PosShift
    {
        $shift = $this->currentShift();

        if (!$shift) {
            throw new Exception(__('pos.screen.no_open_shift'));
        }

        return $shift;
    }

    // ── Cart building ───────────────────────────────────────────────────

    public function addTicketToCart(): void
    {
        if (!$this->selectedEventId || !$this->selectedTimeSlotId || !$this->selectedTicketTypeId || $this->ticketQuantity < 1) {
            return;
        }

        $event = Event::find($this->selectedEventId);
        $slot = TimeSlot::find($this->selectedTimeSlotId);
        $type = TicketType::find($this->selectedTicketTypeId);

        if (!$event || !$slot || !$type) {
            return;
        }

        $key = "{$event->id}:{$slot->id}:{$type->id}";

        if (isset($this->ticketCart[$key])) {
            $this->ticketCart[$key]['quantity'] += $this->ticketQuantity;
        } else {
            $this->ticketCart[$key] = [
                'event_id' => $event->id,
                'event_name' => $event->getTranslation('title', app()->getLocale()),
                'time_slot_id' => $slot->id,
                'slot_label' => $slot->getTimeRange() . ' — ' . $slot->date->format('Y-m-d'),
                'ticket_type_id' => $type->id,
                'ticket_name' => $type->getTranslation('name', app()->getLocale()),
                'quantity' => $this->ticketQuantity,
                'unit_price' => (float) $type->price,
            ];
        }

        $this->ticketQuantity = 1;
    }

    public function removeTicketLine(string $key): void
    {
        unset($this->ticketCart[$key]);
    }

    public function addProductToCart(int $productId): void
    {
        $product = PosProduct::find($productId);

        if (!$product) {
            return;
        }

        if (isset($this->productCart[$productId])) {
            $this->productCart[$productId]['quantity'] += $this->productQuantity;
        } else {
            $this->productCart[$productId] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'quantity' => $this->productQuantity,
                'unit_price' => (float) $product->price,
                'tax_rate' => (float) $product->tax_rate,
            ];
        }

        $this->productQuantity = 1;
    }

    public function removeProductLine(int $productId): void
    {
        unset($this->productCart[$productId]);
    }

    // ── Totals ──────────────────────────────────────────────────────────

    public function appliedDiscount(): ?PosDiscount
    {
        if (!$this->discountCode) {
            return null;
        }

        return PosDiscount::where('code', $this->discountCode)->first();
    }

    public function cartTotals(): array
    {
        $ticketSubtotal = collect($this->ticketCart)->sum(fn ($l) => $l['quantity'] * $l['unit_price']);
        $productSubtotal = collect($this->productCart)->sum(fn ($l) => $l['quantity'] * $l['unit_price']);
        $subtotal = $ticketSubtotal + $productSubtotal;

        $tax = collect($this->productCart)->sum(fn ($l) => round($l['quantity'] * $l['unit_price'] * ($l['tax_rate'] / 100), 3));

        $discountAmount = 0;
        if ($discount = $this->appliedDiscount()) {
            $discountAmount = $discount->calculateDiscount($subtotal);
        }

        $total = max(0, $subtotal - $discountAmount + $tax);

        return compact('subtotal', 'tax', 'discountAmount', 'total');
    }

    public function changeDue(): float
    {
        $total = $this->cartTotals()['total'];

        return max(0, $this->amountTendered - $total);
    }

    // ── Checkout ────────────────────────────────────────────────────────

    public function completeSale(): void
    {
        try {
            $shift = $this->requireOpenShift();
            $totals = $this->cartTotals();

            $ticketLines = collect($this->ticketCart)->values()->map(fn ($l) => [
                'event_id' => $l['event_id'],
                'time_slot_id' => $l['time_slot_id'],
                'ticket_type_id' => $l['ticket_type_id'],
                'quantity' => $l['quantity'],
            ])->all();

            $productLines = collect($this->productCart)->values()->map(fn ($l) => [
                'product_id' => $l['product_id'],
                'quantity' => $l['quantity'],
            ])->all();

            $paymentLines = [[
                'method' => $this->paymentMethod,
                'amount' => round($totals['total'], 3),
            ]];

            $transaction = app(PosTransactionService::class)->createTransaction(
                shift: $shift,
                ticketLines: $ticketLines,
                productLines: $productLines,
                paymentLines: $paymentLines,
                discount: $this->appliedDiscount(),
                notes: null,
            );

            $this->reset(['ticketCart', 'productCart', 'discountCode', 'amountTendered']);
            $this->paymentMethod = 'cash';

            Notification::make()->success()->title(__('pos.screen.sale_completed'))->send();

            // Printing is best-effort: the sale is already committed above, so a
            // disabled/unreachable printer must never roll back or block checkout.
            try {
                app(ThermalPrintService::class)->enqueuePosReceipt($transaction);
            } catch (Exception $e) {
                Notification::make()->warning()->title(__('pos.transaction.actions.reprint') . ': ' . $e->getMessage())->send();
            }
        } catch (Exception $e) {
            Notification::make()->danger()->title($e->getMessage())->send();
        }
    }
}
