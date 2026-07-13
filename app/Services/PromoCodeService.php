<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\PromoCode;
use App\Models\PromoCodeUsage;
use Illuminate\Support\Facades\DB;

class PromoCodeService
{
    /**
     * Validate a promo code against a specific event and subtotal.
     *
     * @return array{valid: bool, message: string, promo_code_id?: int, discount_amount?: float, discount_type?: string, discount_value?: float}
     */
    public function validate(string $code, int $eventId, float $subtotal): array
    {
        $promoCode = PromoCode::where('code', strtoupper(trim($code)))->first();

        if (!$promoCode) {
            return ['valid' => false, 'message' => __('promo.invalid_code')];
        }

        if (!$promoCode->is_active) {
            return ['valid' => false, 'message' => __('promo.code_inactive')];
        }

        if ($promoCode->valid_from && now()->lt($promoCode->valid_from)) {
            return ['valid' => false, 'message' => __('promo.code_not_started')];
        }

        if ($promoCode->valid_until && now()->gt($promoCode->valid_until)) {
            return ['valid' => false, 'message' => __('promo.code_expired')];
        }

        if ($promoCode->max_uses !== null && $promoCode->used_count >= $promoCode->max_uses) {
            return ['valid' => false, 'message' => __('promo.code_used_up')];
        }

        // Event-scoped code: reject if it doesn't match the booked event
        if ($promoCode->event_id !== null && $promoCode->event_id !== $eventId) {
            return ['valid' => false, 'message' => __('promo.invalid_code')];
        }

        if ($subtotal <= 0) {
            return ['valid' => false, 'message' => __('promo.invalid_code')];
        }

        $discountAmount = $promoCode->calculateDiscount($subtotal);

        return [
            'valid'           => true,
            'promo_code_id'   => $promoCode->id,
            'discount_amount' => $discountAmount,
            'discount_type'   => $promoCode->discount_type,
            'discount_value'  => (float) $promoCode->discount_value,
            'message'         => __('promo.code_applied', [
                'amount' => number_format($discountAmount, 3),
            ]),
        ];
    }

    /**
     * Record the usage of a promo code after a successful booking confirmation.
     *
     * Increments used_count atomically inside a transaction. No-op if the
     * booking has no promo code attached, or it was already recorded.
     */
    public function recordUsage(Booking $booking): void
    {
        if (!$booking->promo_code_id) {
            return;
        }

        DB::transaction(function () use ($booking) {
            $alreadyRecorded = PromoCodeUsage::where('promo_code_id', $booking->promo_code_id)
                ->where('booking_id', $booking->id)
                ->exists();

            if ($alreadyRecorded) {
                return;
            }

            PromoCodeUsage::create([
                'promo_code_id'   => $booking->promo_code_id,
                'booking_id'      => $booking->id,
                'discount_amount' => (float) ($booking->discount_amount ?? 0),
                'used_at'         => now(),
            ]);

            PromoCode::where('id', $booking->promo_code_id)->increment('used_count');
        });
    }
}
