<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Models\BookingSetting;
use App\Models\ExtraService;
use Filament\Notifications\Notification;
use App\Filament\Resources\BookingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterFill(): void
    {
        // The "attendees" repeater uses ->relationship(), so Filament's
        // relationship loader always resets it to [] for a brand-new,
        // unsaved booking (there are no related attendee rows yet),
        // overriding the repeater's defaultItems() count. Re-seed it here
        // so the attendee fields reflect the selected quantity.
        $minQty = (int) BookingSetting::get('min_tickets_per_booking', 1) ?: 1;
        $quantity = (int) ($this->data['quantity'] ?? $minQty) ?: $minQty;

        $this->data['attendees'] = array_fill(0, $quantity, []);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Store extra services temporarily
        if (isset($data['extra_services'])) {
            $this->extraServicesToAttach = $data['extra_services'];
            unset($data['extra_services']);
        }

        // The "attendees" repeater uses ->relationship(), which makes Filament
        // mark it dehydrated(false) — it's saved automatically via
        // saveRelationships() and never appears in $data. Read it from the
        // raw form state instead.
        $this->attendeesData = $this->form->getRawState()['attendees'] ?? [];
        unset($data['attendees']);

        // bookings.ticket_type_id has no default; use the first attendee's
        // selected ticket type as the booking's primary type (same convention
        // used by the public booking flow in EventBooking::submitBooking()).
        $data['ticket_type_id'] = collect($this->attendeesData)
            ->pluck('ticket_type_id')
            ->filter()
            ->first();

        $data['source'] = 'admin';
        $data['created_by'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        $booking = $this->record;

        // Attach extra services
        if (isset($this->extraServicesToAttach) && is_array($this->extraServicesToAttach)) {
            $syncData = [];
            foreach ($this->extraServicesToAttach as $serviceId) {
                $service = ExtraService::find($serviceId);
                if ($service) {
                    $syncData[$serviceId] = [
                        'quantity' => $booking->quantity,
                        'price' => $service->price,
                    ];
                }
            }
            $booking->extraServices()->sync($syncData);
        }

        // Auto-confirm booking
        $booking->confirm();

        Notification::make()
            ->success()
            ->title(__('booking.notifications.booking_created'))
            ->body(__('booking.notifications.booking_created_body'))
            ->send();
    }

    protected $extraServicesToAttach = [];
    protected $attendeesData = [];
}