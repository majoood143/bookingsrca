<?php

namespace App\Filament\Resources\PromoCodeResource\RelationManagers;

use Illuminate\Database\Eloquent\Model;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\Action;
use App\Models\Booking;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;

class BookingsRelationManager extends RelationManager
{
    protected static string $relationship = 'bookings';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('promo.rel_bookings_title');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('booking_reference')
                    ->label(__('promo.rel_col_booking_reference'))
                    ->searchable()
                    ->copyable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('firstAttendee.first_name')
                    ->label(__('promo.rel_col_customer'))
                    ->getStateUsing(fn(Booking $record): string => trim(($record->firstAttendee?->first_name ?? '') . ' ' . ($record->firstAttendee?->last_name ?? '')) ?: '—')
                    ->description(fn(Booking $record) => $record->firstAttendee?->email),

                TextColumn::make('event.title')
                    ->label(__('promo.rel_col_event'))
                    ->getStateUsing(fn(Booking $record): string => $record->event
                        ? $record->event->getTranslation('title', app()->getLocale())
                        : '-'),

                TextColumn::make('event_date')
                    ->label(__('promo.rel_col_booking_date'))
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('discount_amount')
                    ->label(__('promo.rel_col_discount'))
                    ->formatStateUsing(fn($state) => number_format((float) $state, 3) . ' OMR')
                    ->color('success'),

                TextColumn::make('total_price')
                    ->label(__('promo.rel_col_total'))
                    ->formatStateUsing(fn($state) => number_format((float) $state, 3) . ' OMR'),

                TextColumn::make('status')
                    ->label(__('promo.rel_col_status'))
                    ->badge()
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->color(fn($state) => match ($state) {
                        'confirmed'  => 'success',
                        'pending'    => 'warning',
                        'checked_in' => 'info',
                        'cancelled'  => 'danger',
                        default      => 'gray',
                    }),

                TextColumn::make('payment_status')
                    ->label(__('promo.rel_col_payment_status'))
                    ->badge()
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->color(fn($state) => match ($state) {
                        'paid'    => 'success',
                        'partial' => 'warning',
                        'pending' => 'gray',
                        default   => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label(__('promo.rel_col_created_at'))
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('promo.rel_col_status'))
                    ->options([
                        'pending'    => 'Pending',
                        'confirmed'  => 'Confirmed',
                        'checked_in' => 'Checked In',
                        'cancelled'  => 'Cancelled',
                    ]),

                SelectFilter::make('payment_status')
                    ->label(__('promo.rel_col_payment_status'))
                    ->options([
                        'pending' => 'Pending',
                        'partial' => 'Partial',
                        'paid'    => 'Paid',
                    ]),
            ])
            ->headerActions([
                Action::make('export_bookings')
                    ->label(__('promo.rel_export_bookings'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function () {
                        $promoCode = $this->getOwnerRecord();
                        $bookings  = $promoCode->bookings()->with(['event', 'firstAttendee'])->get();

                        $filename = 'promo_' . $promoCode->code . '_bookings_' . now()->format('Y_m_d_His') . '.csv';
                        $path     = storage_path('app/public/exports/' . $filename);

                        if (!file_exists(dirname($path))) {
                            mkdir(dirname($path), 0755, true);
                        }

                        $file = fopen($path, 'w');

                        fputcsv($file, [
                            __('promo.rel_col_booking_reference'),
                            __('promo.rel_col_customer'),
                            __('promo.export_col_email'),
                            __('promo.export_col_phone'),
                            __('promo.rel_col_event'),
                            __('promo.rel_col_booking_date'),
                            __('promo.rel_col_discount'),
                            __('promo.rel_col_total'),
                            __('promo.rel_col_status'),
                            __('promo.rel_col_payment_status'),
                            __('promo.rel_col_created_at'),
                        ]);

                        foreach ($bookings as $booking) {
                            fputcsv($file, [
                                $booking->booking_reference,
                                trim(($booking->firstAttendee?->first_name ?? '') . ' ' . ($booking->firstAttendee?->last_name ?? '')),
                                $booking->firstAttendee?->email ?? '',
                                $booking->firstAttendee?->phone ?? '',
                                $booking->event ? $booking->event->getTranslation('title', 'en') : '',
                                $booking->event_date?->format('Y-m-d'),
                                number_format((float) $booking->discount_amount, 3),
                                number_format((float) $booking->total_price, 3),
                                ucfirst($booking->status),
                                ucfirst($booking->payment_status),
                                $booking->created_at?->format('Y-m-d H:i:s'),
                            ]);
                        }

                        fclose($file);

                        Notification::make()
                            ->success()
                            ->title(__('promo.export_success_title'))
                            ->body(__('promo.export_success_body', ['filename' => $filename]))
                            ->actions([
                                Action::make('download')
                                    ->label(__('promo.export_download'))
                                    ->url(asset('storage/exports/' . $filename))
                                    ->openUrlInNewTab(),
                            ])
                            ->send();
                    }),
            ])
            ->recordActions([])
            ->toolbarActions([])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50]);
    }
}
