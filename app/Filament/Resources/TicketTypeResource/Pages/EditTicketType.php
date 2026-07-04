<?php

namespace App\Filament\Resources\TicketTypeResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use App\Filament\Resources\TicketTypeResource;
use App\Filament\Resources\TicketTypeResource\Pages\ListTicketTypeActivities;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTicketType extends EditRecord
{
    protected static string $resource = TicketTypeResource::class;

    public function getSubNavigation(): array
    {
        return $this->generateNavigationItems([
            self::class,
            ListTicketTypeActivities::class,
        ]);
    }

    public function getSubNavigationParameters(): array
    {
        return ['record' => $this->getRecord()];
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function () {
                    if ($this->record->bookings()->count() > 0) {
                        Notification::make()
                            ->danger()
                            ->title(__('ticket_type.notifications.cannot_delete'))
                            ->body(__('ticket_type.notifications.has_bookings'))
                            ->send();

                        $this->halt();
                    }
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __('ticket_type.notifications.updated');
    }
}
