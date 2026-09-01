<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PosTransaction extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'shift_id',
        'terminal_id',
        'cashier_id',
        'discount_id',
        'ad_hoc_discount_type',
        'ad_hoc_discount_value',
        'customer_id',
        'transaction_number',
        'subtotal',
        'discount_total',
        'tax_total',
        'order_tax_id',
        'order_tax_total',
        'shipping_details',
        'shipping_address',
        'shipping_charges',
        'shipping_status',
        'delivered_to',
        'delivery_person_id',
        'grand_total',
        'status',
        'notes',
        'staff_note',
    ];

    protected $casts = [
        'subtotal' => 'decimal:3',
        'discount_total' => 'decimal:3',
        'ad_hoc_discount_value' => 'decimal:3',
        'tax_total' => 'decimal:3',
        'order_tax_total' => 'decimal:3',
        'shipping_charges' => 'decimal:3',
        'grand_total' => 'decimal:3',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'subtotal', 'discount_total', 'tax_total', 'order_tax_total', 'shipping_charges', 'grand_total', 'notes'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Sequential per terminal, e.g. "T3-000042", so receipt reprints/reconciliation
    // can reference a short human-readable number instead of the raw id.
    public static function nextTransactionNumber(int $terminalId): string
    {
        $count = static::where('terminal_id', $terminalId)->count() + 1;

        return sprintf('T%d-%06d', $terminalId, $count);
    }

    public function shift()
    {
        return $this->belongsTo(PosShift::class, 'shift_id');
    }

    public function terminal()
    {
        return $this->belongsTo(PosTerminal::class, 'terminal_id');
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function discount()
    {
        return $this->belongsTo(PosDiscount::class, 'discount_id');
    }

    public function orderTax()
    {
        return $this->belongsTo(PosTax::class, 'order_tax_id');
    }

    public function deliveryPerson()
    {
        return $this->belongsTo(User::class, 'delivery_person_id');
    }

    public function customer()
    {
        return $this->belongsTo(PosCustomer::class, 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(PosTransactionItem::class, 'transaction_id');
    }

    public function payments()
    {
        return $this->hasMany(PosPayment::class, 'transaction_id');
    }

    public function ticketItems()
    {
        return $this->items()->where('itemable_type', BookingAttendee::class);
    }

    public function merchandiseItems()
    {
        return $this->items()->where('itemable_type', PosProduct::class);
    }

    // Completed/refunded transactions carry stock and financial history —
    // it must be voided first (which already reverses those effects) before
    // it can be removed from the record.
    public function canBeDeleted(): bool
    {
        return $this->status === 'voided';
    }
}
