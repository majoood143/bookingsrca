<?php

namespace App\Filament\Pages;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use App\Exports\ReportsExport;
use App\Models\Booking;
use App\Models\Event;
use App\Models\TimeSlot;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class Reports extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static string | \UnitEnum | null $navigationGroup = 'Booking Management';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.reports';

    public ?array $data = [];

    public function getTitle(): string
    {
        return __('reports.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('reports.navigation.label');
    }

    public function mount(): void
    {
        $this->form->fill([
            'period'       => 'this_month',
            'event_id'     => null,
            'event_date'   => null,
            'time_slot_id' => null,
            'date_from'    => null,
            'date_to'      => null,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(['default' => 1, 'sm' => 2, 'lg' => 6])
                    ->schema([
                        Select::make('period')
                            ->label(__('reports.filters.period'))
                            ->options([
                                'today'      => __('reports.periods.today'),
                                'this_week'  => __('reports.periods.this_week'),
                                'this_month' => __('reports.periods.this_month'),
                                'last_month' => __('reports.periods.last_month'),
                                'this_year'  => __('reports.periods.this_year'),
                                'custom'     => __('reports.periods.custom'),
                            ])
                            ->default('this_month')
                            ->selectablePlaceholder(false)
                            ->live(),

                        Select::make('event_id')
                            ->label(__('reports.filters.event'))
                            ->options(function () {
                                $locale = app()->getLocale();
                                return Event::query()
                                    ->orderByDesc('start_date')
                                    ->get()
                                    ->mapWithKeys(fn($e) => [
                                        $e->id => $e->getTranslation('title', $locale),
                                    ]);
                            })
                            ->placeholder(__('reports.filters.all_events'))
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function (callable $set) {
                                $set('event_date', null);
                                $set('time_slot_id', null);
                            }),

                        Select::make('event_date')
                            ->label(__('reports.filters.event_date'))
                            ->options(fn(callable $get) => self::getEventDateOptions($get('event_id')))
                            ->placeholder(__('reports.filters.all_dates'))
                            ->disabled(fn(callable $get) => !$get('event_id'))
                            ->live()
                            ->afterStateUpdated(fn(callable $set) => $set('time_slot_id', null)),

                        Select::make('time_slot_id')
                            ->label(__('reports.filters.time_slot'))
                            ->options(fn(callable $get) => self::getTimeSlotOptions($get('event_id'), $get('event_date')))
                            ->placeholder(__('reports.filters.all_slots'))
                            ->disabled(fn(callable $get) => !$get('event_id') || !$get('event_date'))
                            ->live(),

                        DatePicker::make('date_from')
                            ->label(__('reports.filters.date_from'))
                            ->visible(fn(callable $get) => $get('period') === 'custom')
                            ->live(),

                        DatePicker::make('date_to')
                            ->label(__('reports.filters.date_to'))
                            ->visible(fn(callable $get) => $get('period') === 'custom')
                            ->live(),
                    ]),
            ])
            ->statePath('data');
    }

    protected static function getEventDateOptions(?int $eventId): array
    {
        if (!$eventId) {
            return [];
        }

        return TimeSlot::where('event_id', $eventId)
            ->orderBy('date')
            ->distinct()
            ->pluck('date')
            ->mapWithKeys(fn($date) => [$date->format('Y-m-d') => $date->format('Y-m-d')])
            ->all();
    }

    protected static function getTimeSlotOptions(?int $eventId, ?string $eventDate): array
    {
        if (!$eventId || !$eventDate) {
            return [];
        }

        return TimeSlot::where('event_id', $eventId)
            ->where('date', $eventDate)
            ->get()
            ->mapWithKeys(fn($slot) => [$slot->id => $slot->getTimeRange()])
            ->all();
    }

    protected function getDateRange(): array
    {
        $period = $this->data['period'] ?? 'this_month';

        return match ($period) {
            'today'      => [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()],
            'this_week'  => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'this_month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'last_month' => [
                Carbon::now()->subMonth()->startOfMonth(),
                Carbon::now()->subMonth()->endOfMonth(),
            ],
            'this_year'  => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()],
            'custom'     => [
                isset($this->data['date_from']) && $this->data['date_from']
                    ? Carbon::parse($this->data['date_from'])->startOfDay()
                    : Carbon::now()->startOfMonth(),
                isset($this->data['date_to']) && $this->data['date_to']
                    ? Carbon::parse($this->data['date_to'])->endOfDay()
                    : Carbon::now()->endOfDay(),
            ],
            default => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
        };
    }

    public function getReportData(): array
    {
        [$from, $to] = $this->getDateRange();

        $eventId    = $this->data['event_id'] ?? null;
        $eventDate  = $this->data['event_date'] ?? null;
        $timeSlotId = $this->data['time_slot_id'] ?? null;

        $bookings = Booking::query()
            ->with(['event', 'ticketType', 'attendees'])
            ->whereBetween('created_at', [$from, $to])
            ->when($eventId, fn($q) => $q->where('event_id', $eventId))
            ->when($eventDate, fn($q) => $q->where('event_date', $eventDate))
            ->when($timeSlotId, fn($q) => $q->where('time_slot_id', $timeSlotId))
            ->get();

        $locale = app()->getLocale();

        $totalBookings    = $bookings->count();
        $confirmedCount   = $bookings->where('status', 'confirmed')->count();
        $pendingCount     = $bookings->where('status', 'pending')->count();
        $cancelledCount   = $bookings->where('status', 'cancelled')->count();
        $checkedInCount   = $bookings->where('status', 'checked_in')->count();
        $totalRevenue     = $bookings->whereIn('status', ['confirmed', 'checked_in'])->sum('total_price');
        $totalAttendees   = $bookings->sum('quantity');

        $byEvent = $bookings->groupBy('event_id')->map(function ($group) use ($locale) {
            $first = $group->first();
            return [
                'event'      => $first->event?->getTranslation('title', $locale) ?? '—',
                'total'      => $group->count(),
                'confirmed'  => $group->where('status', 'confirmed')->count(),
                'pending'    => $group->where('status', 'pending')->count(),
                'cancelled'  => $group->where('status', 'cancelled')->count(),
                'checked_in' => $group->where('status', 'checked_in')->count(),
                'attendees'  => $group->sum('quantity'),
                'revenue'    => $group->whereIn('status', ['confirmed', 'checked_in'])->sum('total_price'),
            ];
        })->values();

        $byTicket = $bookings->groupBy('ticket_type_id')->map(function ($group) use ($locale) {
            $first = $group->first();
            return [
                'ticket_type' => $first->ticketType?->getTranslation('name', $locale) ?? '—',
                'bookings'    => $group->count(),
                'attendees'   => $group->sum('quantity'),
                'revenue'     => $group->whereIn('status', ['confirmed', 'checked_in'])->sum('total_price'),
            ];
        })->values();

        return compact(
            'from', 'to',
            'totalBookings', 'confirmedCount', 'pendingCount',
            'cancelledCount', 'checkedInCount', 'totalRevenue',
            'totalAttendees', 'byEvent', 'byTicket'
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label(__('reports.actions.export'))
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    [$from, $to] = $this->getDateRange();
                    $eventId    = $this->data['event_id'] ?? null;
                    $eventDate  = $this->data['event_date'] ?? null;
                    $timeSlotId = $this->data['time_slot_id'] ?? null;
                    $period     = $this->data['period'] ?? 'this_month';
                    $filename   = __('reports.export.filename') . '-' . $period . '-' . now()->format('Y-m-d') . '.xlsx';

                    return Excel::download(new ReportsExport($from, $to, $eventId, $eventDate, $timeSlotId), $filename);
                }),
        ];
    }
}
