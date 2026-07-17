<?php

namespace App\Filament\Pages\Settings;

use App\Models\BookingSetting;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class GeneralSettings extends Page implements HasForms
{
    use InteractsWithForms;
    use HasPageShield;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string | \UnitEnum | null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.settings.general-settings';

    public ?array $data = [];

    public function getTitle(): string
    {
        return __('general_settings.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('general_settings.title');
    }

    public function mount(): void
    {
        $this->form->fill([
            'site_name_en' => BookingSetting::get('site_name_en', 'Bookings'),
            'site_name_ar' => BookingSetting::get('site_name_ar', 'الحجوزات'),
            'timezone' => BookingSetting::get('timezone', 'Asia/Muscat'),
            'currency_code' => BookingSetting::get('currency_code', 'OMR'),
            'currency_symbol' => BookingSetting::get('currency_symbol', 'OMR'),
            'currency_icon' => BookingSetting::get('currency_icon'),

            'site_logo' => BookingSetting::get('site_logo'),
            'app_logo' => BookingSetting::get('app_logo'),
            'favicon' => BookingSetting::get('favicon'),
            'primary_color' => BookingSetting::get('primary_color', '#05602b'),
            'secondary_color' => BookingSetting::get('secondary_color', '#0da74c'),
            'panel_primary_color' => BookingSetting::get('panel_primary_color', '#16a34a'),

            'min_tickets_per_booking' => (string) BookingSetting::get('min_tickets_per_booking', 1),
            'max_tickets_per_booking' => (string) BookingSetting::get('max_tickets_per_booking', 10),
            'max_attendee_age_years' => (string) BookingSetting::get('max_attendee_age_years', 75),
            'pending_booking_expiry_minutes' => (string) BookingSetting::get('pending_booking_expiry_minutes', 15),

            'show_email' => (bool) BookingSetting::get('show_email', true),
            'show_phone' => (bool) BookingSetting::get('show_phone', true),
            'show_date_of_birth' => (bool) BookingSetting::get('show_date_of_birth', true),
            'show_gender' => (bool) BookingSetting::get('show_gender', true),
            'show_nationality' => (bool) BookingSetting::get('show_nationality', true),
            'show_identity_number' => (bool) BookingSetting::get('show_identity_number', true),
            'show_slot_end_time' => (bool) BookingSetting::get('show_slot_end_time', true),

            'terms_en' => BookingSetting::get('terms_en', ''),
            'terms_ar' => BookingSetting::get('terms_ar', ''),

            'module_kiosk_enabled' => (bool) BookingSetting::get('module_kiosk_enabled', true),
            'module_extra_services_enabled' => (bool) BookingSetting::get('module_extra_services_enabled', true),
            'module_private_events_enabled' => (bool) BookingSetting::get('module_private_events_enabled', true),
            'module_promo_codes_enabled' => (bool) BookingSetting::get('module_promo_codes_enabled', true),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('settings')
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make(__('general_settings.tabs.general'))
                            ->icon('heroicon-o-identification')
                            ->schema([
                                Section::make(__('general_settings.sections.site_identity'))
                                    ->description(__('general_settings.sections.site_identity_desc'))
                                    ->schema([
                                        Grid::make(2)->schema([
                                            TextInput::make('site_name_en')
                                                ->label(__('general_settings.fields.site_name_en'))
                                                ->required()
                                                ->maxLength(255),

                                            TextInput::make('site_name_ar')
                                                ->label(__('general_settings.fields.site_name_ar'))
                                                ->required()
                                                ->maxLength(255),
                                        ]),
                                    ]),

                                Section::make(__('general_settings.sections.localization'))
                                    ->description(__('general_settings.sections.localization_desc'))
                                    ->schema([
                                        Grid::make(3)->schema([
                                            Select::make('timezone')
                                                ->label(__('general_settings.fields.timezone'))
                                                ->options(collect(\DateTimeZone::listIdentifiers())
                                                    ->mapWithKeys(fn ($tz) => [$tz => $tz]))
                                                ->searchable()
                                                ->required()
                                                ->native(false),

                                            Select::make('currency_code')
                                                ->label(__('general_settings.fields.currency_code'))
                                                ->options([
                                                    'OMR' => 'OMR — Omani Rial',
                                                    'USD' => 'USD — US Dollar',
                                                    'AED' => 'AED — UAE Dirham',
                                                    'SAR' => 'SAR — Saudi Riyal',
                                                    'QAR' => 'QAR — Qatari Riyal',
                                                    'KWD' => 'KWD — Kuwaiti Dinar',
                                                    'BHD' => 'BHD — Bahraini Dinar',
                                                    'EUR' => 'EUR — Euro',
                                                    'GBP' => 'GBP — British Pound',
                                                ])
                                                ->searchable()
                                                ->required()
                                                ->live()
                                                ->native(false)
                                                ->afterStateUpdated(fn ($state, $set) => $set('currency_symbol', $state)),

                                            TextInput::make('currency_symbol')
                                                ->label(__('general_settings.fields.currency_symbol'))
                                                ->required()
                                                ->maxLength(10),

                                        ]),

                                        FileUpload::make('currency_icon')
                                            ->label(__('general_settings.fields.currency_icon'))
                                            ->helperText(__('general_settings.fields.currency_icon_helper'))
                                            ->acceptedFileTypes(['image/svg+xml'])
                                            ->disk('public')
                                            ->directory('branding')
                                            ->visibility('public'),
                                    ]),
                            ]),

                        Tab::make(__('general_settings.tabs.branding'))
                            ->icon('heroicon-o-paint-brush')
                            ->schema([
                                Section::make(__('general_settings.sections.logos'))
                                    ->description(__('general_settings.sections.logos_desc'))
                                    ->schema([
                                        Grid::make(3)->schema([
                                            FileUpload::make('site_logo')
                                                ->label(__('general_settings.fields.site_logo'))
                                                ->helperText(__('general_settings.fields.site_logo_helper'))
                                                ->image()
                                                ->disk('public')
                                                ->directory('branding')
                                                ->visibility('public'),

                                            FileUpload::make('app_logo')
                                                ->label(__('general_settings.fields.app_logo'))
                                                ->helperText(__('general_settings.fields.app_logo_helper'))
                                                ->image()
                                                ->disk('public')
                                                ->directory('branding')
                                                ->visibility('public'),

                                            FileUpload::make('favicon')
                                                ->label(__('general_settings.fields.favicon'))
                                                ->helperText(__('general_settings.fields.favicon_helper'))
                                                ->image()
                                                ->disk('public')
                                                ->directory('branding')
                                                ->visibility('public'),
                                        ]),
                                    ]),

                                Section::make(__('general_settings.sections.colors'))
                                    ->description(__('general_settings.sections.colors_desc'))
                                    ->schema([
                                        Grid::make(3)->schema([
                                            ColorPicker::make('primary_color')
                                                ->label(__('general_settings.fields.primary_color'))
                                                ->helperText(__('general_settings.fields.primary_color_helper')),

                                            ColorPicker::make('secondary_color')
                                                ->label(__('general_settings.fields.secondary_color'))
                                                ->helperText(__('general_settings.fields.secondary_color_helper')),

                                            ColorPicker::make('panel_primary_color')
                                                ->label(__('general_settings.fields.panel_primary_color'))
                                                ->helperText(__('general_settings.fields.panel_primary_color_helper')),
                                        ]),
                                    ]),
                            ]),

                        Tab::make(__('general_settings.tabs.booking_rules'))
                            ->icon('heroicon-o-ticket')
                            ->schema([
                                Section::make(__('general_settings.sections.booking_rules'))
                                    ->schema([
                                        Grid::make(2)->schema([
                                            TextInput::make('min_tickets_per_booking')
                                                ->label(__('general_settings.fields.min_tickets_per_booking'))
                                                ->numeric()
                                                ->minValue(1)
                                                ->required(),

                                            TextInput::make('max_tickets_per_booking')
                                                ->label(__('general_settings.fields.max_tickets_per_booking'))
                                                ->numeric()
                                                ->minValue(1)
                                                ->maxValue(1000)
                                                ->required(),

                                            TextInput::make('max_attendee_age_years')
                                                ->label(__('general_settings.fields.max_attendee_age_years'))
                                                ->numeric()
                                                ->minValue(1)
                                                ->maxValue(120)
                                                ->required(),

                                            TextInput::make('pending_booking_expiry_minutes')
                                                ->label(__('general_settings.fields.pending_booking_expiry_minutes'))
                                                ->helperText(__('general_settings.fields.pending_booking_expiry_minutes_helper'))
                                                ->numeric()
                                                ->minValue(1)
                                                ->required(),
                                        ]),

                                        Toggle::make('show_slot_end_time')
                                            ->label(__('general_settings.fields.show_slot_end_time'))
                                            ->helperText(__('general_settings.fields.show_slot_end_time_helper')),
                                    ]),
                            ]),

                        Tab::make(__('general_settings.tabs.attendee_fields'))
                            ->icon('heroicon-o-user-circle')
                            ->schema([
                                Section::make(__('general_settings.sections.attendee_fields'))
                                    ->description(__('general_settings.sections.attendee_fields_desc'))
                                    ->schema([
                                        Grid::make(3)->schema([
                                            Toggle::make('show_email')
                                                ->label(__('general_settings.fields.show_email')),

                                            Toggle::make('show_phone')
                                                ->label(__('general_settings.fields.show_phone')),

                                            Toggle::make('show_date_of_birth')
                                                ->label(__('general_settings.fields.show_date_of_birth')),

                                            Toggle::make('show_gender')
                                                ->label(__('general_settings.fields.show_gender')),

                                            Toggle::make('show_nationality')
                                                ->label(__('general_settings.fields.show_nationality')),

                                            Toggle::make('show_identity_number')
                                                ->label(__('general_settings.fields.show_identity_number')),
                                        ]),
                                    ]),
                            ]),

                        Tab::make(__('general_settings.tabs.terms'))
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Section::make(__('general_settings.fields.terms_en'))
                                    ->schema([
                                        RichEditor::make('terms_en')
                                            ->label('')
                                            ->fileAttachmentsDisk('public'),
                                    ]),

                                Section::make(__('general_settings.fields.terms_ar'))
                                    ->schema([
                                        RichEditor::make('terms_ar')
                                            ->label('')
                                            ->fileAttachmentsDisk('public'),
                                    ]),
                            ]),

                        Tab::make(__('general_settings.tabs.modules'))
                            ->icon('heroicon-o-squares-2x2')
                            ->schema([
                                Section::make(__('general_settings.sections.modules'))
                                    ->description(__('general_settings.sections.modules_desc'))
                                    ->schema([
                                        Toggle::make('module_kiosk_enabled')
                                            ->label(__('general_settings.fields.module_kiosk_enabled'))
                                            ->helperText(__('general_settings.fields.module_kiosk_enabled_helper')),

                                        Toggle::make('module_extra_services_enabled')
                                            ->label(__('general_settings.fields.module_extra_services_enabled'))
                                            ->helperText(__('general_settings.fields.module_extra_services_enabled_helper')),

                                        Toggle::make('module_private_events_enabled')
                                            ->label(__('general_settings.fields.module_private_events_enabled'))
                                            ->helperText(__('general_settings.fields.module_private_events_enabled_helper')),

                                        Toggle::make('module_promo_codes_enabled')
                                            ->label(__('general_settings.fields.module_promo_codes_enabled'))
                                            ->helperText(__('general_settings.fields.module_promo_codes_enabled_helper')),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        foreach ([
            'site_name_en',
            'site_name_ar',
            'timezone',
            'currency_code',
            'currency_symbol',
            'currency_icon',
            'site_logo',
            'app_logo',
            'favicon',
            'primary_color',
            'secondary_color',
            'panel_primary_color',
            'min_tickets_per_booking',
            'max_tickets_per_booking',
            'max_attendee_age_years',
            'pending_booking_expiry_minutes',
            'terms_en',
            'terms_ar',
        ] as $key) {
            BookingSetting::set($key, (string) ($state[$key] ?? ''));
        }

        foreach ([
            'show_email',
            'show_phone',
            'show_date_of_birth',
            'show_gender',
            'show_nationality',
            'show_identity_number',
            'show_slot_end_time',
            'module_kiosk_enabled',
            'module_extra_services_enabled',
            'module_private_events_enabled',
            'module_promo_codes_enabled',
        ] as $key) {
            BookingSetting::set($key, !empty($state[$key]) ? 'true' : 'false');
        }

        BookingSetting::clearCache();

        Notification::make()
            ->title(__('general_settings.notifications.updated'))
            ->success()
            ->send();
    }
}
