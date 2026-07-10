<?php

namespace App\Livewire;

use App\Livewire\Concerns\GuardsPrivateEvents;
use App\Models\Event;
use App\Models\EventSignageSetting;
use App\Models\TimeSlot;
use Carbon\Carbon;
use Livewire\Component;

class EventSignageDashboard extends Component
{
    use GuardsPrivateEvents;

    public Event $event;

    public ?array $nextTrip = null;

    /** @var array<int, array> */
    public array $upcomingTrips = [];

    public function mount(): void
    {
        $this->guardEventAccess($this->event);

        if (! $this->passwordRequired) {
            $this->refreshData();
        }
    }

    protected function afterEventUnlocked(): void
    {
        $this->refreshData();
    }

    public function refreshData(): void
    {
        $now = Carbon::now();
        $signage = $this->signage();

        $slots = $this->event->timeSlots()
            ->where('is_active', true)
            ->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->filter(fn (TimeSlot $slot) => $this->slotStartsAt($slot)->gt($now))
            ->values();

        $next = $slots->first();
        $rest = $next ? $slots->slice(1, $signage->upcoming_trips_count) : collect();

        $this->nextTrip = $next ? $this->presentTrip($next, 1, $signage, $now) : null;
        $this->upcomingTrips = $rest->values()
            ->map(fn (TimeSlot $slot, int $index) => $this->presentTrip($slot, $index + 2, $signage, $now))
            ->all();
    }

    public function signage(): EventSignageSetting
    {
        return $this->event->signageSettingOrDefault();
    }

    private function slotStartsAt(TimeSlot $slot): Carbon
    {
        return Carbon::parse($slot->date->format('Y-m-d') . ' ' . $slot->start_time->format('H:i:s'));
    }

    private function presentTrip(TimeSlot $slot, int $ordinal, EventSignageSetting $signage, Carbon $now): array
    {
        $startsAt = $this->slotStartsAt($slot);
        $minutesUntil = $now->diffInMinutes($startsAt, false);

        $status = match (true) {
            $minutesUntil <= $signage->ready_threshold_minutes => 'ready',
            $minutesUntil <= $signage->soon_threshold_minutes => 'soon',
            default => 'waiting',
        };

        return [
            'id' => $slot->id,
            'label' => [
                'en' => $slot->displayLabel($ordinal, 'en'),
                'ar' => $slot->displayLabel($ordinal, 'ar'),
            ],
            'time_range' => $slot->getTimeRange(),
            'starts_at' => $startsAt->toIso8601String(),
            'booked_count' => $slot->getActiveBookedQuantity(),
            'remaining_seats' => $slot->getRemainingCapacity(),
            'status' => $status,
        ];
    }

    public function render()
    {
        if ($this->passwordRequired) {
            return view('livewire.event-password-prompt');
        }

        return view('livewire.event-signage-dashboard', [
            'signage' => $this->signage(),
        ]);
    }
}
