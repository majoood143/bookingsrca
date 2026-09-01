<?php

namespace App\Filament\Pages;

use App\Models\PosCustomer;
use App\Models\PosDiscount;
use App\Models\PosProduct;
use App\Models\PosShift;
use App\Models\PosTax;
use App\Models\PosTerminal;
use App\Models\PosTransaction;
use App\Models\User;
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
 *
 * Product sales only — ticket sales go through the booking wizard/kiosk flow,
 * which drives seat allocation and attendee capture that this fast counter
 * screen isn't shaped for.
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

    // Cart state — kept as a plain array (not a model) so every add/remove is
    // an instant client-perceived update with no DB round trip until checkout.
    public array $productCart = []; // [product_id => {product_id, name, quantity, unit_price, tax_rate}]

    // Product-add sub-form
    public string $productSearch = '';
    public int $productQuantity = 1;

    // Customer
    public ?int $selectedCustomerId = null;
    public string $customerSearch = '';
    public bool $showCustomerModal = false;
    public string $newCustomerType = 'individual';
    public string $newCustomerName = '';
    public string $newCustomerBusinessName = '';
    public string $newCustomerMobile = '';
    public string $newCustomerAlternateContact = '';
    public string $newCustomerEmail = '';
    public bool $showCustomerMoreInfo = false;
    public string $newCustomerAddressLine1 = '';
    public string $newCustomerAddressLine2 = '';
    public string $newCustomerCity = '';
    public string $newCustomerState = '';
    public string $newCustomerCountry = '';
    public string $newCustomerZipCode = '';

    // Discount
    public string $discountCode = '';

    // Payment modal — one row per split-tender line, e.g. [['method' => 'cash', 'amount' => 10, 'reference' => '', 'card_holder_name' => '', 'card_last4' => '', 'card_type' => 'credit'], ...]
    public bool $showPaymentModal = false;
    public array $paymentRows = [];
    public string $sellNote = '';
    public string $staffNote = '';

    // Discount sub-modal — a cashier-typed type+amount that overrides discountCode
    // above when set (see cartTotals()). Opened from within the payment modal.
    public bool $showDiscountModal = false;
    public string $adHocDiscountType = 'percentage';
    public float $adHocDiscountValue = 0;
    public array $adHocDiscountBackup = [];

    // Order tax sub-modal
    public bool $showOrderTaxModal = false;
    public ?int $orderTaxId = null;
    public ?int $orderTaxIdBackup = null;

    // Shipping sub-modal
    public bool $showShippingModal = false;
    public string $shippingDetails = '';
    public string $shippingAddress = '';
    public float $shippingCharges = 0;
    public ?string $shippingStatus = null;
    public string $deliveredTo = '';
    public ?int $deliveryPersonId = null;
    public array $shippingBackup = [];

    public const SHIPPING_STATUSES = ['pending', 'shipped', 'delivered', 'cancelled'];

    // Recent transactions modal
    public bool $showTransactionsModal = false;
    public ?int $editingTransactionId = null;
    public string $editSellNote = '';
    public string $editStaffNote = '';
    public ?int $editCustomerId = null;
    public string $editCustomerSearch = '';
    public ?int $confirmVoidTransactionId = null;

    // Shift-close form state
    public float $countedCash = 0;

    public const PAYMENT_METHODS = [
        'cash' => ['pos.screen.cash', 'heroicon-o-banknotes'],
        'card' => ['pos.screen.card', 'heroicon-o-credit-card'],
        'mobile_wallet' => ['pos.screen.mobile_wallet', 'heroicon-o-device-phone-mobile'],
        'mobile_transfer' => ['pos.screen.mobile_transfer', 'heroicon-o-arrow-path'],
        'other' => ['pos.screen.other', 'heroicon-o-ellipsis-horizontal-circle'],
    ];

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
        $this->selectedCustomerId = PosCustomer::walkIn()->id;
    }

    public function currentTerminal(): ?PosTerminal
    {
        return $this->terminalId ? PosTerminal::find($this->terminalId) : null;
    }

    public function currentShift(): ?PosShift
    {
        return $this->currentTerminal()?->openShift;
    }

    public function searchProducts(): Collection
    {
        return PosProduct::where('is_active', true)
            ->when($this->productSearch, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->productSearch}%")
                        ->orWhere('name_ar', 'like', "%{$this->productSearch}%")
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

    // ── Customer ────────────────────────────────────────────────────────

    public function selectedCustomer(): ?PosCustomer
    {
        return $this->selectedCustomerId ? PosCustomer::find($this->selectedCustomerId) : null;
    }

    public function searchCustomers(): Collection
    {
        if (!$this->customerSearch) {
            return collect();
        }

        return PosCustomer::where(function ($q) {
            $q->where('name', 'like', "%{$this->customerSearch}%")
                ->orWhere('business_name', 'like', "%{$this->customerSearch}%")
                ->orWhere('mobile', 'like', "%{$this->customerSearch}%");
        })
            ->where('is_walk_in', false)
            ->orderBy('name')
            ->limit(10)
            ->get();
    }

    public function selectCustomer(int $customerId): void
    {
        $this->selectedCustomerId = $customerId;
        $this->customerSearch = '';
    }

    public function useWalkInCustomer(): void
    {
        $this->selectedCustomerId = PosCustomer::walkIn()->id;
        $this->customerSearch = '';
    }

    public function openCustomerModal(): void
    {
        $this->reset([
            'newCustomerType', 'newCustomerName', 'newCustomerBusinessName', 'newCustomerMobile',
            'newCustomerAlternateContact', 'newCustomerEmail', 'showCustomerMoreInfo',
            'newCustomerAddressLine1', 'newCustomerAddressLine2', 'newCustomerCity',
            'newCustomerState', 'newCustomerCountry', 'newCustomerZipCode',
        ]);
        $this->newCustomerType = 'individual';
        $this->showCustomerModal = true;
    }

    public function saveNewCustomer(): void
    {
        $data = $this->validate([
            'newCustomerType' => 'required|in:individual,business',
            'newCustomerName' => 'required|string|max:255',
            'newCustomerBusinessName' => 'nullable|string|max:255',
            'newCustomerMobile' => 'required|string|max:50',
            'newCustomerAlternateContact' => 'nullable|string|max:50',
            'newCustomerEmail' => 'nullable|email|max:255',
            'newCustomerAddressLine1' => 'nullable|string|max:255',
            'newCustomerAddressLine2' => 'nullable|string|max:255',
            'newCustomerCity' => 'nullable|string|max:255',
            'newCustomerState' => 'nullable|string|max:255',
            'newCustomerCountry' => 'nullable|string|max:255',
            'newCustomerZipCode' => 'nullable|string|max:50',
        ]);

        $customer = PosCustomer::create([
            'type' => $data['newCustomerType'],
            'name' => $data['newCustomerName'],
            'business_name' => $data['newCustomerBusinessName'] ?: null,
            'mobile' => $data['newCustomerMobile'],
            'alternate_contact' => $data['newCustomerAlternateContact'] ?: null,
            'email' => $data['newCustomerEmail'] ?: null,
            'address_line_1' => $data['newCustomerAddressLine1'] ?: null,
            'address_line_2' => $data['newCustomerAddressLine2'] ?: null,
            'city' => $data['newCustomerCity'] ?: null,
            'state' => $data['newCustomerState'] ?: null,
            'country' => $data['newCustomerCountry'] ?: null,
            'zip_code' => $data['newCustomerZipCode'] ?: null,
        ]);

        $this->selectedCustomerId = $customer->id;
        $this->showCustomerModal = false;

        Notification::make()->success()->title(__('pos.customer.notifications.created'))->send();
    }

    // ── Cart building ───────────────────────────────────────────────────

    public function addProductToCart(int $productId): void
    {
        $product = PosProduct::find($productId);

        if (!$product) {
            return;
        }

        if ($product->stock_quantity <= 0) {
            Notification::make()->danger()->title(__('pos.screen.out_of_stock'))->send();

            return;
        }

        if (isset($this->productCart[$productId])) {
            $this->productCart[$productId]['quantity'] += $this->productQuantity;
        } else {
            $this->productCart[$productId] = [
                'product_id' => $product->id,
                'name' => $product->display_name,
                'quantity' => $this->productQuantity,
                'unit_price' => (float) $product->price,
                'tax_rate' => (float) $product->tax_rate,
            ];
        }

        $this->productQuantity = 1;
    }

    // Fires on Enter in the product search box — the way every barcode-scanner
    // gun terminates a scan. An exact barcode/SKU match adds straight to the
    // cart and clears the box for the next scan; anything else is left as a
    // normal in-progress text search (no line added).
    public function scanBarcode(): void
    {
        $code = trim($this->productSearch);

        if ($code === '') {
            return;
        }

        $product = PosProduct::where('is_active', true)
            ->where(function ($q) use ($code) {
                $q->where('barcode', $code)->orWhere('sku', $code);
            })
            ->first();

        if ($product) {
            $this->addProductToCart($product->id);
            $this->productSearch = '';
        }
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

    // A manually-typed discount from the payment modal's Discount sub-modal
    // takes precedence over the code-based discount above whenever it's set.
    public function adHocDiscountAmount(float $subtotal): float
    {
        if ($this->adHocDiscountValue <= 0) {
            return 0;
        }

        return match ($this->adHocDiscountType) {
            'percentage' => round($subtotal * ($this->adHocDiscountValue / 100), 3),
            'fixed_amount' => min($subtotal, $this->adHocDiscountValue),
            default => 0,
        };
    }

    public function selectedOrderTax(): ?PosTax
    {
        return $this->orderTaxId ? PosTax::find($this->orderTaxId) : null;
    }

    public function orderTaxOptions(): Collection
    {
        return PosTax::where('is_active', true)->orderBy('name')->get();
    }

    public function deliveryPersonOptions(): Collection
    {
        return User::orderBy('name')->get();
    }

    public function cartTotals(): array
    {
        $subtotal = collect($this->productCart)->sum(fn ($l) => $l['quantity'] * $l['unit_price']);

        $tax = collect($this->productCart)->sum(fn ($l) => round($l['quantity'] * $l['unit_price'] * ($l['tax_rate'] / 100), 3));

        if ($this->adHocDiscountValue > 0) {
            $discountAmount = $this->adHocDiscountAmount($subtotal);
        } elseif ($discount = $this->appliedDiscount()) {
            $discountAmount = $discount->calculateDiscount($subtotal);
        } else {
            $discountAmount = 0;
        }

        $orderTaxAmount = ($orderTax = $this->selectedOrderTax()) ? $orderTax->calculateTax($subtotal) : 0;
        $shippingCharges = round((float) $this->shippingCharges, 3);

        $total = max(0, $subtotal - $discountAmount + $tax + $orderTaxAmount + $shippingCharges);

        return compact('subtotal', 'tax', 'discountAmount', 'orderTaxAmount', 'shippingCharges', 'total');
    }

    public function cartItemCount(): float
    {
        return (float) collect($this->productCart)->sum('quantity');
    }

    public function paymentMethodOptions(): array
    {
        return self::PAYMENT_METHODS;
    }

    // ── Payment modal ───────────────────────────────────────────────────

    protected function blankPaymentRow(float $amount): array
    {
        return [
            'method' => 'cash',
            'amount' => $amount,
            'reference' => '',
            'card_holder_name' => '',
            'card_last4' => '',
            'card_type' => 'credit',
        ];
    }

    public function openPaymentModal(): void
    {
        if (empty($this->productCart)) {
            Notification::make()->danger()->title(__('pos.screen.cart_empty'))->send();

            return;
        }

        $this->paymentRows = [$this->blankPaymentRow(round($this->cartTotals()['total'], 3))];
        $this->sellNote = '';
        $this->staffNote = '';
        $this->showPaymentModal = true;
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
    }

    public function addPaymentRow(): void
    {
        $this->paymentRows[] = $this->blankPaymentRow(max(0, $this->paymentBalance()));
    }

    public function removePaymentRow(int $index): void
    {
        if (count($this->paymentRows) <= 1) {
            return;
        }

        unset($this->paymentRows[$index]);
        $this->paymentRows = array_values($this->paymentRows);
    }

    public function paymentRowsTotal(): float
    {
        return round(array_sum(array_map(fn ($r) => (float) ($r['amount'] ?? 0), $this->paymentRows)), 3);
    }

    public function paymentBalance(): float
    {
        return max(0, round($this->cartTotals()['total'] - $this->paymentRowsTotal(), 3));
    }

    public function paymentChangeReturn(): float
    {
        return max(0, round($this->paymentRowsTotal() - $this->cartTotals()['total'], 3));
    }

    // ── Discount sub-modal ──────────────────────────────────────────────

    public function openDiscountModal(): void
    {
        $this->adHocDiscountBackup = ['type' => $this->adHocDiscountType, 'value' => $this->adHocDiscountValue];
        $this->showDiscountModal = true;
    }

    public function cancelDiscountModal(): void
    {
        $this->adHocDiscountType = $this->adHocDiscountBackup['type'] ?? 'percentage';
        $this->adHocDiscountValue = (float) ($this->adHocDiscountBackup['value'] ?? 0);
        $this->showDiscountModal = false;
    }

    public function saveDiscountModal(): void
    {
        $this->validate([
            'adHocDiscountType' => 'required|in:percentage,fixed_amount',
            'adHocDiscountValue' => 'required|numeric|min:0',
        ]);

        $this->showDiscountModal = false;
    }

    // ── Order tax sub-modal ─────────────────────────────────────────────

    public function openOrderTaxModal(): void
    {
        $this->orderTaxIdBackup = $this->orderTaxId;
        $this->showOrderTaxModal = true;
    }

    public function cancelOrderTaxModal(): void
    {
        $this->orderTaxId = $this->orderTaxIdBackup;
        $this->showOrderTaxModal = false;
    }

    public function saveOrderTax(): void
    {
        if (!$this->orderTaxId) {
            $this->orderTaxId = null;
        }

        $this->showOrderTaxModal = false;
    }

    // ── Shipping sub-modal ──────────────────────────────────────────────

    public function openShippingModal(): void
    {
        $this->shippingBackup = [
            'details' => $this->shippingDetails,
            'address' => $this->shippingAddress,
            'charges' => $this->shippingCharges,
            'status' => $this->shippingStatus,
            'delivered_to' => $this->deliveredTo,
            'delivery_person_id' => $this->deliveryPersonId,
        ];
        $this->showShippingModal = true;
    }

    public function cancelShippingModal(): void
    {
        $this->shippingDetails = $this->shippingBackup['details'] ?? '';
        $this->shippingAddress = $this->shippingBackup['address'] ?? '';
        $this->shippingCharges = (float) ($this->shippingBackup['charges'] ?? 0);
        $this->shippingStatus = $this->shippingBackup['status'] ?? null;
        $this->deliveredTo = $this->shippingBackup['delivered_to'] ?? '';
        $this->deliveryPersonId = $this->shippingBackup['delivery_person_id'] ?? null;
        $this->showShippingModal = false;
    }

    public function saveShippingModal(): void
    {
        if ($this->shippingStatus === '') {
            $this->shippingStatus = null;
        }

        if (!$this->deliveryPersonId) {
            $this->deliveryPersonId = null;
        }

        $this->validate([
            'shippingDetails' => 'required|string',
            'shippingCharges' => 'required|numeric|min:0',
            'shippingAddress' => 'nullable|string',
            'shippingStatus' => 'nullable|in:' . implode(',', self::SHIPPING_STATUSES),
            'deliveredTo' => 'nullable|string|max:255',
            'deliveryPersonId' => 'nullable|exists:users,id',
        ]);

        $this->showShippingModal = false;
    }

    // ── Checkout ────────────────────────────────────────────────────────

    public function finalizePayment(): void
    {
        try {
            $shift = $this->requireOpenShift();
            $total = round($this->cartTotals()['total'], 3);

            $rows = array_values(array_filter(
                $this->paymentRows,
                fn ($row) => (float) ($row['amount'] ?? 0) > 0
            ));

            if (empty($rows)) {
                throw new Exception(__('pos.screen.payment_required'));
            }

            foreach ($rows as $row) {
                if ($row['method'] === 'card' && trim((string) ($row['reference'] ?? '')) === '') {
                    throw new Exception(__('pos.screen.card_reference_required'));
                }
            }

            $paidTotal = round(array_sum(array_column($rows, 'amount')), 3);

            if ($paidTotal < $total) {
                throw new Exception(__('pos.screen.insufficient_payment'));
            }

            // Only cash tenders can generate change — a card/wallet/transfer row is
            // charged for exactly what it's asked for, never "overpaid" and refunded.
            $excess = round($paidTotal - $total, 3);

            if ($excess > 0) {
                $cashIndex = null;

                foreach ($rows as $i => $row) {
                    if ($row['method'] === 'cash') {
                        $cashIndex = $i;
                    }
                }

                if ($cashIndex === null || $rows[$cashIndex]['amount'] < $excess) {
                    throw new Exception(__('pos.screen.change_requires_cash'));
                }

                $rows[$cashIndex]['amount'] = round($rows[$cashIndex]['amount'] - $excess, 3);
            }

            $paymentLines = array_map(fn ($row) => [
                'method' => $row['method'],
                'amount' => round((float) $row['amount'], 3),
                'reference' => $row['reference'] !== '' ? $row['reference'] : null,
                'card_holder_name' => $row['method'] === 'card' && $row['card_holder_name'] !== '' ? $row['card_holder_name'] : null,
                'card_last4' => $row['method'] === 'card' && $row['card_last4'] !== '' ? $row['card_last4'] : null,
                'card_type' => $row['method'] === 'card' ? $row['card_type'] : null,
            ], $rows);

            $productLines = collect($this->productCart)->values()->map(fn ($l) => [
                'product_id' => $l['product_id'],
                'quantity' => $l['quantity'],
            ])->all();

            $adHocDiscount = $this->adHocDiscountValue > 0
                ? ['type' => $this->adHocDiscountType, 'value' => $this->adHocDiscountValue]
                : null;

            $shipping = ($this->shippingDetails !== '' || $this->shippingCharges > 0)
                ? [
                    'details' => $this->shippingDetails !== '' ? $this->shippingDetails : null,
                    'address' => $this->shippingAddress !== '' ? $this->shippingAddress : null,
                    'charges' => $this->shippingCharges,
                    'status' => $this->shippingStatus,
                    'delivered_to' => $this->deliveredTo !== '' ? $this->deliveredTo : null,
                    'delivery_person_id' => $this->deliveryPersonId,
                ]
                : null;

            $transaction = app(PosTransactionService::class)->createTransaction(
                shift: $shift,
                ticketLines: [],
                productLines: $productLines,
                paymentLines: $paymentLines,
                discount: $this->appliedDiscount(),
                notes: $this->sellNote !== '' ? $this->sellNote : null,
                customer: $this->selectedCustomer(),
                staffNote: $this->staffNote !== '' ? $this->staffNote : null,
                adHocDiscount: $adHocDiscount,
                orderTax: $this->selectedOrderTax(),
                shipping: $shipping,
            );

            $this->reset([
                'productCart', 'discountCode', 'paymentRows', 'sellNote', 'staffNote',
                'adHocDiscountType', 'adHocDiscountValue', 'orderTaxId',
                'shippingDetails', 'shippingAddress', 'shippingCharges', 'shippingStatus', 'deliveredTo', 'deliveryPersonId',
            ]);
            $this->showPaymentModal = false;
            $this->useWalkInCustomer();

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

    // ── Recent transactions (view/edit/print/void from the counter) ──────

    public function openTransactionsModal(): void
    {
        $this->showTransactionsModal = true;
    }

    public function recentTransactions(): Collection
    {
        return PosTransaction::where('terminal_id', $this->terminalId)
            ->with(['customer', 'shift'])
            ->latest()
            ->limit(25)
            ->get();
    }

    public function reprintTransaction(int $transactionId): void
    {
        $transaction = PosTransaction::find($transactionId);

        if (!$transaction) {
            return;
        }

        try {
            app(ThermalPrintService::class)->enqueuePosReceipt($transaction);
            Notification::make()->success()->title(__('pos.transaction.actions.reprint'))->send();
        } catch (Exception $e) {
            Notification::make()->danger()->title($e->getMessage())->send();
        }
    }

    public function editingTransaction(): ?PosTransaction
    {
        return $this->editingTransactionId ? PosTransaction::find($this->editingTransactionId) : null;
    }

    public function openEditTransaction(int $transactionId): void
    {
        $transaction = PosTransaction::find($transactionId);

        if (!$transaction) {
            return;
        }

        $this->editingTransactionId = $transaction->id;
        $this->editSellNote = (string) $transaction->notes;
        $this->editStaffNote = (string) $transaction->staff_note;
        $this->editCustomerId = $transaction->customer_id;
        $this->editCustomerSearch = '';
    }

    public function cancelEditTransaction(): void
    {
        $this->editingTransactionId = null;
    }

    public function searchEditCustomers(): Collection
    {
        if (!$this->editCustomerSearch) {
            return collect();
        }

        return PosCustomer::where(function ($q) {
            $q->where('name', 'like', "%{$this->editCustomerSearch}%")
                ->orWhere('business_name', 'like', "%{$this->editCustomerSearch}%")
                ->orWhere('mobile', 'like', "%{$this->editCustomerSearch}%");
        })
            ->where('is_walk_in', false)
            ->orderBy('name')
            ->limit(10)
            ->get();
    }

    public function selectEditCustomer(int $customerId): void
    {
        $this->editCustomerId = $customerId;
        $this->editCustomerSearch = '';
    }

    public function saveTransactionEdit(): void
    {
        $transaction = PosTransaction::find($this->editingTransactionId);

        if (!$transaction) {
            return;
        }

        $transaction->update([
            'notes' => $this->editSellNote !== '' ? $this->editSellNote : null,
            'staff_note' => $this->editStaffNote !== '' ? $this->editStaffNote : null,
            'customer_id' => $this->editCustomerId,
        ]);

        $this->editingTransactionId = null;

        Notification::make()->success()->title(__('pos.transaction.notifications.updated'))->send();
    }

    // A cashier may only void a same-shift sale while that shift is still
    // open; once it's closed (or it's a different terminal's shift) only a
    // manager override can undo it — same rule the admin resource enforces.
    public function canVoidTransaction(PosTransaction $transaction): bool
    {
        return $transaction->status === 'completed'
            && ($transaction->shift->isOpen() || (bool) auth()->user()?->hasAnyRole(['super_admin', 'pos_manager']));
    }

    public function confirmVoidTransaction(int $transactionId): void
    {
        $this->confirmVoidTransactionId = $transactionId;
    }

    public function cancelVoidTransaction(): void
    {
        $this->confirmVoidTransactionId = null;
    }

    public function voidRecentTransaction(): void
    {
        $transaction = PosTransaction::find($this->confirmVoidTransactionId);
        $this->confirmVoidTransactionId = null;

        if (!$transaction || !$this->canVoidTransaction($transaction)) {
            return;
        }

        try {
            app(PosTransactionService::class)->voidTransaction($transaction, __('pos.screen.voided_from_counter'));
            Notification::make()->success()->title(__('pos.transaction.notifications.voided'))->send();
        } catch (Exception $e) {
            Notification::make()->danger()->title($e->getMessage())->send();
        }
    }
}
