<?php

namespace App\Filament\Pages;

use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use Exception;
use App\Models\BookingSetting;
use App\Models\Event;
use App\Models\ExtraService;
use App\Models\TicketType;
use App\Models\TimeSlot;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class BookingWizard extends Page implements HasForms
{
    use InteractsWithForms;
    use HasPageShield;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-device-tablet';

    protected static ?int $navigationSort = 0;

    protected string $view = 'filament.pages.booking-wizard';

    public ?array $data = [];

    public ?Booking $createdBooking = null;

    public static function getNavigationGroup(): ?string
    {
        return __('booking.navigation.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('booking.wizard.navigation_label');
    }

    public function getTitle(): string
    {
        return __('booking.wizard.title');
    }

    public function mount(): void
    {
        $this->resetWizardForm();
    }

    protected function resetWizardForm(): void
    {
        $minQty = BookingSetting::get('min_tickets_per_booking', 1);

        $this->createdBooking = null;

        $this->form->fill([
            'quantity' => $minQty,
            'attendees_count' => $minQty,
            'attendees' => array_fill(0, max(1, (int) $minQty), []),
            'payment_method' => 'cash',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make(__('booking.wizard.steps.event'))
                        ->icon('heroicon-o-calendar-days')
                        ->description(__('booking.wizard.steps.event_description'))
                        ->schema([
                            Section::make()
                                ->schema([
                                    Select::make('event_id')
                                        ->label(__('booking.fields.event'))
                                        ->options(fn() => Event::where('status', 'published')
                                            ->get()
                                            ->mapWithKeys(fn($event) => [
                                                $event->id => $event->getTranslation('title', app()->getLocale()),
                                            ]))
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->live()
                                        ->native(false)
                                        ->extraInputAttributes(['class' => 'text-lg'])
                                        ->afterStateUpdated(fn(Set $set) => $set('time_slot_id', null)),

                                    DatePicker::make('event_date')
                                        ->label(__('booking.fields.event_date'))
                                        ->required()
                                        ->native(false)
                                        ->displayFormat('Y-m-d')
                                        ->live()
                                        ->extraInputAttributes(['class' => 'text-lg'])
                                        ->afterStateUpdated(fn(Set $set) => $set('time_slot_id', null)),

                                    Select::make('time_slot_id')
                                        ->label(__('booking.fields.time_slot'))
                                        ->options(function (Get $get) {
                                            if (!$get('event_id') || !$get('event_date')) {
                                                return [];
                                            }

                                            return TimeSlot::where('event_id', $get('event_id'))
                                                ->where('is_active', true)
                                                ->where('date', $get('event_date'))
                                                ->get()
                                                ->mapWithKeys(function ($slot) use ($get) {
                                                    $label = $slot->getTimeRange() . ' (' . $slot->getRemainingCapacity() . ' ' . __('booking.wizard.available') . ')';

                                                    if (!self::isSlotBookable($slot, $get('event_date'))) {
                                                        $label .= ' — ' . __('booking.wizard.past_slot');
                                                    }

                                                    return [$slot->id => $label];
                                                });
                                        })
                                        ->required()
                                        ->native(false)
                                        ->live()
                                        ->extraInputAttributes(['class' => 'text-lg']),

                                    TextInput::make('quantity')
                                        ->label(__('booking.fields.quantity'))
                                        ->required()
                                        ->numeric()
                                        ->minValue(fn() => BookingSetting::get('min_tickets_per_booking', 1))
                                        ->maxValue(fn() => BookingSetting::get('max_tickets_per_booking', 10))
                                        ->default(fn() => BookingSetting::get('min_tickets_per_booking', 1))
                                        ->live(onBlur: true)
                                        ->extraInputAttributes(['class' => 'text-lg text-center'])
                                        ->helperText(fn() => __('booking.fields.quantity_helper', [
                                            'min' => BookingSetting::get('min_tickets_per_booking', 1),
                                            'max' => BookingSetting::get('max_tickets_per_booking', 10),
                                        ]))
                                        ->prefixAction(
                                            Action::make('decrease_quantity')
                                                ->icon('heroicon-o-minus')
                                                ->action(function (Set $set, Get $get) {
                                                    $min = BookingSetting::get('min_tickets_per_booking', 1);
                                                    $new = max($min, ((int) $get('quantity')) - 1);
                                                    $set('quantity', $new);
                                                    $set('attendees_count', $new);
                                                    self::syncAttendees($set, $get, $new);
                                                    self::calculateTotal($set, $get);
                                                })
                                        )
                                        ->suffixAction(
                                            Action::make('increase_quantity')
                                                ->icon('heroicon-o-plus')
                                                ->action(function (Set $set, Get $get) {
                                                    $max = BookingSetting::get('max_tickets_per_booking', 10);
                                                    $new = min($max, ((int) $get('quantity')) + 1);
                                                    $set('quantity', $new);
                                                    $set('attendees_count', $new);
                                                    self::syncAttendees($set, $get, $new);
                                                    self::calculateTotal($set, $get);
                                                })
                                        )
                                        ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                            $quantity = (int) $state ?:0;
                                            $set('attendees_count', $quantity);
                                            self::syncAttendees($set, $get, $quantity);
                                            self::calculateTotal($set, $get);
                                        }),

                                    Hidden::make('attendees_count')->default(1),
                                ])
                                ->columns(2),
                        ]),

                    Step::make(__('booking.wizard.steps.attendees'))
                        ->icon('heroicon-o-users')
                        ->description(fn(Get $get) => __('booking.attendee_details_description', ['count' => (int) $get('quantity') ?: 1]))
                        ->schema([
                            Checkbox::make('copy_contact_to_all')
                                ->label(__('booking.wizard.copy_contact_to_all'))
                                ->live()
                                ->dehydrated(false)
                                ->visible(fn(Get $get) => ((int) $get('quantity') ?: 1) > 1)
                                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                    if ($state) {
                                        self::applyFirstAttendeeContact($set, $get);
                                    }
                                }),

                            Repeater::make('attendees')
                                ->label('')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            TextInput::make('first_name')
                                                ->label(__('booking.fields.first_name'))
                                                ->required()
                                                ->maxLength(255)
                                                ->extraInputAttributes(['class' => 'text-lg'])
                                                ->placeholder(__('booking.placeholders.first_name')),

                                            TextInput::make('last_name')
                                                ->label(__('booking.fields.last_name'))
                                                ->required()
                                                ->maxLength(255)
                                                ->extraInputAttributes(['class' => 'text-lg'])
                                                ->placeholder(__('booking.placeholders.last_name')),
                                        ]),

                                    Grid::make(2)
                                        ->schema([
                                            TextInput::make('email')
                                                ->label(__('booking.fields.email'))
                                                ->email()
                                                //->required()
                                                ->maxLength(255)
                                                ->extraInputAttributes(['class' => 'text-lg'])
                                                ->placeholder(__('booking.placeholders.email')),

                                            TextInput::make('phone')
                                                ->label(__('booking.fields.phone'))
                                                ->tel()
                                                ->maxLength(20)
                                                ->extraInputAttributes(['class' => 'text-lg'])
                                                ->placeholder(__('booking.placeholders.phone')),
                                        ]),

                                    Grid::make(3)
                                        ->schema([
                                            DatePicker::make('date_of_birth')
                                                ->label(__('booking.fields.date_of_birth'))
                                                ->maxDate(now())
                                                ->minDate(fn() => now()->subYears((int) BookingSetting::get('max_attendee_age_years', 75)))
                                                ->placeholder(__('booking.placeholders.date'))
                                                ->visible(fn() => (bool) BookingSetting::get('show_date_of_birth', true)),

                                            Select::make('gender')
                                                ->label(__('booking.fields.gender'))
                                                ->options(__('booking.options.gender'))
                                                ->placeholder(__('booking.placeholders.gender'))
                                                ->visible(fn() => (bool) BookingSetting::get('show_gender', true)),

                                            Select::make('nationality')
                                                ->label(__('booking.fields.nationality'))
                                                ->required()
                                                ->options(__('booking.options.nationality'))
                                                ->placeholder(__('booking.placeholders.nationality'))
                                                ->visible(fn() => (bool) BookingSetting::get('show_nationality', true)),
                                        ]),

                                    Grid::make(1)
                                        ->visible(fn() => (bool) BookingSetting::get('show_identity_number', true))
                                        ->schema([
                                            TextInput::make('identity_number')
                                                ->label(__('booking.fields.identity_number'))
                                                ->maxLength(50)
                                                ->placeholder(__('booking.placeholders.identity_number')),
                                        ]),

                                    Grid::make(2)
                                        ->schema([
                                            Select::make('ticket_type_id')
                                                ->label(__('booking.fields.ticket_type'))
                                                ->options(function (Get $get) {
                                                    $eventId = $get('../../event_id');
                                                    if (!$eventId) {
                                                        return [];
                                                    }

                                                    return TicketType::where('event_id', $eventId)
                                                        ->where('is_active', true)
                                                        ->get()
                                                        ->mapWithKeys(fn($ticket) => [
                                                            $ticket->id => $ticket->getTranslation('name', app()->getLocale()) .
                                                                ' - OMR ' . number_format($ticket->price, 3) .
                                                                ' (' . $ticket->getRemainingQuantity() . ' ' . __('booking.wizard.available') . ')',
                                                        ]);
                                                })
                                                ->required()
                                                ->live()
                                                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                                    if ($state) {
                                                        $ticketType = TicketType::find($state);
                                                        $set('ticket_price', $ticketType?->price ?? 0);
                                                    } else {
                                                        $set('ticket_price', 0);
                                                    }

                                                    self::calculateTotal($set, $get, '../../');
                                                })
                                                ->visible(fn(Get $get) => filled($get('../../event_id')))
                                                ->dehydratedWhenHidden()
                                                ->helperText(__('booking.ticket_type_helper')),

                                            TextInput::make('ticket_price')
                                                ->label(__('booking.fields.ticket_price'))
                                                ->numeric()
                                                ->prefix('OMR')
                                                ->disabled()
                                                ->dehydrated(true)
                                                ->default(0)
                                                ->live()
                                                ->visible(fn(Get $get) => filled($get('ticket_type_id'))),
                                        ]),

                                    CheckboxList::make('extra_service_ids')
                                        ->label(__('booking.fields.extra_services'))
                                        ->options(function (Get $get) {
                                            $eventId = $get('../../event_id');
                                            if (!$eventId) {
                                                return [];
                                            }

                                            return ExtraService::where('event_id', $eventId)
                                                ->where('is_active', true)
                                                ->get()
                                                ->mapWithKeys(fn($service) => [
                                                    $service->id => $service->getTranslation('name', app()->getLocale()) .
                                                        ' — OMR ' . number_format($service->price, 3),
                                                ]);
                                        })
                                        ->columns(2)
                                        ->gridDirection('row')
                                        ->live()
                                        ->afterStateUpdated(fn(Set $set, Get $get) => self::calculateTotal($set, $get, '../../'))
                                        ->visible(fn(Get $get) => filled($get('../../event_id')) && ExtraService::where('event_id', $get('../../event_id'))->where('is_active', true)->exists())
                                        ->columnSpanFull(),
                                ])
                                ->itemLabel(
                                    fn(array $state): ?string =>
                                    !empty($state['first_name']) && !empty($state['last_name'])
                                        ? '👤 ' . $state['first_name'] . ' ' . $state['last_name']
                                        : __('booking.attendee_new')
                                )
                                ->defaultItems(fn(Get $get) => (int) $get('quantity') ?: 1)
                                ->columnSpanFull()
                                ->live()
                                ->afterStateUpdated(function (Set $set, Get $get) {
                                    if ($get('copy_contact_to_all')) {
                                        self::applyFirstAttendeeContact($set, $get);
                                    }
                                })
                                ->cloneable(false)
                                ->addable(false)
                                ->deletable(false)
                                ->reorderable(false),
                        ]),

                    Step::make(__('booking.wizard.steps.review'))
                        ->icon('heroicon-o-receipt-percent')
                        ->description(__('booking.wizard.steps.review_description'))
                        ->schema([
                            Section::make(__('booking.wizard.summary_heading'))
                                ->schema([
                                    Placeholder::make('booking_summary')
                                        ->label('')
                                        ->content(fn(Get $get) => self::renderSummary($get)),
                                ]),

                            Section::make(__('booking.sections.pricing'))
                                ->schema([
                                    TextInput::make('ticket_price')
                                        ->label(__('booking.fields.ticket_price'))
                                        ->numeric()
                                        ->prefix('OMR')
                                        ->disabled()
                                        ->dehydrated()
                                        ->live()
                                        ->default(0),

                                    TextInput::make('services_price')
                                        ->label(__('booking.fields.services_price'))
                                        ->numeric()
                                        ->prefix('OMR')
                                        ->disabled()
                                        ->dehydrated()
                                        ->live()
                                        ->default(0),

                                    TextInput::make('total_price')
                                        ->label(__('booking.fields.total_price'))
                                        ->numeric()
                                        ->prefix('OMR')
                                        ->disabled()
                                        ->dehydrated()
                                        ->live()
                                        ->default(0)
                                        ->extraInputAttributes(['class' => 'text-lg font-bold']),
                                ])
                                ->columns(3),

                            Section::make(__('booking.payments.title'))
                                ->schema([
                                    Select::make('payment_method')
                                        ->label(__('booking.payments.fields.method'))
                                        ->options(__('booking.payments.methods'))
                                        ->default('cash')
                                        ->required()
                                        ->native(false)
                                        ->extraInputAttributes(['class' => 'text-lg']),

                                    TextInput::make('payment_amount')
                                        ->label(__('booking.wizard.amount_received'))
                                        ->numeric()
                                        ->prefix('OMR')
                                        ->default(fn(Get $get) => $get('total_price') ?: 0)
                                        ->live(onBlur: true)
                                        ->extraInputAttributes(['class' => 'text-lg']),

                                    TextInput::make('payment_reference')
                                        ->label(__('booking.payments.fields.reference'))
                                        ->required()
                                        ->maxLength(255),

                                    Textarea::make('payment_notes')
                                        ->label(__('booking.payments.fields.notes'))
                                        ->rows(2),
                                ])
                                ->columns(2),
                        ]),
                ])
                    ->submitAction(new HtmlString(Blade::render(<<<'BLADE'
                        <x-filament::button type="submit" size="lg" icon="heroicon-o-check-circle" color="success">
                            {{ __('booking.wizard.confirm_button') }}
                        </x-filament::button>
                    BLADE)))
                    ->persistStepInQueryString(),
            ])
            ->statePath('data');
    }

    // A slot is bookable if active, and—when the chosen date is today—its
    // start time hasn't already passed.
    protected static function isSlotBookable(TimeSlot $slot, ?string $date): bool
    {
        if ($date && \Carbon\Carbon::parse($date)->isToday()) {
            return $slot->start_time->format('H:i') > now()->format('H:i');
        }

        return true;
    }

    protected static function syncAttendees(Set $set, Get $get, int $quantity): void
    {
        $quantity = max(1, $quantity);
        $attendees = $get('attendees') ?? [];
        $count = count($attendees);

        if ($count < $quantity) {
            for ($i = $count; $i < $quantity; $i++) {
                $attendees[] = [];
            }
        } elseif ($count > $quantity) {
            $attendees = array_slice($attendees, 0, $quantity);
        }

        $set('attendees', $attendees);
    }

    protected static function applyFirstAttendeeContact(Set $set, Get $get): void
    {
        $attendees = $get('attendees') ?? [];

        if (count($attendees) < 2) {
            return;
        }

        $keys = array_keys($attendees);
        $firstKey = $keys[0];
        $email = $attendees[$firstKey]['email'] ?? null;
        $phone = $attendees[$firstKey]['phone'] ?? null;

        foreach ($keys as $key) {
            if ($key === $firstKey) {
                continue;
            }

            $attendees[$key]['email'] = $email;
            $attendees[$key]['phone'] = $phone;
        }

        $set('attendees', $attendees);
    }

    protected static function calculateTotal(Set $set, Get $get, string $prefix = ''): void
    {
        $ticketPrice = 0;
        $servicesPrice = 0;

        $attendees = $get($prefix . 'attendees') ?? [];

        $serviceIds = collect($attendees)->flatMap(fn($a) => $a['extra_service_ids'] ?? [])->unique();
        $services = ExtraService::whereIn('id', $serviceIds)->get()->keyBy('id');

        foreach ($attendees as $attendee) {
            if (isset($attendee['ticket_price'])) {
                $ticketPrice += (float) $attendee['ticket_price'];
            }

            foreach ($attendee['extra_service_ids'] ?? [] as $serviceId) {
                if (isset($services[$serviceId])) {
                    $servicesPrice += (float) $services[$serviceId]->price;
                }
            }
        }

        $set($prefix . 'ticket_price', number_format($ticketPrice, 2, '.', ''));
        $set($prefix . 'services_price', number_format($servicesPrice, 2, '.', ''));
        $set($prefix . 'total_price', number_format($ticketPrice + $servicesPrice, 2, '.', ''));
    }

    protected static function renderSummary(Get $get): HtmlString
    {
        $event = $get('event_id') ? Event::find($get('event_id')) : null;
        $timeSlot = $get('time_slot_id') ? TimeSlot::find($get('time_slot_id')) : null;
        $attendees = collect($get('attendees') ?? []);
        $serviceIds = $attendees->flatMap(fn($a) => $a['extra_service_ids'] ?? [])->unique();
        $services = ExtraService::whereIn('id', $serviceIds)->get();

        $rows = [];
        $rows[] = ['label' => __('booking.fields.event'), 'value' => $event?->getTranslation('title', app()->getLocale()) ?? '—'];
        $rows[] = ['label' => __('booking.fields.event_date'), 'value' => $get('event_date') ?: '—'];
        $rows[] = ['label' => __('booking.fields.time_slot'), 'value' => $timeSlot?->getTimeRange() ?? '—'];
        $rows[] = ['label' => __('booking.fields.quantity'), 'value' => $get('quantity') ?: '—'];

        $attendeeNames = $attendees
            ->filter(fn($a) => !empty($a['first_name']))
            ->map(fn($a) => trim(($a['first_name'] ?? '') . ' ' . ($a['last_name'] ?? '')))
            ->implode(', ');

        $rows[] = ['label' => __('booking.wizard.attendee_names'), 'value' => $attendeeNames ?: '—'];

        $serviceNames = $services->map(fn($s) => $s->getTranslation('name', app()->getLocale()))->implode(', ');
        $rows[] = ['label' => __('booking.fields.extra_services'), 'value' => $serviceNames ?: '—'];

        $html = '<div class="grid grid-cols-1 gap-1 text-sm">';
        foreach ($rows as $row) {
            $html .= '<div class="flex justify-between gap-4 py-1 border-b border-gray-100 dark:border-gray-700">'
                . '<span class="font-medium text-gray-500 dark:text-gray-400">' . e($row['label']) . '</span>'
                . '<span class="text-gray-800 dark:text-gray-100 text-right">' . e($row['value']) . '</span>'
                . '</div>';
        }
        $html .= '</div>';

        return new HtmlString($html);
    }

    public function createBooking(): void
    {
        $data = $this->form->getState();
        $quantity = (int) ($data['quantity'] ?? 1);
        $attendeesData = $data['attendees'] ?? [];

        try {
            $booking = DB::transaction(function () use ($data, $attendeesData, $quantity) {
                // Admin bookings can exceed remaining capacity/stock (overbooking is
                // allowed from the admin side) — only existence is checked here, not
                // isAvailable(), which is reserved for the customer-facing flow.
                $timeSlot = TimeSlot::where('id', $data['time_slot_id'] ?? null)->lockForUpdate()->first();

                if (!$timeSlot) {
                    throw new Exception(__('booking.wizard.notifications.no_capacity'));
                }

                $ticketTypeIds = collect($attendeesData)->pluck('ticket_type_id')->filter()->unique();
                foreach ($ticketTypeIds as $ticketTypeId) {
                    $ticketType = TicketType::where('id', $ticketTypeId)->lockForUpdate()->first();
                    if (!$ticketType) {
                        throw new Exception(__('booking.wizard.notifications.ticket_unavailable'));
                    }
                }

                $booking = Booking::create([
                    'event_id' => $data['event_id'],
                    'time_slot_id' => $data['time_slot_id'],
                    'ticket_type_id' => collect($attendeesData)->pluck('ticket_type_id')->filter()->first(),
                    'event_date' => $data['event_date'],
                    'quantity' => $quantity,
                    'ticket_price' => $data['ticket_price'] ?? 0,
                    'services_price' => $data['services_price'] ?? 0,
                    'total_price' => $data['total_price'] ?? 0,
                    'source' => 'admin',
                    'created_by' => auth()->id(),
                    'status' => 'pending',
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);

                foreach ($attendeesData as $attendee) {
                    $booking->attendees()->create($attendee);
                }

                $serviceCounts = collect($attendeesData)->flatMap(fn($a) => $a['extra_service_ids'] ?? [])->countBy();

                if ($serviceCounts->isNotEmpty()) {
                    $services = ExtraService::whereIn('id', $serviceCounts->keys())->lockForUpdate()->get()->keyBy('id');

                    $syncData = [];
                    foreach ($serviceCounts as $serviceId => $count) {
                        $service = $services[$serviceId] ?? null;
                        if (!$service) {
                            throw new Exception(__('booking.wizard.notifications.service_unavailable'));
                        }
                        $syncData[$serviceId] = [
                            'quantity' => $count,
                            'price' => $service->price,
                        ];
                    }
                    $booking->extraServices()->sync($syncData);
                }

                $booking->confirm();

                if ((float) ($data['payment_amount'] ?? 0) > 0) {
                    $booking->payments()->create([
                        'payment_method' => $data['payment_method'] ?? 'cash',
                        'amount' => $data['payment_amount'],
                        'reference' => $data['payment_reference'] ?? null,
                        'notes' => $data['payment_notes'] ?? null,
                        'recorded_by' => auth()->id(),
                    ]);
                }

                return $booking;
            });
        } catch (Exception $e) {
            Notification::make()
                ->danger()
                ->title($e->getMessage())
                ->send();

            return;
        }

        $this->createdBooking = $booking->fresh(['event', 'timeSlot', 'ticketType', 'attendees', 'extraServices', 'payments']);

        Notification::make()
            ->success()
            ->title(__('booking.wizard.notifications.created'))
            ->body(__('booking.wizard.notifications.created_body', ['reference' => $this->createdBooking->booking_reference]))
            ->send();
    }

    public function startNewBooking(): void
    {
        $this->resetWizardForm();
    }

    public function getViewBookingUrl(): ?string
    {
        return $this->createdBooking
            ? BookingResource::getUrl('view', ['record' => $this->createdBooking])
            : null;
    }
}
