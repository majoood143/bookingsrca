<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;

class BackfillOnlinePayments extends Command
{
    protected $signature = 'payments:backfill-online {--dry-run : Preview the records that would be created without writing anything}';

    protected $description = 'Create BookingPayment records for existing online-gateway bookings (Thawani, NBO, CCAvenue) that were paid before online payments were tracked per-transaction.';

    private const GATEWAY_NOTES = [
        'thawani'  => 'Backfilled — online payment via Thawani.',
        'nbo'      => 'Backfilled — online payment via NBO.',
        'ccavenue' => 'Backfilled — online payment via CCAvenue (Bank Muscat).',
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $bookings = Booking::query()
            ->whereIn('payment_method', array_keys(self::GATEWAY_NOTES))
            ->where('payment_status', 'paid')
            ->get();

        $created = 0;
        $skipped = 0;

        foreach ($bookings as $booking) {
            $alreadyRecorded = $booking->payments()
                ->where('payment_method', $booking->payment_method)
                ->exists();

            if ($alreadyRecorded) {
                $skipped++;
                continue;
            }

            $this->line("Booking {$booking->booking_reference}: {$booking->payment_method}, OMR {$booking->total_price}");

            if (!$dryRun) {
                $booking->payments()->create([
                    'payment_method' => $booking->payment_method,
                    'amount'         => $booking->total_price,
                    'reference'      => $booking->payment_reference,
                    'notes'          => self::GATEWAY_NOTES[$booking->payment_method],
                ]);
            }

            $created++;
        }

        $prefix = $dryRun ? '[Dry run] ' : '';
        $verb   = $dryRun ? 'would be created' : 'created';

        $this->info("{$prefix}Checked {$bookings->count()} paid online booking(s): {$created} payment record(s) {$verb}, {$skipped} already had one.");

        return self::SUCCESS;
    }
}
