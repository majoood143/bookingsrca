<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGatewayLog extends Model
{
    protected $fillable = [
        'booking_id',
        'gateway',
        'event',
        'status_code',
        'request_payload',
        'response_payload',
    ];

    protected $casts = [
        'request_payload'  => 'array',
        'response_payload' => 'array',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Record a gateway request/response pair for investigation purposes.
     * No-op if the booking can't be resolved (nothing to attach the log to).
     */
    public static function log(?Booking $booking, string $gateway, string $event, mixed $request = null, mixed $response = null, ?int $statusCode = null): ?self
    {
        if (!$booking) {
            return null;
        }

        return static::create([
            'booking_id'        => $booking->id,
            'gateway'           => $gateway,
            'event'             => $event,
            'status_code'       => $statusCode,
            'request_payload'   => $request,
            'response_payload'  => $response,
        ]);
    }
}
