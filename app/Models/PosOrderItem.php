<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosOrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'quantity',
        'unit_price',
        'line_total',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:3',
        'line_total' => 'decimal:3',
    ];

    public function order()
    {
        return $this->belongsTo(PosOrder::class, 'order_id');
    }

    public function product()
    {
        return $this->belongsTo(PosProduct::class, 'product_id');
    }
}
