<?php

namespace App\Models;

use App\Support\ClassifiesGatewayOutcome;
use Illuminate\Database\Eloquent\Model;

class PosOrderGatewayLog extends Model
{
    use ClassifiesGatewayOutcome;

    protected $fillable = [
        'pos_order_id',
        'gateway',
        'event',
        'status_code',
        'request_payload',
        'response_payload',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(PosOrder::class, 'pos_order_id');
    }

    // Record a gateway request/response pair for investigation purposes.
    // No-op if the order can't be resolved (nothing to attach the log to).
    public static function log(?PosOrder $order, string $gateway, string $event, mixed $request = null, mixed $response = null, ?int $statusCode = null): ?self
    {
        if (!$order) {
            return null;
        }

        return static::create([
            'pos_order_id' => $order->id,
            'gateway' => $gateway,
            'event' => $event,
            'status_code' => $statusCode,
            'request_payload' => $request,
            'response_payload' => $response,
        ]);
    }
}
