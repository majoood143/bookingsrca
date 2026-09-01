<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosPayment extends Model
{
    protected $fillable = [
        'transaction_id',
        'order_id',
        'method',
        'amount',
        'reference',
        'card_holder_name',
        'card_last4',
        'card_type',
    ];

    protected $casts = [
        'amount' => 'decimal:3',
    ];

    public function transaction()
    {
        return $this->belongsTo(PosTransaction::class, 'transaction_id');
    }

    public function order()
    {
        return $this->belongsTo(PosOrder::class, 'order_id');
    }
}
