<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\TimeSlot;
use App\Models\TicketType;
use App\Models\ExtraService;

class EventSeeder extends Seeder
{
    public function run()
    {
        $event = Event::create([
            'title' => [
                'en' => 'Laravel Conference 2025',
                'ar' => 'مؤتمر لارافيل 2025'
            ],
            'description' => [
                'en' => 'Join us for the biggest Laravel conference of the year.',
                'ar' => 'انضم إلينا في أكبر مؤتمر لارافيل لهذا العام.'
            ],
            'slug' => 'laravel-conference-2025',
            'location' => [
                'en' => 'Dubai World Trade Centre',
                'ar' => 'مركز دبي التجاري العالمي'
            ],
            'organizer' => 'Laravel Community',
            'start_date' => now()->addMonth(),
            'end_date' => now()->addMonth()->addDays(2),
            'is_recurring' => false,
            'status' => 'published',
            'max_attendees' => 500,
        ]);

        // Create time slots — one row per date/time-range combination, since
        // capacity is now tracked per specific date rather than pooled across
        // every date the event runs on.
        $timeSlots = [
            ['start_time' => '09:00', 'end_time' => '12:00', 'max_attendees' => 250],
            ['start_time' => '14:00', 'end_time' => '17:00', 'max_attendees' => 250],
        ];

        $dates = array_slice($event->getAvailableDates(), 0, 3);

        foreach ($dates as $date) {
            foreach ($timeSlots as $slot) {
                TimeSlot::create([
                    'event_id' => $event->id,
                    'date' => $date,
                    'start_time' => $slot['start_time'],
                    'end_time' => $slot['end_time'],
                    'max_attendees' => $slot['max_attendees'],
                ]);
            }
        }

        // Create ticket types
        $ticketTypes = [
            [
                'name' => ['en' => 'Standard', 'ar' => 'عادي'],
                'description' => ['en' => 'Access to all sessions', 'ar' => 'الدخول لجميع الجلسات'],
                'price' => 199.00,
                'quantity_available' => 300,
            ],
            [
                'name' => ['en' => 'VIP', 'ar' => 'في آي بي'],
                'description' => ['en' => 'Premium access with networking', 'ar' => 'دخول مميز مع التواصل'],
                'price' => 399.00,
                'quantity_available' => 100,
            ],
            [
                'name' => ['en' => 'Student', 'ar' => 'طالب'],
                'description' => ['en' => 'Discounted rate for students', 'ar' => 'سعر مخفض للطلاب'],
                'price' => 99.00,
                'quantity_available' => 100,
            ],
        ];

        foreach ($ticketTypes as $ticket) {
            TicketType::create([
                'event_id' => $event->id,
                'name' => $ticket['name'],
                'description' => $ticket['description'],
                'price' => $ticket['price'],
                'quantity_available' => $ticket['quantity_available'],
            ]);
        }

        // Create extra services
        $services = [
            [
                'name' => ['en' => 'Lunch', 'ar' => 'وجبة غداء'],
                'description' => ['en' => 'Delicious lunch buffet', 'ar' => 'بوفيه غداء لذيذ'],
                'price' => 25.00,
                'quantity_available' => 400,
            ],
            [
                'name' => ['en' => 'T-Shirt', 'ar' => 'تي شيرت'],
                'description' => ['en' => 'Official conference t-shirt', 'ar' => 'تي شيرت المؤتمر الرسمي'],
                'price' => 15.00,
                'quantity_available' => 200,
            ],
            [
                'name' => ['en' => 'Transportation', 'ar' => 'مواصلات'],
                'description' => ['en' => 'Round trip transportation', 'ar' => 'مواصلات ذهاب وإياب'],
                'price' => 30.00,
                'quantity_available' => 150,
            ],
        ];

        foreach ($services as $service) {
            ExtraService::create([
                'event_id' => $event->id,
                'name' => $service['name'],
                'description' => $service['description'],
                'price' => $service['price'],
                'quantity_available' => $service['quantity_available'],
            ]);
        }
    }
}
