<?php

namespace App\Services;

use App\Models\PosShift;
use App\Models\PosTerminal;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * Owns the "a shift must be open before any sale, and a terminal can only
 * have one open shift at a time" rule — enforced here rather than left to
 * the UI, per §1.2 of the POS spec.
 */
class PosShiftService
{
    public function open(PosTerminal $terminal, User $cashier, float $openingFloat): PosShift
    {
        return DB::transaction(function () use ($terminal, $cashier, $openingFloat) {
            $alreadyOpen = PosShift::where('terminal_id', $terminal->id)
                ->where('status', 'open')
                ->lockForUpdate()
                ->exists();

            if ($alreadyOpen) {
                throw new Exception(__('This terminal already has an open shift. Close it before opening a new one.'));
            }

            return PosShift::create([
                'terminal_id' => $terminal->id,
                'user_id' => $cashier->id,
                'opening_float' => $openingFloat,
                'status' => 'open',
                'opened_at' => now(),
            ]);
        });
    }

    public function close(PosShift $shift, float $countedCash): PosShift
    {
        if (!$shift->isOpen()) {
            throw new Exception(__('This shift is already closed.'));
        }

        $shift->close($countedCash);

        return $shift->fresh();
    }

    public function varianceTolerance(): float
    {
        return (float) \App\Models\BookingSetting::get('pos_shift_variance_tolerance', 2);
    }

    public function isVarianceWithinTolerance(PosShift $shift): bool
    {
        return abs((float) $shift->variance) <= $this->varianceTolerance();
    }
}
