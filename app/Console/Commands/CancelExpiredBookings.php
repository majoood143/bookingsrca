<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\BookingSetting;
use App\Models\Kiosk;
use App\Services\ThawaniService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CancelExpiredBookings extends Command
{
    protected $signature = 'bookings:cancel-expired';

    protected $description = 'Cancel pending bookings that have sat awaiting payment past the configured timeout, releasing the slot/ticket capacity they were holding.';

    public function handle(ThawaniService $thawani): int
    {
        $globalExpiryMinutes = (int) BookingSetting::get('pending_booking_expiry_minutes', 15);

        // A kiosk with its own booking rules may use a shorter (or longer)
        // expiry than the global default — widen the initial scan to the
        // shortest window in play so those bookings aren't missed, then each
        // candidate is re-checked below against its own kiosk's expiry.
        $shortestPossibleExpiry = min($globalExpiryMinutes, Kiosk::where('override_pending_booking_expiry_minutes', true)
            ->whereNotNull('pending_booking_expiry_minutes')
            ->min('pending_booking_expiry_minutes') ?? $globalExpiryMinutes);

        $staleBookingIds = Booking::where('status', 'pending')
            ->where('created_at', '<', now()->subMinutes($shortestPossibleExpiry))
            ->pluck('id');

        $cancelled = 0;

        foreach ($staleBookingIds as $id) {
            // Resync with Thawani before expiring, outside the row lock below —
            // a booking whose browser callback and webhook both failed to land
            // (dropped connection, worker down) would otherwise sit "paid" at
            // Thawani but get wrongly cancelled here once it goes stale.
            // Expiring is not final either: the Thawani session stays payable for
            // 24 hours, and bookings:recover-late-payments revives a booking whose
            // session is paid after we gave up on it.
            $booking = Booking::find($id);

            if ($booking && $booking->status === 'pending' && $booking->payment_method === 'thawani' && $booking->payment_session_id) {
                try {
                    if ($thawani->reconcileBooking($booking) === 'paid') {
                        continue;
                    }
                } catch (Exception $e) {
                    Log::warning('CancelExpiredBookings: Thawani resync failed, proceeding to expire on schedule', [
                        'booking_id' => $id,
                        'error'      => $e->getMessage(),
                    ]);
                }
            }

            DB::transaction(function () use ($id, $globalExpiryMinutes, &$cancelled) {
                // Re-check under lock: a webhook may confirm this booking concurrently.
                $booking = Booking::with('kiosk')->where('id', $id)->lockForUpdate()->first();

                if (!$booking || $booking->status !== 'pending') {
                    return;
                }

                $expiryMinutes = $booking->kiosk?->bookingRuleOverrides()['pending_booking_expiry_minutes']
                    ?? $globalExpiryMinutes;
                $cutoff = now()->subMinutes($expiryMinutes);

                if ($booking->created_at >= $cutoff) {
                    return;
                }

                if ($booking->payment_status === 'pending') {
                    $booking->update(['payment_status' => 'failed']);
                }

                $booking->cancel('expired');
                $cancelled++;
            });
        }

        $this->info("Cancelled {$cancelled} expired pending booking(s).");

        return self::SUCCESS;
    }
}
