<?php

namespace App\Filament\Resources\BookingResource\Pages;

use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use App\Models\ExtraService;
use App\Filament\Resources\BookingResource;
use App\Filament\Resources\BookingResource\Pages\ViewBooking;
use App\Filament\Resources\BookingResource\Pages\ListBookingActivities;
use Filament\Resources\Pages\EditRecord;

class EditBooking extends EditRecord
{
    protected static string $resource = BookingResource::class;

    public function getSubNavigation(): array
    {
        return $this->generateNavigationItems([
            ViewBooking::class,
            self::class,
            ListBookingActivities::class,
        ]);
    }

    public function getSubNavigationParameters(): array
    {
        return ['record' => $this->getRecord()];
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading(__('booking.modals.delete_heading'))
                ->modalDescription(__('booking.modals.delete_description')),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __('booking.notifications.booking_updated');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load extra services for the form
        $data['extra_services'] = $this->record->extraServices->pluck('id')->toArray();
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Handle extra services separately
        if (isset($data['extra_services'])) {
            $this->extraServicesToSync = $data['extra_services'];
            unset($data['extra_services']);
        }

        // Keep the booking's primary ticket_type_id (no default value in DB)
        // in sync with the first attendee's selected ticket type. The
        // "attendees" repeater uses ->relationship(), which makes Filament
        // mark it dehydrated(false), so it never appears in $data — read it
        // from the raw form state instead.
        $attendeesData = $this->form->getRawState()['attendees'] ?? [];

        $data['ticket_type_id'] = collect($attendeesData)
            ->pluck('ticket_type_id')
            ->filter()
            ->first() ?? $this->record->ticket_type_id;

        return $data;
    }

    protected function afterSave(): void
    {
        $booking = $this->record;

        // Sync extra services
        if (isset($this->extraServicesToSync)) {
            $syncData = [];
            foreach ($this->extraServicesToSync as $serviceId) {
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
    }

    protected $extraServicesToSync = [];
}
