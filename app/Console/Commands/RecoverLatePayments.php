<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Services\ThawaniService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RecoverLatePayments extends Command
{
    protected $signature = 'bookings:recover-late-payments
                            {--hours=24 : How far back to look — a Thawani session stays payable for 24 hours}
                            {--full : Re-check every candidate, not just the recently cancelled ones}';

    protected $description = 'Re-check Thawani for cancelled online bookings whose checkout session may have been paid after the pending-booking expiry, and confirm the ones that were.';

    // Cancelled this recently, a booking is re-checked on every run (the customer
    // is most likely still on the hosted page); older ones only once an hour, to
    // keep the number of Thawani API calls down.
    private const HOT_WINDOW_MINUTES = 120;

    public function handle(ThawaniService $thawani): int
    {
        $hours    = max(1, (int) $this->option('hours'));
        $hourlyRun = now()->minute < 5 || $this->option('full');

        $candidates = Booking::where('status', 'cancelled')
            ->where('payment_method', 'thawani')
            ->whereNotNull('payment_session_id')
            // Only bookings cancelled since cancellation_source was introduced;
            // older ones are never touched.
            ->whereNotNull('cancellation_source')
            ->where('payment_status', '!=', 'paid')
            ->where('created_at', '>=', now()->subHours($hours))
            ->when(!$hourlyRun, fn ($query) => $query->where('cancelled_at', '>=', now()->subMinutes(self::HOT_WINDOW_MINUTES)))
            ->pluck('id');

        $revived = 0;

        foreach ($candidates as $id) {
            $booking = Booking::find($id);

            if (!$booking) {
                continue;
            }

            try {
                $thawani->reconcileBooking($booking);
            } catch (Exception $e) {
                Log::warning('RecoverLatePayments: Thawani session check failed', [
                    'booking_id' => $id,
                    'error'      => $e->getMessage(),
                ]);

                continue;
            }

            if ($booking->status === 'confirmed') {
                $revived++;
            }
        }

        $this->info("Checked {$candidates->count()} cancelled booking(s); revived {$revived}.");

        return self::SUCCESS;
    }
}
