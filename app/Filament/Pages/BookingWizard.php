<?php

namespace App\Filament\Pages;

use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use App\Models\BookingSetting;
use App\Models\Event;
use App\Models\ExtraService;
use App\Models\TicketType;
use App\Models\TimeSlot;
use App\Services\Printing\ThermalPrintService;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Carbon\Carbon;
use Exception;
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
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class BookingWizard extends Page implements HasForms
{
    use HasPageShield;
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-device-tablet';

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
        $this->createdBooking = null;

        $this->form->fill([
            'event_date' => now()->toDateString(),
            'ticket_quantities' => [],
            'attendees' => [],
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
                                        ->options(fn () => Event::where('status', 'published')
                                            ->get()
                                            ->mapWithKeys(fn ($event) => [
                                                $event->id => $event->getTranslation('title', app()->getLocale()),
                                            ]))
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->live()
                                        ->native(false)
                                        ->extraInputAttributes(['class' => 'text-lg'])
                                        ->afterStateUpdated(function (Set $set, Get $get) {
                                            $set('time_slot_id', null);
                                            $set('ticket_quantities', []);
                                            $set('attendees', []);
                                            self::autoSelectTimeSlot($set, $get);
                                        }),

                                    DatePicker::make('event_date')
                                        ->label(__('booking.fields.event_date'))
                                        ->required()
                                        ->native(false)
                                        ->displayFormat('Y-m-d')
                                        ->default(fn () => now()->toDateString())
                                        ->live()
                                        ->extraInputAttributes(['class' => 'text-lg'])
                                        ->afterStateUpdated(function (Set $set, Get $get) {
                                            $set('time_slot_id', null);
                                            self::autoSelectTimeSlot($set, $get);
                                        }),

                                    Select::make('time_slot_id')
                                        ->label(__('booking.fields.time_slot'))
                                        ->options(function (Get $get) {
                                            if (! $get('event_id') || ! $get('event_date')) {
                                                return [];
                                            }

                                            return TimeSlot::where('event_id', $get('event_id'))
                                                ->where('is_active', true)
                                                ->where('date', $get('event_date'))
                                                ->get()
                                                ->mapWithKeys(function ($slot) use ($get) {
                                                    $label = $slot->getTimeRange().' ('.$slot->getRemainingCapacity().' '.__('booking.wizard.available').')';

                                                    if (! self::isSlotBookable($slot, $get('event_date'))) {
                                                        $label .= ' — '.__('booking.wizard.past_slot');
                                                    }

                                                    return [$slot->id => $label];
                                                });
                                        })
                                        ->required()
                                        ->native(false)
                                        ->live()
                                        ->extraInputAttributes(['class' => 'text-lg']),
                                ])
                                ->columns(2),

                            Section::make(__('booking.wizard.select_tickets'))
                                ->visible(fn (Get $get) => filled($get('event_id')))
                                ->schema(function (Get $get) {
                                    $eventId = $get('event_id');

                                    if (! $eventId) {
                                        return [];
                                    }

                                    $ticketTypes = TicketType::where('event_id', $eventId)
                                        ->where('is_active', true)
                                        ->orderBy('id')
                                        ->get();

                                    return $ticketTypes->map(fn (TicketType $ticketType) => self::ticketQuantityCard($ticketType))->all();
                                })
                                ->columns(1),

                            Section::make()
                                ->visible(fn (Get $get) => collect($get('ticket_quantities') ?? [])->sum() > 0)
                                ->schema([
                                    Placeholder::make('tickets_running_total')
                                        ->label('')
                                        ->content(fn (Get $get) => self::renderTicketsRunningTotal($get)),
                                ]),
                        ]),

                    Step::make(__('booking.wizard.steps.attendees'))
                        ->icon('heroicon-o-users')
                        ->description(fn (Get $get) => __('booking.attendee_details_description', [
                            'count' => collect($get('ticket_quantities') ?? [])->sum() ?: 1,
                        ]))
                        ->schema([
                            Grid::make(['default' => 1, 'lg' => 3])
                                ->schema([
                                    Group::make(function (Get $get) {
                                        $components = [
                                            Checkbox::make('copy_contact_to_all')
                                                ->label(__('booking.wizard.copy_contact_to_all'))
                                                ->live()
                                                ->dehydrated(false)
                                                ->visible(fn (Get $get) => collect($get('ticket_quantities') ?? [])->sum() > 1)
                                                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                                    if ($state) {
                                                        self::applyMainAttendeeNameToAll($set, $get);
                                                    }
                                                }),
                                        ];

                                        $eventId = $get('event_id');
                                        $quantities = collect($get('ticket_quantities') ?? []);

                                        if ($eventId) {
                                            $ticketTypes = TicketType::where('event_id', $eventId)
                                                ->where('is_active', true)
                                                ->orderBy('id')
                                                ->get();

                                            foreach ($ticketTypes as $ticketType) {
                                                $qty = (int) ($quantities->get($ticketType->id) ?? 0);

                                                if ($qty <= 0) {
                                                    continue;
                                                }

                                                $components[] = Section::make(
                                                    $ticketType->getTranslation('name', app()->getLocale())." ({$qty})"
                                                )->schema([
                                                    self::attendeeRepeater($ticketType->id, $qty),
                                                ]);
                                            }
                                        }

                                        return $components;
                                    })->columnSpan(['lg' => 2]),

                                    Section::make(__('booking.wizard.summary_heading'))
                                        ->columnSpan(['lg' => 1])
                                        ->extraAttributes(['class' => 'lg:sticky lg:top-20 self-start'])
                                        ->schema([
                                            Placeholder::make('express_summary')
                                                ->label('')
                                                ->content(fn (Get $get) => self::renderExpressSummary($get)),
                                        ]),
                                ]),
                        ]),

                    Step::make(__('booking.wizard.steps.review'))
                        ->icon('heroicon-o-receipt-percent')
                        ->description(__('booking.wizard.steps.review_description'))
                        ->schema([
                            Section::make(__('booking.wizard.summary_heading'))
                                ->schema([
                                    Placeholder::make('booking_summary')
                                        ->label('')
                                        ->content(fn (Get $get) => self::renderSummary($get)),
                                ]),

                            Section::make(__('booking.sections.pricing'))
                                ->schema([
                                    TextInput::make('ticket_price')
                                        ->label(__('booking.fields.ticket_price'))
                                        //->numeric()
                                        ->step(0.001)
                                        ->prefix('OMR')
                                        ->disabled()
                                        ->dehydrated()
                                        ->live()
                                        ->default(0)
                                        ->formatStateUsing(fn ($state) => number_format((float) ($state ?? 0), 3, '.', '')),

                                    TextInput::make('services_price')
                                        ->label(__('booking.fields.services_price'))
                                        //->numeric()
                                        ->step(0.001)
                                        ->prefix('OMR')
                                        ->disabled()
                                        ->dehydrated()
                                        ->live()
                                        ->default(0)
                                        ->formatStateUsing(fn ($state) => number_format((float) ($state ?? 0), 3, '.', '')),

                                    TextInput::make('total_price')
                                        ->label(__('booking.fields.total_price'))
                                        //->numeric()
                                        ->step(0.001)
                                        ->prefix('OMR')
                                        ->disabled()
                                        ->dehydrated()
                                        ->live()
                                        ->default(0)
                                        ->formatStateUsing(fn ($state) => number_format((float) ($state ?? 0), 3, '.', ''))
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

                                    Hidden::make('payment_amount_is_manual')
                                        ->default(false)
                                        ->dehydrated(false),

                                    TextInput::make('payment_amount')
                                        ->label(__('booking.wizard.amount_received'))
                                        //->numeric()
                                        ->prefix('OMR')
                                        ->default(0)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn (Set $set) => $set('payment_amount_is_manual', true))
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
        if ($date && Carbon::parse($date)->isToday()) {
            return $slot->start_time->format('H:i') > now()->format('H:i');
        }

        return true;
    }

    // Auto-selects the time slot when only one is available for the chosen
    // event/date, saving admins a click on the overwhelmingly common case.
    protected static function autoSelectTimeSlot(Set $set, Get $get): void
    {
        $eventId = $get('event_id');
        $eventDate = $get('event_date');

        if (! $eventId || ! $eventDate) {
            return;
        }

        $slots = TimeSlot::where('event_id', $eventId)
            ->where('is_active', true)
            ->where('date', $eventDate)
            ->get();

        if ($slots->count() === 1) {
            $set('time_slot_id', $slots->first()->id);
        }
    }

    protected static function ticketQuantityCard(TicketType $ticketType): Grid
    {
        $locale = app()->getLocale();
        $key = "ticket_quantities.{$ticketType->id}";

        return Grid::make(1)
            ->extraAttributes(fn (Get $get) => [
                'class' => 'rounded-2xl border-2 p-4 transition-colors '
                    .(((int) ($get($key) ?? 0)) > 0
                        ? 'border-primary-500 bg-primary-50 dark:bg-primary-500/10'
                        : 'border-gray-200 dark:border-gray-700'),
            ])
            ->schema([
                Placeholder::make("ticket_type_{$ticketType->id}_info")
                    ->label('')
                    ->content(fn () => new HtmlString(
                        '<div class="flex items-center justify-between gap-4">'
                        .'<div>'
                        .'<div class="font-bold text-base">'.e($ticketType->getTranslation('name', $locale)).'</div>'
                        .'<div class="text-sm text-gray-500 dark:text-gray-400">OMR '.number_format($ticketType->price, 3)
                        .' &middot; '.$ticketType->getRemainingQuantity().' '.e(__('booking.wizard.available')).'</div>'
                        .'</div>'
                        .'</div>'
                    )),

                TextInput::make($key)
                    ->label(__('booking.fields.quantity'))
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->live(onBlur: true)
                    ->extraInputAttributes(['class' => 'text-lg text-center'])
                    ->prefixAction(
                        Action::make("decrease_{$ticketType->id}")
                            ->icon('heroicon-o-minus')
                            ->action(function (Set $set, Get $get) use ($key) {
                                $new = max(0, ((int) $get($key)) - 1);
                                $set($key, $new);
                                self::syncAttendeesFromQuantities($set, $get);
                            })
                    )
                    ->suffixAction(
                        Action::make("increase_{$ticketType->id}")
                            ->icon('heroicon-o-plus')
                            ->action(function (Set $set, Get $get) use ($key) {
                                $set($key, ((int) $get($key)) + 1);
                                self::syncAttendeesFromQuantities($set, $get);
                            })
                    )
                    ->afterStateUpdated(function (Set $set, Get $get, $state) use ($key) {
                        $set($key, max(0, (int) $state));
                        self::syncAttendeesFromQuantities($set, $get);
                    }),
            ]);
    }

    protected static function attendeeRepeater(int $ticketTypeId, int $qty): Repeater
    {
        return Repeater::make("attendees.{$ticketTypeId}")
            ->label('')
            ->schema([
                Hidden::make('ticket_type_id'),
                Hidden::make('ticket_price'),
                Hidden::make('_number'),
                Hidden::make('_is_main'),

                Grid::make(2)
                    ->schema([
                        TextInput::make('first_name')
                            ->label(__('booking.fields.first_name'))
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->extraInputAttributes(['class' => 'text-lg'])
                            ->placeholder(__('booking.placeholders.first_name'))
                            ->autofocus(fn (Get $get) => (bool) $get('_is_main'))
                            ->disabled(fn (Get $get) => $get('../../copy_contact_to_all') && ! $get('_is_main'))
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                if ($get('_is_main') && $get('../../copy_contact_to_all')) {
                                    self::applyMainAttendeeNameToAll($set, $get, '../../');
                                }
                            }),

                        TextInput::make('last_name')
                            ->label(__('booking.fields.last_name'))
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->extraInputAttributes(['class' => 'text-lg'])
                            ->placeholder(__('booking.placeholders.last_name'))
                            ->disabled(fn (Get $get) => $get('../../copy_contact_to_all') && ! $get('_is_main'))
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                if ($get('_is_main') && $get('../../copy_contact_to_all')) {
                                    self::applyMainAttendeeNameToAll($set, $get, '../../');
                                }
                            }),
                    ]),

                Grid::make(2)
                    ->schema([
                        TextInput::make('email')
                            ->label(__('booking.fields.email'))
                            ->email()
                            ->default('booking@razatfarm.gov.om')
                            ->maxLength(255)
                            ->extraInputAttributes(['class' => 'text-lg'])
                            ->placeholder(__('booking.placeholders.email'))
                            ->helperText(__('booking.wizard.main_contact_email_helper'))
                            ->visible(fn (Get $get) => (bool) $get('_is_main')),

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
                            ->minDate(fn () => now()->subYears((int) BookingSetting::get('max_attendee_age_years', 75)))
                            ->placeholder(__('booking.placeholders.date'))
                            ->visible(fn () => (bool) BookingSetting::get('show_date_of_birth', true)),

                        Select::make('gender')
                            ->label(__('booking.fields.gender'))
                            ->options(__('booking.options.gender'))
                            ->placeholder(__('booking.placeholders.gender'))
                            ->visible(fn () => (bool) BookingSetting::get('show_gender', true)),

                        Select::make('nationality')
                            ->label(__('booking.fields.nationality'))
                            ->required()
                            ->options(__('booking.options.nationality'))
                            ->placeholder(__('booking.placeholders.nationality'))
                            ->visible(fn () => (bool) BookingSetting::get('show_nationality', true)),
                    ]),

                Grid::make(1)
                    ->visible(fn () => (bool) BookingSetting::get('show_identity_number', true))
                    ->schema([
                        TextInput::make('identity_number')
                            ->label(__('booking.fields.identity_number'))
                            ->maxLength(50)
                            ->placeholder(__('booking.placeholders.identity_number')),
                    ]),

                CheckboxList::make('extra_service_ids')
                    ->label(__('booking.fields.extra_services'))
                    ->options(function (Get $get) {
                        $eventId = $get('../../event_id');
                        if (! $eventId) {
                            return [];
                        }

                        return ExtraService::where('event_id', $eventId)
                            ->where('is_active', true)
                            ->get()
                            ->mapWithKeys(fn ($service) => [
                                $service->id => $service->getTranslation('name', app()->getLocale()).
                                    ' — OMR '.number_format($service->price, 3),
                            ]);
                    })
                    ->columns(2)
                    ->gridDirection('row')
                    ->live()
                    ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateTotal($set, $get, '../../'))
                    ->visible(fn (Get $get) => filled($get('../../event_id')) && ExtraService::where('event_id', $get('../../event_id'))->where('is_active', true)->exists())
                    ->columnSpanFull(),
            ])
            ->itemLabel(fn (array $state): ?string => trim(
                __('booking.attendee_id', ['id' => $state['_number'] ?? '?'])
                .(($state['_is_main'] ?? false) ? __('booking.wizard.main_contact_suffix') : '')
            ))
            ->defaultItems($qty)
            ->columnSpanFull()
            ->cloneable(false)
            ->addable(false)
            ->deletable(false)
            ->reorderable(false);
    }

    // Rebuilds the nested `attendees` array (keyed by ticket_type_id) to match
    // `ticket_quantities`, preserving already-entered rows by index and
    // re-numbering every attendee sequentially across all groups.
    protected static function syncAttendeesFromQuantities(Set $set, Get $get, string $prefix = ''): void
    {
        $eventId = $get($prefix.'event_id');
        $quantities = collect($get($prefix.'ticket_quantities') ?? []);
        $existingAttendees = $get($prefix.'attendees') ?? [];

        $ticketTypes = $eventId
            ? TicketType::where('event_id', $eventId)->where('is_active', true)->orderBy('id')->get()
            : collect();

        $attendees = [];
        $number = 0;

        foreach ($ticketTypes as $ticketType) {
            $qty = (int) ($quantities->get($ticketType->id) ?? 0);

            if ($qty <= 0) {
                continue;
            }

            $existingGroup = $existingAttendees[$ticketType->id] ?? [];
            $group = [];

            for ($i = 0; $i < $qty; $i++) {
                $row = $existingGroup[$i] ?? [];
                $number++;
                $isMain = $number === 1;

                $row['ticket_type_id'] = $ticketType->id;
                $row['ticket_price'] = $ticketType->price;
                $row['_number'] = $number;
                $row['_is_main'] = $isMain;

                if ($isMain && ! array_key_exists('nationality', $row)) {
                    $row['nationality'] = 'OM';
                }

                $group[] = $row;
            }

            $attendees[$ticketType->id] = $group;
        }

        $set($prefix.'attendees', $attendees);

        self::calculateTotal($set, $get, $prefix);

        if ($get($prefix.'copy_contact_to_all')) {
            self::applyMainAttendeeNameToAll($set, $get, $prefix);
        }
    }

    // Copies the main (first) attendee's first/last name onto every other
    // attendee across all ticket-type groups.
    protected static function applyMainAttendeeNameToAll(Set $set, Get $get, string $prefix = ''): void
    {
        $attendees = $get($prefix.'attendees') ?? [];

        $mainFirstName = null;
        $mainLastName = null;

        foreach ($attendees as $group) {
            foreach ($group as $row) {
                if (! empty($row['_is_main'])) {
                    $mainFirstName = $row['first_name'] ?? null;
                    $mainLastName = $row['last_name'] ?? null;
                    break 2;
                }
            }
        }

        if ($mainFirstName === null && $mainLastName === null) {
            return;
        }

        foreach ($attendees as $ticketTypeId => $group) {
            foreach ($group as $index => $row) {
                if (! empty($row['_is_main'])) {
                    continue;
                }

                $attendees[$ticketTypeId][$index]['first_name'] = $mainFirstName;
                $attendees[$ticketTypeId][$index]['last_name'] = $mainLastName;
            }
        }

        $set($prefix.'attendees', $attendees);
    }

    protected static function calculateTotal(Set $set, Get $get, string $prefix = ''): void
    {
        $ticketPrice = 0;
        $servicesPrice = 0;

        $attendees = collect($get($prefix.'attendees') ?? [])->flatMap(fn ($group) => $group);

        $serviceIds = $attendees->flatMap(fn ($a) => $a['extra_service_ids'] ?? [])->unique();
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

        $set($prefix.'ticket_price', number_format($ticketPrice, 3, '.', ''));
        $set($prefix.'services_price', number_format($servicesPrice, 3, '.', ''));
        $set($prefix.'total_price', number_format($ticketPrice + $servicesPrice, 3, '.', ''));

        if (! $get($prefix.'payment_amount_is_manual')) {
            $set($prefix.'payment_amount', number_format($ticketPrice + $servicesPrice, 3, '.', ''));
        }
    }

    protected static function renderTicketsRunningTotal(Get $get): HtmlString
    {
        $count = collect($get('ticket_quantities') ?? [])->sum();

        return new HtmlString(
            '<div class="flex items-center justify-between">'
            .'<span class="font-medium">'.e(__('booking.wizard.tickets_selected', ['count' => $count])).'</span>'
            .'<span class="text-xl font-bold">OMR '.number_format((float) ($get('ticket_price') ?: 0), 3).'</span>'
            .'</div>'
        );
    }

    protected static function renderExpressSummary(Get $get): HtmlString
    {
        $totalTickets = collect($get('ticket_quantities') ?? [])->sum();

        $rows = [
            ['label' => __('booking.wizard.express_summary.total_tickets'), 'value' => (string) $totalTickets],
            ['label' => __('booking.wizard.express_summary.subtotal'), 'value' => 'OMR '.number_format((float) ($get('ticket_price') ?: 0), 3)],
            ['label' => __('booking.wizard.express_summary.services'), 'value' => 'OMR '.number_format((float) ($get('services_price') ?: 0), 3)],
        ];

        $html = '<div class="grid grid-cols-1 gap-1 text-sm">';
        foreach ($rows as $row) {
            $html .= '<div class="flex justify-between gap-4 py-1 border-b border-gray-100 dark:border-gray-700">'
                .'<span class="font-medium text-gray-500 dark:text-gray-400">'.e($row['label']).'</span>'
                .'<span class="text-gray-800 dark:text-gray-100 text-right">'.e($row['value']).'</span>'
                .'</div>';
        }
        $html .= '<div class="flex justify-between gap-4 pt-2 mt-1">'
            .'<span class="font-bold">'.e(__('booking.wizard.express_summary.total')).'</span>'
            .'<span class="text-lg font-bold">OMR '.number_format((float) ($get('total_price') ?: 0), 3).'</span>'
            .'</div>';
        $html .= '</div>';

        return new HtmlString($html);
    }

    protected static function renderSummary(Get $get): HtmlString
    {
        $event = $get('event_id') ? Event::find($get('event_id')) : null;
        $timeSlot = $get('time_slot_id') ? TimeSlot::find($get('time_slot_id')) : null;
        $attendees = collect($get('attendees') ?? [])->flatMap(fn ($group) => $group);
        $serviceIds = $attendees->flatMap(fn ($a) => $a['extra_service_ids'] ?? [])->unique();
        $services = ExtraService::whereIn('id', $serviceIds)->get();

        $quantities = collect($get('ticket_quantities') ?? [])->filter(fn ($qty) => (int) $qty > 0);
        $ticketTypes = TicketType::whereIn('id', $quantities->keys())->get()->keyBy('id');

        $rows = [];
        $rows[] = ['label' => __('booking.fields.event'), 'value' => $event?->getTranslation('title', app()->getLocale()) ?? '—'];
        $rows[] = ['label' => __('booking.fields.event_date'), 'value' => $get('event_date') ?: '—'];
        $rows[] = ['label' => __('booking.fields.time_slot'), 'value' => $timeSlot?->getTimeRange() ?? '—'];
        $rows[] = ['label' => __('booking.fields.quantity'), 'value' => $attendees->count() ?: '—'];

        $ticketBreakdown = $quantities->map(function ($qty, $typeId) use ($ticketTypes) {
            $ticketType = $ticketTypes->get($typeId);

            return $ticketType ? $ticketType->getTranslation('name', app()->getLocale()).' × '.$qty : null;
        })->filter()->implode(', ');
        $rows[] = ['label' => __('booking.wizard.tickets_heading'), 'value' => $ticketBreakdown ?: '—'];

        $attendeeNames = $attendees
            ->filter(fn ($a) => ! empty($a['first_name']))
            ->map(fn ($a) => trim(($a['first_name'] ?? '').' '.($a['last_name'] ?? '')))
            ->implode(', ');

        $rows[] = ['label' => __('booking.wizard.attendee_names'), 'value' => $attendeeNames ?: '—'];

        $serviceNames = $services->map(fn ($s) => $s->getTranslation('name', app()->getLocale()))->implode(', ');
        $rows[] = ['label' => __('booking.fields.extra_services'), 'value' => $serviceNames ?: '—'];

        $html = '<div class="grid grid-cols-1 gap-1 text-sm">';
        foreach ($rows as $row) {
            $html .= '<div class="flex justify-between gap-4 py-1 border-b border-gray-100 dark:border-gray-700">'
                .'<span class="font-medium text-gray-500 dark:text-gray-400">'.e($row['label']).'</span>'
                .'<span class="text-gray-800 dark:text-gray-100 text-right">'.e($row['value']).'</span>'
                .'</div>';
        }
        $html .= '</div>';

        return new HtmlString($html);
    }

    public function createBooking(): void
    {
        $data = $this->form->getState();

        $attendeesData = collect($data['attendees'] ?? [])
            ->flatMap(fn ($group) => $group)
            ->map(fn ($attendee) => Arr::except($attendee, ['_number', '_is_main']))
            ->values()
            ->all();

        $quantity = count($attendeesData);
        $minQty = (int) BookingSetting::get('min_tickets_per_booking', 1);

        if ($quantity < 1) {
            Notification::make()
                ->danger()
                ->title(__('booking.wizard.no_tickets_selected'))
                ->send();

            return;
        }

        if ($quantity < $minQty) {
            Notification::make()
                ->danger()
                ->title(__('booking.wizard.min_tickets_required', ['min' => $minQty]))
                ->send();

            return;
        }

        try {
            $booking = DB::transaction(function () use ($data, $attendeesData, $quantity) {
                // Admin bookings can exceed remaining capacity/stock (overbooking is
                // allowed from the admin side) — only existence is checked here, not
                // isAvailable(), which is reserved for the customer-facing flow.
                $timeSlot = TimeSlot::where('id', $data['time_slot_id'] ?? null)->lockForUpdate()->first();

                if (! $timeSlot) {
                    throw new Exception(__('booking.wizard.notifications.no_capacity'));
                }

                $ticketTypeIds = collect($attendeesData)->pluck('ticket_type_id')->filter()->unique();
                foreach ($ticketTypeIds as $ticketTypeId) {
                    $ticketType = TicketType::where('id', $ticketTypeId)->lockForUpdate()->first();
                    if (! $ticketType) {
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
                    if (empty($attendee['email'])) {
                        $attendee['email'] = 'booking@razatfarm.gov.om';
                    }
                    $booking->attendees()->create($attendee);
                }

                $serviceCounts = collect($attendeesData)->flatMap(fn ($a) => $a['extra_service_ids'] ?? [])->countBy();

                if ($serviceCounts->isNotEmpty()) {
                    $services = ExtraService::whereIn('id', $serviceCounts->keys())->lockForUpdate()->get()->keyBy('id');

                    $syncData = [];
                    foreach ($serviceCounts as $serviceId => $count) {
                        $service = $services[$serviceId] ?? null;
                        if (! $service) {
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

    public function printAttendeeTickets(): void
    {
        if (! $this->createdBooking) {
            return;
        }

        try {
            app(ThermalPrintService::class)->enqueueAttendeeTickets($this->createdBooking);

            Notification::make()
                ->success()
                ->title(__('booking.notifications.tickets_queued'))
                ->send();
        } catch (Exception $e) {
            Notification::make()
                ->danger()
                ->title(__('booking.notifications.tickets_queue_failed'))
                ->body($e->getMessage())
                ->send();
        }
    }

    public function printReceipt(): void
    {
        if (! $this->createdBooking) {
            return;
        }

        try {
            app(ThermalPrintService::class)->enqueueReceipt($this->createdBooking);

            Notification::make()
                ->success()
                ->title(__('booking.notifications.receipt_queued'))
                ->send();
        } catch (Exception $e) {
            Notification::make()
                ->danger()
                ->title(__('booking.notifications.receipt_queue_failed'))
                ->body($e->getMessage())
                ->send();
        }
    }

    public function getViewBookingUrl(): ?string
    {
        return $this->createdBooking
            ? BookingResource::getUrl('view', ['record' => $this->createdBooking])
            : null;
    }
}
