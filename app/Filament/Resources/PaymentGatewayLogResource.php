<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentGatewayLogResource\Pages\ListPaymentGatewayLogs;
use App\Filament\Resources\PaymentGatewayLogResource\Pages\ViewPaymentGatewayLog;
use App\Models\PaymentGatewayLog;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PaymentGatewayLogResource extends Resource
{
    protected static ?string $model = PaymentGatewayLog::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shield-exclamation';
    protected static ?int $navigationSort = 6;

    public static function getNavigationGroup(): ?string
    {
        return __('payment_gateway_log.navigation.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('payment_gateway_log.navigation.plural');
    }

    public static function getModelLabel(): string
    {
        return __('payment_gateway_log.navigation.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('payment_gateway_log.navigation.plural');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label(__('payment_gateway_log.columns.created_at'))
                    ->dateTime('M d, Y H:i:s')
                    ->sortable(),

                TextColumn::make('booking.booking_reference')
                    ->label(__('payment_gateway_log.columns.booking'))
                    ->placeholder('—')
                    ->searchable()
                    ->copyable()
                    ->weight('medium')
                    ->url(fn(PaymentGatewayLog $record) => $record->booking_id
                        ? BookingResource::getUrl('view', ['record' => $record->booking_id])
                        : null),

                TextColumn::make('gateway')
                    ->label(__('payment_gateway_log.columns.gateway'))
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->colors([
                        'info'    => 'thawani',
                        'warning' => 'nbo',
                        'primary' => 'ccavenue',
                    ]),

                TextColumn::make('event')
                    ->label(__('payment_gateway_log.columns.event'))
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn(string $state) => \Illuminate\Support\Str::of($state)->replace('_', ' ')->headline())
                    ->searchable()
                    ->sortable(),

                TextColumn::make('outcome')
                    ->label(__('payment_gateway_log.columns.result'))
                    ->badge()
                    ->getStateUsing(fn(PaymentGatewayLog $record) => $record->outcome)
                    ->formatStateUsing(fn(string $state) => __('payment_gateway_log.outcomes.' . $state))
                    ->colors([
                        'success' => 'success',
                        'danger'  => 'failed',
                        'warning' => 'pending',
                        'gray'    => ['error', 'unknown'],
                    ]),

                TextColumn::make('status_code')
                    ->label(__('payment_gateway_log.columns.status_code'))
                    ->badge()
                    ->placeholder('—')
                    ->sortable()
                    ->color(function ($state) {
                        if ($state === null || $state === '') {
                            return 'gray';
                        }

                        return match (true) {
                            (int) $state < 300 => 'success',
                            (int) $state < 500 => 'warning',
                            default             => 'danger',
                        };
                    }),

                TextColumn::make('response_payload')
                    ->label(__('payment_gateway_log.columns.response_preview'))
                    ->getStateUsing(fn(PaymentGatewayLog $record) => json_encode($record->response_payload))
                    ->formatStateUsing(fn(?string $state) => \Illuminate\Support\Str::limit($state ?? '', 60))
                    ->fontFamily('mono')
                    ->searchable(
                        query: fn(Builder $query, string $search): Builder => $query->searchPayloads($search),
                        isIndividual: true,
                    )
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('gateway')
                    ->label(__('payment_gateway_log.filters.gateway'))
                    ->options([
                        'thawani'  => 'Thawani',
                        'nbo'      => 'NBO',
                        'ccavenue' => 'CCAvenue',
                    ])
                    ->multiple(),

                SelectFilter::make('event')
                    ->label(__('payment_gateway_log.filters.event'))
                    ->options([
                        'create_session'   => __('payment_gateway_log.events.create_session'),
                        'get_session'      => __('payment_gateway_log.events.get_session'),
                        'initiate_payment' => __('payment_gateway_log.events.initiate_payment'),
                        'callback'         => __('payment_gateway_log.events.callback'),
                        'webhook'          => __('payment_gateway_log.events.webhook'),
                    ])
                    ->multiple(),

                SelectFilter::make('outcome')
                    ->label(__('payment_gateway_log.filters.result'))
                    ->options([
                        'success' => __('payment_gateway_log.outcomes.success'),
                        'failed'  => __('payment_gateway_log.outcomes.failed'),
                        'pending' => __('payment_gateway_log.outcomes.pending'),
                        'error'   => __('payment_gateway_log.outcomes.error'),
                        'unknown' => __('payment_gateway_log.outcomes.unknown'),
                    ])
                    ->multiple()
                    ->query(function (Builder $query, array $data): Builder {
                        $values = $data['values'] ?? [];

                        if (empty($values)) {
                            return $query;
                        }

                        return $query->where(function (Builder $query) use ($values) {
                            foreach ($values as $value) {
                                $query->orWhere(fn(Builder $query) => $query->outcome($value));
                            }
                        });
                    }),

                Filter::make('status_code')
                    ->label(__('payment_gateway_log.filters.status_code'))
                    ->schema([
                        Select::make('range')
                            ->label(__('payment_gateway_log.filters.status_code'))
                            ->options([
                                '2xx' => __('payment_gateway_log.status_ranges.2xx'),
                                '4xx' => __('payment_gateway_log.status_ranges.4xx'),
                                '5xx' => __('payment_gateway_log.status_ranges.5xx'),
                                'none' => __('payment_gateway_log.status_ranges.none'),
                            ])
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['range'] ?? null) {
                            '2xx'   => $query->whereBetween('status_code', [200, 299]),
                            '4xx'   => $query->whereBetween('status_code', [400, 499]),
                            '5xx'   => $query->whereBetween('status_code', [500, 599]),
                            'none'  => $query->whereNull('status_code'),
                            default => $query,
                        };
                    }),

                Filter::make('created_at')
                    ->label(__('payment_gateway_log.filters.date'))
                    ->schema([
                        DatePicker::make('from')
                            ->label(__('payment_gateway_log.filters.from'))
                            ->native(false),
                        DatePicker::make('until')
                            ->label(__('payment_gateway_log.filters.to'))
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['from'] ?? null) {
                            $indicators[] = __('payment_gateway_log.filters.from') . ' ' . $data['from'];
                        }

                        if ($data['until'] ?? null) {
                            $indicators[] = __('payment_gateway_log.filters.to') . ' ' . $data['until'];
                        }

                        return $indicators;
                    }),

                Filter::make('payload_search')
                    ->label(__('payment_gateway_log.filters.payload_search'))
                    ->schema([
                        TextInput::make('term')
                            ->label(__('payment_gateway_log.filters.payload_search'))
                            ->placeholder(__('payment_gateway_log.filters.payload_search_placeholder')),
                    ])
                    ->query(fn(Builder $query, array $data): Builder => $query->when(
                        filled($data['term'] ?? null),
                        fn(Builder $query) => $query->searchPayloads($data['term']),
                    ))
                    ->indicateUsing(fn(array $data): ?string => filled($data['term'] ?? null)
                        ? __('payment_gateway_log.filters.payload_search') . ': ' . $data['term']
                        : null),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->emptyStateHeading(__('payment_gateway_log.empty_state.heading'))
            ->emptyStateDescription(__('payment_gateway_log.empty_state.description'))
            ->emptyStateIcon('heroicon-o-shield-exclamation');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('booking');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPaymentGatewayLogs::route('/'),
            'view'  => ViewPaymentGatewayLog::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::query()
            ->outcome('error')
            ->where('created_at', '>=', now()->subDay())
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['gateway', 'event', 'booking.booking_reference'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return strtoupper($record->gateway) . ' · ' . $record->event;
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            __('payment_gateway_log.columns.booking') => $record->booking?->booking_reference,
            __('payment_gateway_log.columns.result')  => __('payment_gateway_log.outcomes.' . $record->outcome),
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with('booking');
    }
}
