<?php

namespace App\Models;

use App\Mail\PosOrderReceipt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PosOrder extends Model
{
    use LogsActivity;

    protected $fillable = [
        'order_number',
        'customer_id',
        'status',
        'fulfillment_status',
        'contact_name',
        'contact_email',
        'contact_mobile',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'country',
        'zip_code',
        'subtotal',
        'discount_total',
        'tax_total',
        'grand_total',
        'payment_method',
        'payment_reference',
        'paid_at',
        'customer_note',
        'staff_note',
    ];

    protected $casts = [
        'subtotal' => 'decimal:3',
        'discount_total' => 'decimal:3',
        'tax_total' => 'decimal:3',
        'grand_total' => 'decimal:3',
        'paid_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'fulfillment_status', 'grand_total', 'payment_method', 'staff_note'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Sequential, e.g. "SO-000123" ("Store Order"), independent of the
    // terminal-scoped numbering used by in-person pos_transactions.
    public static function nextOrderNumber(): string
    {
        $count = static::count() + 1;

        return sprintf('SO-%06d', $count);
    }

    public function customer()
    {
        return $this->belongsTo(PosCustomer::class, 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(PosOrderItem::class, 'order_id');
    }

    public function payments()
    {
        return $this->hasMany(PosPayment::class, 'order_id');
    }

    public function gatewayLogs()
    {
        return $this->hasMany(PosOrderGatewayLog::class);
    }

    public function reviews()
    {
        return $this->hasMany(PosProductReview::class, 'order_id');
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    // Paid orders keep stock movements, payments, and a mailed receipt tied
    // to them — only unsettled orders are safe to remove entirely.
    public function canBeDeleted(): bool
    {
        return !$this->isPaid();
    }

    // Called from the gateway callback once payment is verified: settles the
    // order, decrements stock, records the payment, and emails the receipt.
    public function markPaid(string $gateway, ?string $reference, ?float $amount = null): void
    {
        if ($this->isPaid()) {
            return;
        }

        $this->update([
            'status' => 'paid',
            'fulfillment_status' => 'preparing',
            'payment_method' => $gateway,
            'payment_reference' => $reference,
            'paid_at' => now(),
        ]);

        foreach ($this->items as $item) {
            $item->product?->applyStockMovement('sale', -$item->quantity, $this->order_number);
        }

        $this->payments()->create([
            'method' => $gateway,
            'amount' => $amount ?? $this->grand_total,
            'reference' => $reference,
        ]);

        DB::afterCommit(fn () => Mail::to($this->contact_email)->send(new PosOrderReceipt($this)));
    }
}
