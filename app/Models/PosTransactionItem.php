<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosTransactionItem extends Model
{
    protected $fillable = [
        'transaction_id',
        'itemable_type',
        'itemable_id',
        'quantity',
        'unit_price',
        'discount_amount',
        'tax_amount',
        'line_total',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:3',
        'discount_amount' => 'decimal:3',
        'tax_amount' => 'decimal:3',
        'line_total' => 'decimal:3',
    ];

    public function transaction()
    {
        return $this->belongsTo(PosTransaction::class, 'transaction_id');
    }

    public function itemable()
    {
        return $this->morphTo();
    }

    public function isTicket(): bool
    {
        return $this->itemable_type === BookingAttendee::class;
    }

    public function isMerchandise(): bool
    {
        return $this->itemable_type === PosProduct::class;
    }
}
