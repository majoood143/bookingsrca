<?php

namespace App\Filament\Pages\Settings;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Group;
use App\Models\BookingSetting;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class PaymentGateways extends Page implements HasForms
{
    use InteractsWithForms;
    use HasPageShield;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-credit-card';

    protected static string | \UnitEnum | null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 20;

    protected string $view = 'filament.pages.settings.payment-gateways';

    public ?array $data = [];

    public function getTitle(): string
    {
        return __('payment_gateways.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('payment_gateways.title');
    }

    public function mount(): void
    {
        $this->form->fill([
            'enabled_gateways' => $this->resolveEnabledGateways(),
            'thawani'        => [
                'secret_key'      => BookingSetting::get('thawani.secret_key', ''),
                'publishable_key' => BookingSetting::get('thawani.publishable_key', ''),
                'base_url'        => BookingSetting::get('thawani.base_url', ''),
                'webhook_secret'  => BookingSetting::get('thawani.webhook_secret', ''),
                'test_mode'       => (bool) BookingSetting::get('thawani.test_mode', true),
            ],
            'nbo'            => [
                'tranportal_id'       => BookingSetting::get('nbo.tranportal_id', ''),
                'tranportal_password' => BookingSetting::get('nbo.tranportal_password', ''),
                'resource_key'        => BookingSetting::get('nbo.resource_key', ''),
                'endpoint_url'        => BookingSetting::get('nbo.endpoint_url', ''),
                'test_mode'           => (bool) BookingSetting::get('nbo.test_mode', true),
            ],
            'ccavenue'       => [
                'merchant_id'  => BookingSetting::get('ccavenue.merchant_id', ''),
                'access_code'  => BookingSetting::get('ccavenue.access_code', ''),
                'working_key'  => BookingSetting::get('ccavenue.working_key', ''),
                'endpoint_url' => BookingSetting::get('ccavenue.endpoint_url', ''),
                'test_mode'    => (bool) BookingSetting::get('ccavenue.test_mode', true),
            ],
        ]);
    }

    /**
     * Reads the enabled_gateways JSON setting, falling back to the legacy
     * single active_gateway value for installs that haven't saved this
     * page since the multi-gateway option was introduced.
     */
    protected function resolveEnabledGateways(): array
    {
        $stored  = BookingSetting::get('enabled_gateways');
        $decoded = $stored ? json_decode($stored, true) : null;

        if (is_array($decoded) && !empty($decoded)) {
            return array_values($decoded);
        }

        return [(string) BookingSetting::get('active_gateway', 'free')];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('payment_gateways.sections.active_gateway'))
                    ->description(__('payment_gateways.sections.active_gateway_desc'))
                    ->schema([
                        CheckboxList::make('enabled_gateways')
                            ->label(__('payment_gateways.options.active_gateway'))
                            ->options([
                                'free'    => __('payment_gateways.options.free'),
                                'cash'    => __('payment_gateways.options.cash'),
                                'thawani'  => __('payment_gateways.options.thawani'),
                                'nbo'      => __('payment_gateways.options.nbo'),
                                'ccavenue' => __('payment_gateways.options.ccavenue'),
                            ])
                            ->required()
                            ->live()
                            ->columns(2),
                    ]),

                Section::make(__('payment_gateways.sections.thawani'))
                    ->description(__('payment_gateways.sections.thawani_desc'))
                    ->visible(fn ($get) => in_array('thawani', (array) $get('enabled_gateways')))
                    ->schema([
                        Toggle::make('thawani.test_mode')
                            ->label(__('payment_gateways.fields.test_mode'))
                            ->helperText(__('payment_gateways.fields.thawani_test_mode_helper'))
                            ->default(true)
                            ->live(),

                        Group::make([
                            TextInput::make('thawani.secret_key')
                                ->label(__('payment_gateways.fields.secret_key'))
                                ->password()
                                ->revealable()
                                ->required(fn ($get) => in_array('thawani', (array) $get('enabled_gateways')))
                                ->maxLength(255),

                            TextInput::make('thawani.publishable_key')
                                ->label(__('payment_gateways.fields.publishable_key'))
                                ->required(fn ($get) => in_array('thawani', (array) $get('enabled_gateways')))
                                ->maxLength(255),
                        ])->columns(2),

                        TextInput::make('thawani.base_url')
                            ->label(__('payment_gateways.fields.base_url_override'))
                            ->helperText(__('payment_gateways.fields.url_override_helper'))
                            ->url()
                            ->placeholder(fn ($get) => $get('thawani.test_mode')
                                ? 'https://uatcheckout.thawani.om/api/v1'
                                : 'https://checkout.thawani.om/api/v1')
                            ->maxLength(255),

                        TextInput::make('thawani.webhook_secret')
                            ->label(__('payment_gateways.fields.webhook_secret'))
                            ->helperText(__('payment_gateways.fields.webhook_secret_helper'))
                            ->password()
                            ->revealable()
                            ->maxLength(255),
                    ]),

                Section::make(__('payment_gateways.sections.nbo'))
                    ->description(__('payment_gateways.sections.nbo_desc'))
                    ->visible(fn ($get) => in_array('nbo', (array) $get('enabled_gateways')))
                    ->schema([
                        Toggle::make('nbo.test_mode')
                            ->label(__('payment_gateways.fields.test_mode'))
                            ->helperText(__('payment_gateways.fields.nbo_test_mode_helper'))
                            ->default(true)
                            ->live(),

                        Group::make([
                            TextInput::make('nbo.tranportal_id')
                                ->label(__('payment_gateways.fields.tranportal_id'))
                                ->required(fn ($get) => in_array('nbo', (array) $get('enabled_gateways')))
                                ->maxLength(255),

                            TextInput::make('nbo.tranportal_password')
                                ->label(__('payment_gateways.fields.tranportal_password'))
                                ->password()
                                ->revealable()
                                ->required(fn ($get) => in_array('nbo', (array) $get('enabled_gateways')))
                                ->maxLength(255),
                        ])->columns(2),

                        TextInput::make('nbo.resource_key')
                            ->label(__('payment_gateways.fields.resource_key'))
                            ->helperText(__('payment_gateways.fields.resource_key_helper'))
                            ->password()
                            ->revealable()
                            ->required(fn ($get) => in_array('nbo', (array) $get('enabled_gateways')))
                            ->maxLength(255),

                        TextInput::make('nbo.endpoint_url')
                            ->label(__('payment_gateways.fields.endpoint_url_override'))
                            ->helperText(__('payment_gateways.fields.url_override_helper'))
                            ->url()
                            ->placeholder(fn ($get) => $get('nbo.test_mode')
                                ? 'https://unifiedpg.nbo.om/OLTPSTG/payment/hosted.htm'
                                : 'https://unifiedpg.nbo.om/OLTP/payment/hosted.htm')
                            ->maxLength(255),
                    ]),

                Section::make(__('payment_gateways.sections.ccavenue'))
                    ->description(__('payment_gateways.sections.ccavenue_desc'))
                    ->visible(fn ($get) => in_array('ccavenue', (array) $get('enabled_gateways')))
                    ->schema([
                        Toggle::make('ccavenue.test_mode')
                            ->label(__('payment_gateways.fields.test_mode'))
                            ->helperText(__('payment_gateways.fields.ccavenue_test_mode_helper'))
                            ->default(true)
                            ->live(),

                        Group::make([
                            TextInput::make('ccavenue.merchant_id')
                                ->label(__('payment_gateways.fields.merchant_id'))
                                ->required(fn ($get) => in_array('ccavenue', (array) $get('enabled_gateways')))
                                ->maxLength(255),

                            TextInput::make('ccavenue.access_code')
                                ->label(__('payment_gateways.fields.access_code'))
                                ->password()
                                ->revealable()
                                ->required(fn ($get) => in_array('ccavenue', (array) $get('enabled_gateways')))
                                ->maxLength(255),
                        ])->columns(2),

                        TextInput::make('ccavenue.working_key')
                            ->label(__('payment_gateways.fields.working_key'))
                            ->helperText(__('payment_gateways.fields.working_key_helper'))
                            ->password()
                            ->revealable()
                            ->required(fn ($get) => in_array('ccavenue', (array) $get('enabled_gateways')))
                            ->maxLength(255),

                        TextInput::make('ccavenue.endpoint_url')
                            ->label(__('payment_gateways.fields.endpoint_url_override'))
                            ->helperText(__('payment_gateways.fields.ccavenue_endpoint_url_helper'))
                            ->url()
                            ->placeholder(fn ($get) => $get('ccavenue.test_mode')
                                ? 'https://mti.bankmuscat.com:6443/transaction.do?command=initiateTransaction'
                                : 'https://smartpaytrns.bankmuscat.com/transaction.do?command=initiateTransaction')
                            ->maxLength(255),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state    = $this->form->getState();
        $thawani  = $state['thawani']  ?? [];
        $nbo      = $state['nbo']      ?? [];
        $ccavenue = $state['ccavenue'] ?? [];
        $enabled  = array_values($state['enabled_gateways'] ?? []);

        BookingSetting::set('enabled_gateways', json_encode($enabled));

        BookingSetting::set('thawani.secret_key',      $thawani['secret_key']      ?? '');
        BookingSetting::set('thawani.publishable_key', $thawani['publishable_key'] ?? '');
        BookingSetting::set('thawani.base_url',        $thawani['base_url']        ?? '');
        BookingSetting::set('thawani.webhook_secret',  $thawani['webhook_secret']  ?? '');
        BookingSetting::set('thawani.test_mode',       !empty($thawani['test_mode']) ? '1' : '0');

        BookingSetting::set('nbo.tranportal_id',       $nbo['tranportal_id']       ?? '');
        BookingSetting::set('nbo.tranportal_password', $nbo['tranportal_password'] ?? '');
        BookingSetting::set('nbo.resource_key',        $nbo['resource_key']        ?? '');
        BookingSetting::set('nbo.endpoint_url',        $nbo['endpoint_url']        ?? '');
        BookingSetting::set('nbo.test_mode',           !empty($nbo['test_mode'])   ? '1' : '0');

        BookingSetting::set('ccavenue.merchant_id',  $ccavenue['merchant_id']  ?? '');
        BookingSetting::set('ccavenue.access_code',  $ccavenue['access_code']  ?? '');
        BookingSetting::set('ccavenue.working_key',  $ccavenue['working_key']  ?? '');
        BookingSetting::set('ccavenue.endpoint_url', $ccavenue['endpoint_url'] ?? '');
        BookingSetting::set('ccavenue.test_mode',    !empty($ccavenue['test_mode']) ? '1' : '0');

        Notification::make()
            ->title(__('payment_gateways.notifications.saved'))
            ->success()
            ->send();
    }
}
