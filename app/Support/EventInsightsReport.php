<?php

namespace App\Support;

use App\Models\Booking;
use App\Models\Event;
use Illuminate\Support\Carbon;

class EventInsightsReport
{
    public static function build(Event $event, Carbon $from, Carbon $to): array
    {
        $locale = app()->getLocale();

        $bookings = Booking::query()
            ->with(['ticketType'])
            ->where('event_id', $event->id)
            ->whereBetween('created_at', [$from, $to])
            ->get();

        $totalBookings  = $bookings->count();
        $confirmedCount = $bookings->where('status', 'confirmed')->count();
        $pendingCount   = $bookings->where('status', 'pending')->count();
        $cancelledCount = $bookings->where('status', 'cancelled')->count();
        $checkedInCount = $bookings->where('status', 'checked_in')->count();
        $totalRevenue   = (float) $bookings->whereIn('status', ['confirmed', 'checked_in'])->sum('total_price');
        $totalAttendees = (int) $bookings->sum('quantity');
        $totalDiscount  = (float) $bookings->sum('discount_amount');

        $revenueBookings = $bookings->whereIn('status', ['confirmed', 'checked_in']);
        $avgTicketPrice  = $revenueBookings->count() > 0
            ? $totalRevenue / max(1, $revenueBookings->sum('quantity'))
            : 0.0;

        $byTicket = $bookings->groupBy('ticket_type_id')->map(function ($group) use ($locale) {
            $first = $group->first();

            return [
                'ticket_type' => $first->ticketType?->getTranslation('name', $locale) ?? '—',
                'bookings'    => $group->count(),
                'attendees'   => $group->sum('quantity'),
                'revenue'     => (float) $group->whereIn('status', ['confirmed', 'checked_in'])->sum('total_price'),
                'remaining'   => $first->ticketType?->getRemainingQuantity(),
            ];
        })->values();

        $days = (int) $from->diffInDays($to);
        $days = max(1, min($days, 60));

        $trendDates   = [];
        $trendCounts  = [];
        $trendRevenue = [];

        foreach (range($days, 0) as $offset) {
            $day = $to->copy()->subDays($offset)->startOfDay();
            $trendDates[] = $day->format('M j');
            $trendCounts[] = $bookings->filter(fn ($b) => $b->created_at->isSameDay($day))->count();
            $trendRevenue[] = (float) $bookings
                ->filter(fn ($b) => $b->created_at->isSameDay($day) && in_array($b->status, ['confirmed', 'checked_in'], true))
                ->sum('total_price');
        }

        $totalBookedQty     = $event->getTotalBookings();
        $remainingCapacity  = $event->getRemainingCapacity();
        $capacityPct        = $event->max_attendees
            ? round(($totalBookedQty / max(1, $event->max_attendees)) * 100, 1)
            : null;

        return [
            'event' => $event,
            'from' => $from,
            'to' => $to,
            'totalBookings' => $totalBookings,
            'confirmedCount' => $confirmedCount,
            'pendingCount' => $pendingCount,
            'cancelledCount' => $cancelledCount,
            'checkedInCount' => $checkedInCount,
            'totalRevenue' => $totalRevenue,
            'totalAttendees' => $totalAttendees,
            'totalDiscount' => $totalDiscount,
            'avgTicketPrice' => $avgTicketPrice,
            'maxAttendees' => $event->max_attendees,
            'remainingCapacity' => $remainingCapacity,
            'capacityPct' => $capacityPct,
            'byTicket' => $byTicket,
            'trendDates' => $trendDates,
            'trendCounts' => $trendCounts,
            'trendRevenue' => $trendRevenue,
        ];
    }

    public static function logoBase64(): string
    {
        $path = storage_path('app/public/avatars/logo.jpg');

        return file_exists($path) ? base64_encode(file_get_contents($path)) : '';
    }
}
