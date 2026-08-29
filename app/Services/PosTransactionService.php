<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingAttendee;
use App\Models\PosDiscount;
use App\Models\PosProduct;
use App\Models\PosShift;
use App\Models\PosTransaction;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * Builds a single POS receipt (PosTransaction) that may mix ticket lines and
 * merchandise lines. Ticket lines go through SeatAllocationService — the same
 * locked code path the online/kiosk wizard uses — and merchandise lines
 * decrement PosProduct's independent stock. Neither branch ever touches the
 * other's inventory (see §4.1 of the POS spec): a ticket sale can't move
 * pos_products.stock_quantity, and a merchandise sale can't move
 * TicketType.quantity_sold / TimeSlot.current_bookings.
 */
class PosTransactionService
{
    public function __construct(protected SeatAllocationService $seatAllocation)
    {
    }

    /**
     * @param array<int,array{event_id:int,time_slot_id:int,ticket_type_id:int,quantity:int,attendee_names?:array}> $ticketLines
     *        One entry per distinct ticket type in the cart — a single ticket type requested twice
     *        should be merged by the caller into one line with the combined quantity.
     * @param array<int,array{product_id:int,quantity:int}> $productLines
     * @param array<int,array{method:string,amount:float,reference?:string}> $paymentLines
     */
    public function createTransaction(
        PosShift $shift,
        array $ticketLines,
        array $productLines,
        array $paymentLines,
        ?PosDiscount $discount = null,
        ?string $notes = null,
    ): PosTransaction {
        if (!$shift->isOpen()) {
            throw new Exception(__('This shift is not open — sales cannot be recorded against it.'));
        }

        if (empty($ticketLines) && empty($productLines)) {
            throw new Exception(__('Add at least one ticket or product to the cart.'));
        }

        return DB::transaction(function () use ($shift, $ticketLines, $productLines, $paymentLines, $discount, $notes) {
            $items = [];

            foreach ($ticketLines as $line) {
                // One row per attendee (each is exactly one seat) — merge, don't nest.
                array_push($items, ...$this->sellTicketLine($shift, $line));
            }

            foreach ($productLines as $line) {
                $items[] = $this->sellProductLine($shift, $line);
            }

            $subtotal = array_sum(array_column($items, 'line_total'));
            $taxTotal = array_sum(array_column($items, 'tax_amount'));

            $discountTotal = 0;
            if ($discount) {
                if (!$discount->isUsable()) {
                    throw new Exception(__('This discount is no longer usable.'));
                }
                $discountTotal = $discount->calculateDiscount($subtotal);
            }

            $grandTotal = round(max(0, $subtotal - $discountTotal + $taxTotal), 3);
            $paidTotal = round(array_sum(array_column($paymentLines, 'amount')), 3);

            if ($paidTotal !== $grandTotal) {
                throw new Exception(__('Payment total :paid does not match the transaction total :total.', [
                    'paid' => $paidTotal,
                    'total' => $grandTotal,
                ]));
            }

            $transaction = PosTransaction::create([
                'shift_id' => $shift->id,
                'terminal_id' => $shift->terminal_id,
                'cashier_id' => $shift->user_id,
                'discount_id' => $discount?->id,
                'transaction_number' => PosTransaction::nextTransactionNumber($shift->terminal_id),
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'tax_total' => $taxTotal,
                'grand_total' => $grandTotal,
                'status' => 'completed',
                'notes' => $notes,
            ]);

            foreach ($items as $item) {
                $transaction->items()->create([
                    'itemable_type' => $item['itemable_type'],
                    'itemable_id' => $item['itemable_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_amount' => $item['tax_amount'],
                    'line_total' => $item['line_total'],
                ]);
            }

            foreach ($paymentLines as $payment) {
                $transaction->payments()->create([
                    'method' => $payment['method'],
                    'amount' => $payment['amount'],
                    'reference' => $payment['reference'] ?? null,
                ]);
            }

            $discount?->increment('used_count');

            return $transaction;
        });
    }

    /**
     * Same-shift, cashier-initiated void. Releases every ticket seat and
     * merchandise unit back to their (separate) inventories.
     */
    public function voidTransaction(PosTransaction $transaction, string $reason): PosTransaction
    {
        if ($transaction->status !== 'completed') {
            throw new Exception(__('Only a completed transaction can be voided.'));
        }

        return DB::transaction(function () use ($transaction, $reason) {
            $this->releaseTransactionInventory($transaction, $reason);

            $transaction->update([
                'status' => 'voided',
                'notes' => trim(($transaction->notes ? $transaction->notes . "\n" : '') . '[Void] ' . $reason),
            ]);

            return $transaction->fresh();
        });
    }

    /**
     * Manager/admin-only, post-finalization refund — always requires a reason.
     * v1 refunds the whole transaction (every line); per-line partial refunds
     * are a future refinement, not required for the double-sell/stock-separation
     * safety properties this module exists to guarantee.
     */
    public function refundTransaction(PosTransaction $transaction, string $reason): PosTransaction
    {
        if ($transaction->status !== 'completed') {
            throw new Exception(__('Only a completed transaction can be refunded.'));
        }

        return DB::transaction(function () use ($transaction, $reason) {
            $this->releaseTransactionInventory($transaction, $reason);

            $transaction->update([
                'status' => 'refunded',
                'notes' => trim(($transaction->notes ? $transaction->notes . "\n" : '') . '[Refund] ' . $reason),
            ]);

            return $transaction->fresh();
        });
    }

    // Releases capacity/stock for every line in the transaction. Ticket lines
    // are grouped by their parent Booking first — Booking::refund() releases
    // capacity at the whole-booking quantity, so calling it once per
    // BookingAttendee item (rather than once per Booking) would release the
    // same seats multiple times.
    protected function releaseTransactionInventory(PosTransaction $transaction, string $reason): void
    {
        $bookingIds = $transaction->items()
            ->where('itemable_type', BookingAttendee::class)
            ->with('itemable')
            ->get()
            ->pluck('itemable.booking_id')
            ->filter()
            ->unique();

        foreach ($bookingIds as $bookingId) {
            $booking = Booking::find($bookingId);

            if ($booking && $booking->status === 'confirmed') {
                $booking->refund($reason);
            }
        }

        foreach ($transaction->items()->where('itemable_type', PosProduct::class)->with('itemable')->get() as $item) {
            $item->itemable?->applyStockMovement('return', $item->quantity, 'txn:' . $transaction->transaction_number);
        }
    }

    /**
     * Locks and prices exactly one ticket type via the shared seat-allocation
     * core, creates its Booking + BookingAttendee rows, and confirms it
     * immediately (POS payment is in-person and instant — no "pay at
     * counter later" hold window the way the kiosk offers one).
     *
     * @return array{itemable_type:string,itemable_id:int,quantity:int,unit_price:float,tax_amount:float,line_total:float}
     */
    protected function sellTicketLine(PosShift $shift, array $line): array
    {
        $quantity = (int) $line['quantity'];

        $allocation = $this->seatAllocation->lockAndPrice(
            timeSlotId: $line['time_slot_id'],
            eventId: $line['event_id'],
            ticketQuantities: [$line['ticket_type_id'] => $quantity],
        );

        $ticketType = $allocation['ticketTypes'][$line['ticket_type_id']];

        $booking = Booking::create([
            'event_id' => $line['event_id'],
            'time_slot_id' => $line['time_slot_id'],
            'ticket_type_id' => $ticketType->id,
            'event_date' => $allocation['timeSlot']->date,
            'quantity' => $quantity,
            'ticket_price' => $allocation['ticketPrice'],
            'services_price' => 0,
            'total_price' => $allocation['ticketPrice'],
            'source' => 'pos',
            'created_by' => $shift->user_id,
            'locale' => app()->getLocale(),
            'status' => 'pending',
            'payment_status' => 'paid',
        ]);

        $names = $line['attendee_names'] ?? [];

        for ($i = 0; $i < $quantity; $i++) {
            BookingAttendee::create([
                'booking_id' => $booking->id,
                'ticket_type_id' => $ticketType->id,
                'ticket_price' => $ticketType->price,
                'first_name' => $names[$i]['first_name'] ?? __('Guest'),
                'last_name' => $names[$i]['last_name'] ?? '',
                'email' => $names[$i]['email'] ?? '',
            ]);
        }

        $booking->confirm();

        // One transaction item per attendee (each is exactly one seat), so a
        // single receipt line always maps 1:1 to a redeemable ticket.
        $attendeeIds = $booking->attendees()->pluck('id');

        return $attendeeIds->map(fn ($id) => [
            'itemable_type' => BookingAttendee::class,
            'itemable_id' => $id,
            'quantity' => 1,
            'unit_price' => (float) $ticketType->price,
            'tax_amount' => 0.0,
            'line_total' => (float) $ticketType->price,
        ])->all();
    }

    /**
     * @return array{itemable_type:string,itemable_id:int,quantity:int,unit_price:float,tax_amount:float,line_total:float}
     */
    protected function sellProductLine(PosShift $shift, array $line): array
    {
        $quantity = (int) $line['quantity'];

        $product = PosProduct::where('id', $line['product_id'])->lockForUpdate()->first();

        if (!$product || !$product->isAvailable($quantity)) {
            throw new Exception(__('Product :name is not available in the requested quantity.', [
                'name' => $product?->name ?? '',
            ]));
        }

        $lineSubtotal = round((float) $product->price * $quantity, 3);
        $taxAmount = round($lineSubtotal * ((float) $product->tax_rate / 100), 3);

        $product->applyStockMovement('sale', -$quantity, null, $shift->user_id);

        return [
            'itemable_type' => PosProduct::class,
            'itemable_id' => $product->id,
            'quantity' => $quantity,
            'unit_price' => (float) $product->price,
            'tax_amount' => $taxAmount,
            // Tax-exclusive, like the ticket lines above — tax_total is added
            // once at the transaction level (subtotal + tax = grand_total).
            'line_total' => $lineSubtotal,
        ];
    }
}
