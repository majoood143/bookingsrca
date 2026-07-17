<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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

    /**
     * Outcomes an entry's response_payload can be classified into, used for
     * the "result" column/filter since each gateway shapes its payload
     * differently (see outcome() below for the per-gateway rules).
     */
    public const OUTCOMES = ['success', 'failed', 'pending', 'error', 'unknown'];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Classify this entry's response_payload into a gateway-agnostic result,
     * mirroring the exact checks each *CallbackController/ *Service uses to
     * decide whether a payment succeeded (see PaymentGatewayLog::log() call
     * sites). Kept in sync with scopeOutcome()'s SQL below.
     */
    public function getOutcomeAttribute(): string
    {
        $payload = $this->response_payload ?? [];

        return match ($this->gateway) {
            'thawani'  => $this->thawaniOutcome($payload),
            'nbo'      => $this->nboOutcome($payload),
            'ccavenue' => $this->ccavenueOutcome($payload),
            default    => 'unknown',
        };
    }

    private function thawaniOutcome(array $payload): string
    {
        if (array_key_exists('error', $payload)) {
            return 'error';
        }

        $status = $payload['data']['payment_status'] ?? null;

        return match (true) {
            $status === 'paid' => 'success',
            $status !== null   => 'failed',
            default            => 'unknown',
        };
    }

    private function nboOutcome(array $payload): string
    {
        $error = $payload['error'] ?? null;

        if ($error !== null && $error !== '' && $error !== '0') {
            return $error === 'no_transaction_data' ? 'error' : 'failed';
        }

        $result = $payload['result'] ?? null;

        return match (true) {
            in_array($result, ['CAPTURED', 'APPROVED'], true) => 'success',
            $result !== null => 'failed',
            default => 'unknown',
        };
    }

    private function ccavenueOutcome(array $payload): string
    {
        if (array_key_exists('error', $payload)) {
            return 'error';
        }

        $status = strtolower((string) ($payload['order_status'] ?? ''));

        return match (true) {
            $status === 'success' => 'success',
            in_array($status, ['failure', 'aborted', 'invalid', 'unsuccessful'], true) => 'failed',
            $status === 'initiated' => 'pending',
            default => 'unknown',
        };
    }

    /**
     * Filter by the gateway-agnostic outcome computed in getOutcomeAttribute(),
     * expressed as a single SQL CASE expression so it can run in the
     * database rather than pulling every row into PHP. The branch order and
     * conditions must stay identical to the accessor above.
     */
    public function scopeOutcome(Builder $query, string $outcome): Builder
    {
        return $query->whereRaw(self::outcomeCaseSql() . ' = ?', [$outcome]);
    }

    private static function outcomeCaseSql(): string
    {
        $rp = 'response_payload';

        return "(CASE
            WHEN gateway = 'thawani' AND JSON_EXTRACT({$rp}, '$.error') IS NOT NULL THEN 'error'
            WHEN gateway = 'thawani' AND JSON_UNQUOTE(JSON_EXTRACT({$rp}, '$.data.payment_status')) = 'paid' THEN 'success'
            WHEN gateway = 'thawani' AND JSON_EXTRACT({$rp}, '$.data.payment_status') IS NOT NULL THEN 'failed'
            WHEN gateway = 'thawani' THEN 'unknown'
            WHEN gateway = 'nbo' AND JSON_UNQUOTE(JSON_EXTRACT({$rp}, '$.error')) = 'no_transaction_data' THEN 'error'
            WHEN gateway = 'nbo' AND JSON_EXTRACT({$rp}, '$.error') IS NOT NULL AND JSON_UNQUOTE(JSON_EXTRACT({$rp}, '$.error')) NOT IN ('0', '') THEN 'failed'
            WHEN gateway = 'nbo' AND JSON_UNQUOTE(JSON_EXTRACT({$rp}, '$.result')) IN ('CAPTURED', 'APPROVED') THEN 'success'
            WHEN gateway = 'nbo' AND JSON_EXTRACT({$rp}, '$.result') IS NOT NULL THEN 'failed'
            WHEN gateway = 'nbo' THEN 'unknown'
            WHEN gateway = 'ccavenue' AND JSON_EXTRACT({$rp}, '$.error') IS NOT NULL THEN 'error'
            WHEN gateway = 'ccavenue' AND LOWER(JSON_UNQUOTE(JSON_EXTRACT({$rp}, '$.order_status'))) = 'success' THEN 'success'
            WHEN gateway = 'ccavenue' AND LOWER(JSON_UNQUOTE(JSON_EXTRACT({$rp}, '$.order_status'))) = 'initiated' THEN 'pending'
            WHEN gateway = 'ccavenue' AND LOWER(JSON_UNQUOTE(JSON_EXTRACT({$rp}, '$.order_status'))) IN ('failure', 'aborted', 'invalid', 'unsuccessful') THEN 'failed'
            ELSE 'unknown'
        END)";
    }

    /**
     * Free-text search across both JSON payload columns, used by the
     * Filament resource's "search in payloads" filter so admins can look up
     * a raw value (order id, tracking id, error message, status, ...)
     * without knowing which gateway or JSON path it lives under.
     */
    public function scopeSearchPayloads(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $query) use ($term) {
            $query->whereRaw('CAST(request_payload AS CHAR) LIKE ?', ["%{$term}%"])
                ->orWhereRaw('CAST(response_payload AS CHAR) LIKE ?', ["%{$term}%"]);
        });
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
