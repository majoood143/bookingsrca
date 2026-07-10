<?php

namespace App\Livewire\Concerns;

use App\Models\Event;

// Shared by the public /events/{slug} pages (booking + signage): keeps
// draft/cancelled events 404 for guests, and gates 'private' events behind
// either an authenticated (admin panel) session or a per-event password
// unlocked once per browser session — mirrors WordPress password-protected
// posts rather than being a real auth credential.
trait GuardsPrivateEvents
{
    public bool $passwordRequired = false;

    public string $eventPasswordInput = '';

    public ?string $eventPasswordError = null;

    protected function guardEventAccess(Event $event): void
    {
        if ($event->status === 'published' || auth()->check()) {
            return;
        }

        if (! $event->isPrivate()) {
            abort(404);
        }

        $this->passwordRequired = ! $this->eventIsUnlocked($event);
    }

    public function submitEventPassword(): void
    {
        $event = $this->event;

        if (! $event || ! $event->checkPassword($this->eventPasswordInput)) {
            $this->eventPasswordError = __('event_booking.private.incorrect');
            return;
        }

        $unlocked = session()->get('unlocked_events', []);
        $unlocked[] = $event->id;
        session()->put('unlocked_events', array_values(array_unique($unlocked)));

        $this->passwordRequired = false;
        $this->eventPasswordError = null;
        $this->eventPasswordInput = '';

        $this->afterEventUnlocked();
    }

    // Hook for components that skipped their normal mount()-time setup while
    // the password prompt was showing — runs once access is granted.
    protected function afterEventUnlocked(): void
    {
        //
    }

    protected function eventIsUnlocked(Event $event): bool
    {
        return in_array($event->id, session()->get('unlocked_events', []), true);
    }
}
