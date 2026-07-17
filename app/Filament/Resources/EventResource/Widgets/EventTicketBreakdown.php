<?php

namespace App\Filament\Resources\EventResource\Widgets;

use App\Models\Event;
use App\Support\EventInsightsReport;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;

class EventTicketBreakdown extends BaseWidget
{
    public ?Event $record = null;

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    protected function getTableHeading(): string
    {
        return __('widgets.event_insights.ticket_breakdown_heading');
    }

    private function buildRecords(): \Illuminate\Support\Collection
    {
        return collect(EventInsightsReport::build($this->record, Carbon::now()->subDays(30), Carbon::now())['byTicket'])
            ->values()
            ->map(fn (array $row, int $index) => [...$row, '__key' => (string) $index]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->records(fn () => $this->buildRecords())
            ->columns([
                TextColumn::make('ticket_type')
                    ->label(__('reports.columns.ticket_type'))
                    ->weight('bold'),

                TextColumn::make('bookings')
                    ->label(__('reports.columns.bookings'))
                    ->badge()
                    ->color('primary'),

                TextColumn::make('attendees')
                    ->label(__('reports.columns.attendees'))
                    ->badge()
                    ->color('info'),

                TextColumn::make('remaining')
                    ->label(__('widgets.event_insights.remaining_stock'))
                    ->placeholder(__('widgets.upcoming_events.unlimited'))
                    ->badge()
                    ->color(fn ($state) => $state !== null && $state <= 0 ? 'danger' : 'success'),

                TextColumn::make('revenue')
                    ->label(__('reports.columns.revenue'))
                    ->money('OMR')
                    ->weight('bold')
                    ->color('success'),
            ])
            ->paginated(false);
    }
}
