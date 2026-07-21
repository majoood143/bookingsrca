<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\TicketType;
use App\Models\TimeSlot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// Bulk-inserts bookings + attendees via the query builder instead of Eloquent,
// so this bypasses BookingAttendee's `created` hook (real QR code + mPDF ticket
// render per row) — with that hook, seeding 22k rows would take hours and fill
// the disk with real PDFs/PNGs. Row count is overridable via SEED_ATTENDEE_COUNT
// so this can reproduce the export timeout without editing the file.
//
// Run with: php artisan db:seed --class=LargeBookingAttendeeSeeder
class LargeBookingAttendeeSeeder extends Seeder
{
    protected int $chunkSize = 500;

    public function run(): void
    {
        $count = (int) env('SEED_ATTENDEE_COUNT', 22000);

        $event = Event::firstOrCreate(
            ['slug' => 'export-load-test'],
            [
                'title' => ['en' => 'Export Load Test', 'ar' => 'اختبار تصدير البيانات'],
                'description' => ['en' => 'Seeded event for export load testing.', 'ar' => 'حدث لاختبار التصدير.'],
                'location' => ['en' => 'Test Venue', 'ar' => 'مكان الاختبار'],
                'organizer' => ['en' => 'Test', 'ar' => 'اختبار'],
                'start_date' => now()->subMonth(),
                'end_date' => now()->addMonth(),
                'is_recurring' => false,
                'status' => 'published',
                'max_attendees' => $count + 1000,
            ]
        );

        $timeSlot = TimeSlot::firstOrCreate(
            [
                'event_id' => $event->id,
                'date' => now()->format('Y-m-d'),
                'start_time' => '09:00',
                'end_time' => '18:00',
            ],
            [
                'max_attendees' => $count + 1000,
                'current_bookings' => 0,
                'is_active' => true,
            ]
        );

        $ticketType = TicketType::firstOrCreate(
            ['event_id' => $event->id, 'name->en' => 'Load Test Ticket'],
            [
                'name' => ['en' => 'Load Test Ticket', 'ar' => 'تذكرة اختبار'],
                'description' => ['en' => 'Seeded ticket type', 'ar' => 'نوع تذكرة'],
                'price' => 25.00,
                'quantity_available' => $count + 1000,
                'is_active' => true,
            ]
        );

        $runId = strtoupper(Str::random(5));
        $now = now();
        $statuses = ['confirmed', 'confirmed', 'confirmed', 'cancelled'];
        $genders = ['male', 'female'];
        $nationalities = ['Omani', 'Emirati', 'Saudi', 'Indian', 'British', 'Egyptian'];

        $seeded = 0;
        $bookingCounter = 0;

        $this->command?->getOutput()->progressStart($count);

        while ($seeded < $count) {
            $batchSize = min($this->chunkSize, $count - $seeded);

            $bookingRows = [];
            $references = [];

            for ($i = 0; $i < $batchSize; $i++) {
                $bookingCounter++;
                $status = $statuses[array_rand($statuses)];
                $reference = "LT-{$runId}-B{$bookingCounter}";
                $references[] = $reference;

                $bookingRows[] = [
                    'booking_reference' => $reference,
                    'event_id' => $event->id,
                    'time_slot_id' => $timeSlot->id,
                    'ticket_type_id' => $ticketType->id,
                    'event_date' => $now->format('Y-m-d'),
                    'quantity' => 1,
                    'ticket_price' => 25.00,
                    'services_price' => 0,
                    'total_price' => 25.00,
                    'source' => 'admin',
                    'locale' => 'en',
                    'status' => $status,
                    'payment_method' => 'cash',
                    'payment_status' => $status === 'confirmed' ? 'paid' : 'pending',
                    'confirmed_at' => $status === 'confirmed' ? $now : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('bookings')->insert($bookingRows);

            $bookingIds = DB::table('bookings')
                ->whereIn('booking_reference', $references)
                ->pluck('id', 'booking_reference');

            $attendeeRows = [];

            foreach ($bookingRows as $row) {
                $attendeeCounter = ++$seeded;

                $attendeeRows[] = [
                    'booking_id' => $bookingIds[$row['booking_reference']],
                    'ticket_type_id' => $ticketType->id,
                    'ticket_price' => 25.00,
                    'first_name' => fake()->firstName(),
                    'last_name' => fake()->lastName(),
                    'email' => "loadtest+{$runId}-{$attendeeCounter}@example.com",
                    'phone' => fake()->numerify('9########'),
                    'date_of_birth' => fake()->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
                    'gender' => $genders[array_rand($genders)],
                    'nationality' => $nationalities[array_rand($nationalities)],
                    'identity_number' => (string) fake()->numerify('########'),
                    'ticket_number' => "LT-{$runId}-T{$attendeeCounter}",
                    'email_sent' => (bool) random_int(0, 1),
                    'email_sent_at' => $now,
                    'checked_in' => (bool) random_int(0, 1),
                    'checked_in_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('booking_attendees')->insert($attendeeRows);

            $this->command?->getOutput()->progressAdvance($batchSize);
        }

        $this->command?->getOutput()->progressFinish();
        $this->command?->info("Seeded {$seeded} bookings + attendees for event '{$event->slug}' (run {$runId}).");
    }
}
