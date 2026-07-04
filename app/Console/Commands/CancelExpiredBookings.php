<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\BookingSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CancelExpiredBookings extends Command
{
    protected $signature = 'bookings:cancel-expired';

    protected $description = 'Cancel pending bookings that have sat awaiting payment past the configured timeout, releasing the slot/ticket capacity they were holding.';

    public function handle(): int
    {
        $expiryMinutes = (int) BookingSetting::get('pending_booking_expiry_minutes', 15);
        $cutoff = now()->subMinutes($expiryMinutes);

        $staleBookingIds = Booking::where('status', 'pending')
            ->where('created_at', '<', $cutoff)
            ->pluck('id');

        $cancelled = 0;

        foreach ($staleBookingIds as $id) {
            DB::transaction(function () use ($id, $cutoff, &$cancelled) {
                // Re-check under lock: a webhook may confirm this booking concurrently.
                $booking = Booking::where('id', $id)->lockForUpdate()->first();

                if (!$booking || $booking->status !== 'pending' || $booking->created_at >= $cutoff) {
                    return;
                }

                $booking->cancel();
                $cancelled++;
            });
        }

        $this->info("Cancelled {$cancelled} expired pending booking(s).");

        return self::SUCCESS;
    }
}
