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
        'transaction_number',
        'subtotal',
        'discount_total',
        'tax_total',
        'grand_total',
        'status',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:3',
        'discount_total' => 'decimal:3',
        'tax_total' => 'decimal:3',
        'grand_total' => 'decimal:3',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'subtotal', 'discount_total', 'tax_total', 'grand_total', 'notes'])
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
}
