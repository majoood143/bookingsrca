<?php

namespace App\Filament\Resources\BookingResource\Pages;

use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use App\Mail\BookingConfirmation;
use App\Filament\Resources\BookingResource;
use App\Filament\Resources\BookingResource\Pages\EditBooking;
use App\Filament\Resources\BookingResource\Pages\ListBookingActivities;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Mail;

class ViewBooking extends ViewRecord
{
    protected static string $resource = BookingResource::class;

    public function getSubNavigation(): array
    {
        return $this->generateNavigationItems([
            self::class,
            EditBooking::class,
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
            EditAction::make(),

            Action::make('confirm')
                ->label(__('booking.actions.confirm_booking'))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->action(function () {
                    $this->record->confirm();
                    Notification::make()
                        ->success()
                        ->title(__('booking.notifications.booking_confirmed'))
                        ->send();
                })
                ->requiresConfirmation()
                ->visible(fn() => $this->record->status === 'pending'),

            Action::make('cancel')
                ->label(__('booking.actions.cancel_booking'))
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->action(function () {
                    $this->record->cancel();
                    Notification::make()
                        ->success()
                        ->title(__('booking.notifications.booking_cancelled'))
                        ->send();
                })
                ->requiresConfirmation()
                ->visible(fn() => in_array($this->record->status, ['pending', 'confirmed'])),

            Action::make('check_in')
                ->label(__('booking.actions.check_in'))
                ->icon('heroicon-o-user-plus')
                ->color('primary')
                ->action(function () {
                    $this->record->update(['status' => 'checked_in']);
                    Notification::make()
                        ->success()
                        ->title(__('booking.notifications.checked_in'))
                        ->send();
                })
                ->requiresConfirmation()
                ->visible(fn() => $this->record->status === 'confirmed'),

            Action::make('download_qr')
                ->label(__('booking.actions.download_qr'))
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                //->url(fn() => $this->record->getQrCodeUrl())
                ->openUrlInNewTab()
                ->visible(fn() => $this->record->qr_code),

            Action::make('print_tickets')
                ->label(__('booking.actions.print_tickets'))
                ->icon('heroicon-o-ticket')
                ->color('gray')
                ->url(fn() => route('bookings.attendee-tickets', $this->record))
                ->openUrlInNewTab()
                ->tooltip(__('booking.tooltips.print_tickets')),

            Action::make('resend_email')
                ->label(__('booking.actions.resend_email'))
                ->icon('heroicon-o-envelope')
                ->color('secondary')
                ->action(function () {
                    $primary = $this->record->attendees->first();

                    if (! $primary || empty($primary->email)) {
                        Notification::make()
                            ->danger()
                            ->title(__('booking.notifications.email_sent'))
                            ->body(__('booking.notifications.no_attendee_email'))
                            ->send();

                        return;
                    }

                    Mail::to($primary->email)->send(new BookingConfirmation($this->record));
                    Notification::make()
                        ->success()
                        ->title(__('booking.notifications.email_sent'))
                        ->body(__('booking.notifications.email_resent_body'))
                        ->send();
                })
                ->requiresConfirmation()
                ->visible(fn() => in_array($this->record->status, ['confirmed', 'checked_in'])),
        ];
    }
}