<?php

namespace App\Filament\Resources\BookingAttendeeResource\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use App\Filament\Resources\BookingAttendeeResource;
use App\Filament\Resources\BookingAttendeeResource\Pages\ListBookingAttendeeActivities;
use App\Filament\Resources\BookingResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBookingAttendee extends ViewRecord
{
    protected static string $resource = BookingAttendeeResource::class;

    public function getSubNavigation(): array
    {
        return $this->generateNavigationItems([
            self::class,
            ListBookingAttendeeActivities::class,
        ]);
    }

    public function getSubNavigationParameters(): array
    {
        return ['record' => $this->getRecord()];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('resend_ticket')
                ->label(__('booking_attendee.actions.resend_ticket'))
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->action(function () {
                    if ($this->record->sendTicketEmail()) {
                        Notification::make()
                            ->success()
                            ->title(__('booking_attendee.notifications.ticket_resent'))
                            ->body(__('booking_attendee.notifications.ticket_resent_body', ['email' => $this->record->email]))
                            ->send();
                    } else {
                        Notification::make()
                            ->danger()
                            ->title(__('booking_attendee.notifications.ticket_resend_failed'))
                            ->body(__('booking_attendee.notifications.ticket_resend_failed_body'))
                            ->send();
                    }
                })
                ->requiresConfirmation()
                ->modalHeading(__('booking_attendee.modals.resend_heading'))
                ->modalDescription(fn() => __('booking_attendee.modals.resend_description', ['email' => $this->record->email]))
                ->modalSubmitActionLabel(__('booking_attendee.modals.resend_submit')),

            Action::make('download_ticket')
                ->label(__('booking_attendee.actions.download_ticket'))
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->url(fn() => $this->record->getPdfUrl())
                ->openUrlInNewTab()
                ->visible(fn() => filled($this->record->pdf_path)),

            Action::make('print_ticket')
                ->label(__('booking_attendee.actions.print_ticket'))
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn() => $this->record->getPdfUrl())
                ->openUrlInNewTab()
                ->visible(fn() => filled($this->record->pdf_path)),

            Action::make('check_in')
                ->label(fn() => $this->record->checked_in
                    ? __('booking_attendee.actions.undo_check_in')
                    : __('booking_attendee.actions.check_in'))
                ->icon(fn() => $this->record->checked_in
                    ? 'heroicon-o-arrow-uturn-left'
                    : 'heroicon-o-check-badge')
                ->color(fn() => $this->record->checked_in ? 'warning' : 'primary')
                ->action(function () {
                    if ($this->record->checked_in) {
                        $this->record->update(['checked_in' => false, 'checked_in_at' => null]);
                        Notification::make()
                            ->warning()
                            ->title(__('booking_attendee.notifications.check_in_undone'))
                            ->body(__('booking_attendee.notifications.check_in_undone_body', ['name' => $this->record->getFullName()]))
                            ->send();
                    } else {
                        $this->record->checkIn();
                        Notification::make()
                            ->success()
                            ->title(__('booking_attendee.notifications.checked_in'))
                            ->body(__('booking_attendee.notifications.checked_in_body', ['name' => $this->record->getFullName()]))
                            ->send();
                    }
                    $this->refreshFormData(['checked_in', 'checked_in_at']);
                })
                ->requiresConfirmation()
                ->modalHeading(__('booking_attendee.modals.check_in_heading'))
                ->modalDescription(fn() => __('booking_attendee.modals.check_in_description', ['name' => $this->record->getFullName()]))
                ->modalSubmitActionLabel(__('booking_attendee.modals.check_in_submit'))
                ->visible(fn() => in_array($this->record->booking->status, ['confirmed', 'checked_in'])),

            Action::make('view_booking')
                ->label(__('booking_attendee.actions.view_booking'))
                ->icon('heroicon-o-rectangle-stack')
                ->color('gray')
                ->url(fn() => BookingResource::getUrl('view', ['record' => $this->record->booking_id])),
        ];
    }
}
