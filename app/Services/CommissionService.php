<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CommissionSetting;
use App\Models\Event;

/**
 * Resolves and calculates the platform commission applicable to an event.
 *
 * Commission resolution priority (highest to lowest):
 *   1. Event-specific commission setting
 *   2. Global (platform-wide) commission setting
 *
 * This is a reporting-only calculation: it does not deduct or store
 * anything against bookings/payments, it only informs the Commission
 * Report and the effective-rate display on Events.
 */
class CommissionService
{
    /**
     * Resolve the applicable commission setting for a given event, as of $date.
     */
    public function resolveForEvent(Event $event, $date = null): ?CommissionSetting
    {
        $date ??= now()->toDateString();

        $eventCommission = CommissionSetting::query()
            ->active()
            ->forEvent($event->id)
            ->effectiveOn($date)
            ->orderByDesc('created_at')
            ->first();

        if ($eventCommission) {
            return $eventCommission;
        }

        return CommissionSetting::query()
            ->active()
            ->global()
            ->effectiveOn($date)
            ->orderByDesc('created_at')
            ->first();
    }

    /**
     * Calculate the commission amount for a base amount under a given setting.
     */
    public function calculate(float $baseAmount, CommissionSetting $setting): float
    {
        return $setting->calculateAmount($baseAmount);
    }

    /**
     * Resolve the applicable setting for an event and calculate the commission
     * on a base amount in one call.
     *
     * @return array{setting: ?CommissionSetting, amount: float}
     */
    public function resolveAndCalculate(Event $event, float $baseAmount, $date = null): array
    {
        $setting = $this->resolveForEvent($event, $date);

        return [
            'setting' => $setting,
            'amount' => $setting ? $this->calculate($baseAmount, $setting) : 0.0,
        ];
    }
}
