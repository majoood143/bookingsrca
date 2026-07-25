<?php

namespace App\Support;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Carbon;

class UserInsightsReport
{
    public static function build(User $user, Carbon $from, Carbon $to): array
    {
        $locale = app()->getLocale();

        $bookings = Booking::query()
            ->with(['ticketType', 'event'])
            ->where('created_by', $user->id)
            ->whereBetween('created_at', [$from, $to])
            ->get();

        $totalBookings  = $bookings->count();
        $confirmedCount = $bookings->where('status', 'confirmed')->count();
        $pendingCount   = $bookings->where('status', 'pending')->count();
        $cancelledCount = $bookings->where('status', 'cancelled')->count();
        $checkedInCount = $bookings->where('status', 'checked_in')->count();

        $revenueBookings = $bookings->whereIn('status', ['confirmed', 'checked_in']);
        $totalRevenue    = (float) $revenueBookings->sum('total_price');
        $totalAttendees  = (int) $bookings->sum('quantity');
        $avgTicketPrice  = $revenueBookings->count() > 0
            ? $totalRevenue / max(1, $revenueBookings->sum('quantity'))
            : 0.0;

        $byTicketType = $bookings->groupBy('ticket_type_id')->map(function ($group) use ($locale) {
            $first = $group->first();

            return [
                'ticket_type' => $first->ticketType?->getTranslation('name', $locale) ?? '—',
                'bookings'    => $group->count(),
                'attendees'   => $group->sum('quantity'),
                'revenue'     => (float) $group->whereIn('status', ['confirmed', 'checked_in'])->sum('total_price'),
            ];
        })->sortByDesc('bookings')->values();

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

        return [
            'user' => $user,
            'from' => $from,
            'to' => $to,
            'totalBookings' => $totalBookings,
            'confirmedCount' => $confirmedCount,
            'pendingCount' => $pendingCount,
            'cancelledCount' => $cancelledCount,
            'checkedInCount' => $checkedInCount,
            'totalRevenue' => $totalRevenue,
            'totalAttendees' => $totalAttendees,
            'avgTicketPrice' => $avgTicketPrice,
            'byTicketType' => $byTicketType,
            'trendDates' => $trendDates,
            'trendCounts' => $trendCounts,
            'trendRevenue' => $trendRevenue,
        ];
    }
}
