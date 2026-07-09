<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KioskCardTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'kiosk_card_id',
        'kiosk_id',
        'booking_id',
        'type',
        'amount',
        'balance_after',
        'recorded_by',
        'reference',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function kioskCard()
    {
        return $this->belongsTo(KioskCard::class);
    }

    public function kiosk()
    {
        return $this->belongsTo(Kiosk::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
