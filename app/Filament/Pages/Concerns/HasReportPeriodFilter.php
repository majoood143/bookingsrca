<?php

namespace App\Filament\Pages\Concerns;

use App\Models\TimeSlot;
use Illuminate\Support\Carbon;

trait HasReportPeriodFilter
{
    protected function getDateRange(): array
    {
        $period = $this->data['period'] ?? 'this_month';

        return match ($period) {
            'today'      => [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()],
            'this_week'  => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'this_month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'last_month' => [
                Carbon::now()->subMonth()->startOfMonth(),
                Carbon::now()->subMonth()->endOfMonth(),
            ],
            'this_year'  => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()],
            'custom'     => [
                isset($this->data['date_from']) && $this->data['date_from']
                    ? Carbon::parse($this->data['date_from'])->startOfDay()
                    : Carbon::now()->startOfMonth(),
                isset($this->data['date_to']) && $this->data['date_to']
                    ? Carbon::parse($this->data['date_to'])->endOfDay()
                    : Carbon::now()->endOfDay(),
            ],
            default => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
        };
    }

    protected function getEventDateOptions(?int $eventId): array
    {
        if (!$eventId) {
            return [];
        }

        return TimeSlot::where('event_id', $eventId)
            ->orderBy('date')
            ->distinct()
            ->pluck('date')
            ->mapWithKeys(fn($date) => [$date->format('Y-m-d') => $date->format('Y-m-d')])
            ->all();
    }

    protected function getEventDateBounds(?int $eventId): array
    {
        $available = array_keys($this->getEventDateOptions($eventId));

        if (empty($available)) {
            return [null, null];
        }

        return [min($available), max($available)];
    }

    protected function getDisabledEventDates(?int $eventId): array
    {
        [$start, $end] = $this->getEventDateBounds($eventId);

        if (!$start || !$end) {
            return [];
        }

        $available = array_keys($this->getEventDateOptions($eventId));
        $disabled  = [];

        for ($date = Carbon::parse($start); $date->lte($end); $date->addDay()) {
            $formatted = $date->format('Y-m-d');

            if (!in_array($formatted, $available, true)) {
                $disabled[] = $formatted;
            }
        }

        return $disabled;
    }

    protected function getTimeSlotOptions(?int $eventId, ?string $eventDate): array
    {
        if (!$eventId || !$eventDate) {
            return [];
        }

        return TimeSlot::where('event_id', $eventId)
            ->where('date', $eventDate)
            ->get()
            ->mapWithKeys(fn($slot) => [$slot->id => $slot->getTimeRange()])
            ->all();
    }

    protected function getReportLanguage(): string
    {
        $lang = $this->data['language'] ?? app()->getLocale();

        return in_array($lang, ['en', 'ar']) ? $lang : 'en';
    }

    protected function getLogoBase64(): string
    {
        $path = storage_path('app/public/avatars/logo.jpg');

        return file_exists($path) ? base64_encode(file_get_contents($path)) : '';
    }

    /**
     * Force a numeric/Latin string (e.g. a "09:00 - 13:00" time range) to render
     * left-to-right even inside an RTL document, where the bidi algorithm would
     * otherwise reorder the dash-separated number groups.
     */
    protected function forceLtr(string $text): string
    {
        return "\u{202D}" . $text . "\u{202C}";
    }
}
