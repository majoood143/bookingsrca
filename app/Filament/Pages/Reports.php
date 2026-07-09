<?php

namespace App\Filament\Pages;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use App\Exports\ReportsExport;
use App\Filament\Pages\Concerns\HasReportPeriodFilter;
use App\Models\Booking;
use App\Models\Event;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\LaravelPdf\Enums\Format;
use Spatie\LaravelPdf\Facades\Pdf;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class Reports extends Page implements HasForms
{
    use InteractsWithForms;
    use HasReportPeriodFilter;
    use HasPageShield;

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

                        DatePicker::make('event_date')
                            ->label(__('reports.filters.event_date'))
                            ->native(false)
                            ->placeholder(__('reports.filters.all_dates'))
                            ->disabled(fn(callable $get) => !$get('event_id'))
                            ->minDate(fn(callable $get) => $this->getEventDateBounds($get('event_id'))[0])
                            ->maxDate(fn(callable $get) => $this->getEventDateBounds($get('event_id'))[1])
                            ->disabledDates(fn(callable $get) => $this->getDisabledEventDates($get('event_id')))
                            ->live()
                            ->afterStateUpdated(fn(callable $set) => $set('time_slot_id', null)),

                        Select::make('time_slot_id')
                            ->label(__('reports.filters.time_slot'))
                            ->options(fn(callable $get) => $this->getTimeSlotOptions($get('event_id'), $get('event_date')))
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
            Action::make('downloadPdf')
                ->label(__('reports.actions.download_pdf'))
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->action(function () {
                    $report = $this->getReportData();
                    $locale = app()->getLocale();
                    $period = $this->data['period'] ?? 'this_month';
                    $filename = __('reports.export.filename') . '-' . $period . '-' . now()->format('Y-m-d') . '.pdf';

                    $pdf = Pdf::view('reports.reports-pdf', [
                        ...$report,
                        'locale' => $locale,
                    ])
                        ->headerView('reports.partials.pdf-header', [
                            'title' => __('reports.document_title'),
                            'periodLabel' => __('reports.document.period', [
                                'from' => $report['from']->format('Y-m-d'),
                                'to' => $report['to']->format('Y-m-d'),
                            ], $locale),
                            'locale' => $locale,
                            'logoBase64' => $this->getLogoBase64(),
                        ])
                        ->footerView('reports.partials.pdf-footer', [
                            'locale' => $locale,
                        ])
                        ->format(Format::A4)
                        ->margins(38, 12, 18, 12, 'mm')
                        ->withBrowsershot(fn($browsershot) => $browsershot->waitForFunction('window.pdfReady === true', null, 15000));

                    return Response::streamDownload(
                        fn() => print($pdf->generatePdfContent()),
                        $filename,
                        ['Content-Type' => 'application/pdf'],
                    );
                }),

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
