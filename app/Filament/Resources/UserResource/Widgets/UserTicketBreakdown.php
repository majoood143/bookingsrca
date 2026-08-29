<?php

namespace App\Filament\Resources\UserResource\Widgets;

use App\Models\User;
use App\Support\UserInsightsReport;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;

class UserTicketBreakdown extends BaseWidget
{
    public ?User $record = null;

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected function getTableHeading(): string
    {
        return __('widgets.user_insights.ticket_breakdown_heading');
    }

    private function buildRecords(): \Illuminate\Support\Collection
    {
        return UserInsightsReport::build($this->record, Carbon::now()->subDays(30), Carbon::now())['byTicketType']
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

                TextColumn::make('revenue')
                    ->label(__('reports.columns.revenue'))
                    ->html()
                    ->formatStateUsing(fn ($state) => view('partials.currency-amount', ['amount' => (float) $state])->render())
                    ->weight('bold')
                    ->color('success'),
            ])
            ->paginated(false)
            ->emptyStateHeading(__('widgets.user_insights.no_bookings'));
    }
}
