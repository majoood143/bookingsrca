<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Services\ThawaniService;
use Exception;
use Illuminate\Console\Command;

class RepairThawaniBookings extends Command
{
    protected $signature = 'bookings:repair-thawani
                            {--apply : Change bookings. Without this the command only reports what it would do}
                            {--since= : Only look at bookings created on or after this date (default: 30 days ago)}
                            {--notify : Also send confirmation emails/tickets, for repaired bookings whose event date has not passed}';

    protected $description = 'Find Thawani bookings that were paid but ended up cancelled, or were confirmed by hand without the confirmation side effects, and fix them.';

    public function handle(ThawaniService $thawani): int
    {
        $since = $this->option('since') ?: now()->subDays(30)->toDateString();
        $apply = (bool) $this->option('apply');

        $candidates = Booking::where('payment_method', 'thawani')
            ->whereNotNull('payment_session_id')
            ->where('created_at', '>=', $since)
            ->where(function ($query) {
                $query->where(fn ($q) => $q->where('status', 'cancelled')->where('payment_status', '!=', 'paid'))
                    ->orWhere(fn ($q) => $q->where('status', 'confirmed')->whereNull('confirmed_at'));
            })
            ->orderBy('id')
            ->get();

        $this->info(($apply ? 'APPLYING' : 'DRY RUN') . " — checking {$candidates->count()} booking(s) created since {$since} against Thawani.");

        $rows   = [];
        $counts = [];

        foreach ($candidates as $booking) {
            try {
                $session = $thawani->getSession($booking->payment_session_id, $booking);
            } catch (Exception $e) {
                $rows[] = [$booking->id, $booking->booking_reference, $booking->status, 'error', $e->getMessage()];
                $counts['error'] = ($counts['error'] ?? 0) + 1;

                continue;
            }

            $data          = $session['data'] ?? [];
            $paymentStatus = $data['payment_status'] ?? 'unknown';

            if ($paymentStatus !== 'paid') {
                $rows[] = [$booking->id, $booking->booking_reference, $booking->status, $paymentStatus, 'skip (not paid at Thawani)'];

                continue;
            }

            $action = $thawani->decidePaidAction($booking);

            if ($apply && $action !== ThawaniService::ACTION_NONE) {
                $notify = $this->option('notify') && $booking->event_date->gte(today());

                $action = $thawani->applyPaidSession($booking, $data, $notify);

                if ($notify && $action === ThawaniService::ACTION_COMPLETE_MANUAL) {
                    $booking->sendConfirmationNotifications();
                }
            }

            $rows[] = [$booking->id, $booking->booking_reference, $booking->status, $paymentStatus, $action];
            $counts[$action] = ($counts[$action] ?? 0) + 1;
        }

        $this->table(['ID', 'Reference', 'Status', 'Thawani', $apply ? 'Applied' : 'Would do'], $rows);

        foreach ($counts as $action => $count) {
            $this->line("  {$action}: {$count}");
        }

        if (!$apply) {
            $this->warn('Dry run — nothing was changed. Re-run with --apply to fix these.');
        }

        return self::SUCCESS;
    }
}
