<?php

namespace App\Filament\Resources\BookingResource\Pages;

use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Notifications\Notification;
use App\Mail\BookingConfirmation;
use App\Services\Printing\ThermalPrintService;
use App\Filament\Resources\BookingResource;
use App\Filament\Resources\BookingResource\Pages\EditBooking;
use App\Filament\Resources\BookingResource\Pages\ListBookingActivities;
use App\Models\BookingSetting;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\HtmlString;

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
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('payment_method')
                            ->label(__('booking.payments.fields.method'))
                            ->options([
                                'cash' => __('booking.payments.methods.cash'),
                                'credit_debit' => __('booking.payments.methods.credit_debit'),
                                'partial' => __('booking.payments.methods.partial'),
                            ])
                            ->default('cash')
                            ->required()
                            ->native(false)
                            ->live(),

                        TextInput::make('amount')
                            ->label(__('booking.payments.fields.amount'))
                            ->numeric()
                            ->prefix(fn () => new HtmlString(view('partials.currency-label')->render()))
                            ->minValue(0)
                            ->required()
                            ->default(fn() => round($this->record->balance_due, 2))
                            ->helperText(fn() => __('booking.payments.summary.balance_due') . ': ' . BookingSetting::currencySymbol() . ' ' . number_format($this->record->balance_due, 2)),
                    ]),

                    TextInput::make('reference')
                        ->label(__('booking.payments.fields.reference'))
                        ->required(fn(Get $get) => $get('payment_method') === 'credit_debit')
                        ->maxLength(255),

                    Textarea::make('notes')
                        ->label(__('booking.payments.fields.notes'))
                        ->rows(2),
                ])
                ->action(function (array $data) {
                    $this->record->recordPaymentAndConfirm($data);
                    Notification::make()
                        ->success()
                        ->title(__('booking.notifications.booking_confirmed'))
                        ->body(__('booking.notifications.booking_confirmed_body'))
                        ->send();
                })
                ->requiresConfirmation()
                ->modalHeading(__('booking.modals.confirm_payment_heading'))
                ->modalDescription(fn() => __('booking.modals.confirm_payment_description', ['amount' => number_format($this->record->balance_due, 2)]))
                ->modalSubmitActionLabel(__('booking.modals.confirm_payment_submit'))
                ->visible(fn() => $this->record->status === 'pending'),

            Action::make('cancel')
                ->label(__('booking.actions.cancel_booking'))
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->action(function () {
                    $this->record->cancel('admin');
                    Notification::make()
                        ->success()
                        ->title(__('booking.notifications.booking_cancelled'))
                        ->send();
                })
                ->requiresConfirmation()
                ->visible(fn() => in_array($this->record->status, ['pending', 'confirmed'])),

            Action::make('refund')
                ->label(__('booking.actions.refund_booking'))
                ->icon('heroicon-o-receipt-refund')
                ->color('gray')
                ->schema([
                    Textarea::make('reason')
                        ->label(__('booking.modals.refund_reason'))
                        ->rows(2),
                ])
                ->action(function (array $data) {
                    $this->record->refund($data['reason'] ?? null);
                    Notification::make()
                        ->success()
                        ->title(__('booking.notifications.booking_refunded'))
                        ->send();
                })
                ->requiresConfirmation()
                ->modalHeading(__('booking.modals.refund_heading'))
                ->modalDescription(__('booking.modals.refund_description'))
                ->visible(fn() => $this->record->status === 'confirmed' && (auth()->user()?->can('refund', $this->record) ?? false)),

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
                ->action(function () {
                    try {
                        app(ThermalPrintService::class)->enqueueAttendeeTickets($this->record);

                        Notification::make()
                            ->success()
                            ->title(__('booking.notifications.tickets_queued'))
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->danger()
                            ->title(__('booking.notifications.tickets_queue_failed'))
                            ->body($e->getMessage())
                            ->send();
                    }
                })
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