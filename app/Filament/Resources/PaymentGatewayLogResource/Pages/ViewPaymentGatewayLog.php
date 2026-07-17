<?php

namespace App\Filament\Resources\PaymentGatewayLogResource\Pages;

use App\Filament\Resources\BookingResource;
use App\Filament\Resources\PaymentGatewayLogResource;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class ViewPaymentGatewayLog extends ViewRecord
{
    protected static string $resource = PaymentGatewayLogResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('payment_gateway_log.sections.overview'))
                    ->columns(4)
                    ->schema([
                        TextEntry::make('gateway')
                            ->label(__('payment_gateway_log.columns.gateway'))
                            ->badge(),

                        TextEntry::make('event')
                            ->label(__('payment_gateway_log.columns.event'))
                            ->badge()
                            ->color('gray')
                            ->formatStateUsing(fn(string $state) => \Illuminate\Support\Str::of($state)->replace('_', ' ')->headline()),

                        TextEntry::make('outcome')
                            ->label(__('payment_gateway_log.columns.result'))
                            ->badge()
                            ->getStateUsing(fn($record) => $record->outcome)
                            ->formatStateUsing(fn(string $state) => __('payment_gateway_log.outcomes.' . $state))
                            ->colors([
                                'success' => 'success',
                                'danger'  => 'failed',
                                'warning' => 'pending',
                                'gray'    => ['error', 'unknown'],
                            ]),

                        TextEntry::make('status_code')
                            ->label(__('payment_gateway_log.columns.status_code'))
                            ->placeholder('—')
                            ->badge(),

                        TextEntry::make('booking.booking_reference')
                            ->label(__('payment_gateway_log.columns.booking'))
                            ->placeholder('—')
                            ->url(fn($record) => $record->booking_id
                                ? BookingResource::getUrl('view', ['record' => $record->booking_id])
                                : null)
                            ->color('primary')
                            ->weight('bold'),

                        TextEntry::make('created_at')
                            ->label(__('payment_gateway_log.columns.created_at'))
                            ->dateTime('M d, Y H:i:s'),
                    ]),

                Section::make(__('payment_gateway_log.sections.payloads'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('request_payload')
                            ->label(__('payment_gateway_log.gateway_logs.request'))
                            ->formatStateUsing(fn($state) => new HtmlString(
                                '<pre class="text-xs bg-gray-50 dark:bg-gray-800 rounded p-3 overflow-x-auto whitespace-pre-wrap">'
                                . e(json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))
                                . '</pre>'
                            ))
                            ->html()
                            ->columnSpan(1),

                        TextEntry::make('response_payload')
                            ->label(__('payment_gateway_log.gateway_logs.response'))
                            ->formatStateUsing(fn($state) => new HtmlString(
                                '<pre class="text-xs bg-gray-50 dark:bg-gray-800 rounded p-3 overflow-x-auto whitespace-pre-wrap">'
                                . e(json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))
                                . '</pre>'
                            ))
                            ->html()
                            ->columnSpan(1),
                    ]),
            ]);
    }
}
