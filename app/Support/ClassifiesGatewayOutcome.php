<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

/**
 * Classifies a gateway log row's response_payload into a gateway-agnostic
 * outcome (success/failed/pending/error/unknown), the same rules used by
 * PaymentGatewayLog for booking payments. Shared so both booking and POS
 * order gateway logs stay in sync when a gateway's payload shape changes.
 */
trait ClassifiesGatewayOutcome
{
    public const OUTCOMES = ['success', 'failed', 'pending', 'error', 'unknown'];

    public function getOutcomeAttribute(): string
    {
        $payload = $this->response_payload ?? [];

        return match ($this->gateway) {
            'thawani' => $this->thawaniOutcome($payload),
            'nbo' => $this->nboOutcome($payload),
            'ccavenue' => $this->ccavenueOutcome($payload),
            default => 'unknown',
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
            $status !== null => 'failed',
            default => 'unknown',
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

    public function scopeOutcome(Builder $query, string $outcome): Builder
    {
        return $query->whereRaw(static::outcomeCaseSql() . ' = ?', [$outcome]);
    }

    protected static function outcomeCaseSql(): string
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

    public function scopeSearchPayloads(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $query) use ($term) {
            $query->whereRaw('CAST(request_payload AS CHAR) LIKE ?', ["%{$term}%"])
                ->orWhereRaw('CAST(response_payload AS CHAR) LIKE ?', ["%{$term}%"]);
        });
    }
}
