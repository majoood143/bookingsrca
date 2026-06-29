<?php

namespace App\Filament\Pages\Settings;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Group;
use App\Models\BookingSetting;
use Filament\Forms\Components\Select;
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
        return __('Payment Gateways');
    }

    public static function getNavigationLabel(): string
    {
        return __('Payment Gateways');
    }

    public function mount(): void
    {
        $this->form->fill([
            'active_gateway' => BookingSetting::get('active_gateway', 'free'),
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
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Active Gateway'))
                    ->description(__('Choose which payment gateway is used during checkout.'))
                    ->schema([
                        Select::make('active_gateway')
                            ->label(__('Active Payment Gateway'))
                            ->options([
                                'free'    => __('Free (no payment required)'),
                                'cash'    => __('Cash / Pay at Door'),
                                'thawani' => __('Thawani'),
                                'nbo'     => __('NBO Unified Checkout'),
                            ])
                            ->required()
                            ->live()
                            ->native(false),
                    ]),

                Section::make(__('Thawani Settings'))
                    ->description(__('Credentials for the Thawani payment gateway.'))
                    ->visible(fn ($get) => $get('active_gateway') === 'thawani')
                    ->schema([
                        Toggle::make('thawani.test_mode')
                            ->label(__('Test Mode'))
                            ->helperText(__('When enabled, requests go to uatcheckout.thawani.om instead of the live endpoint.'))
                            ->default(true)
                            ->live(),

                        Group::make([
                            TextInput::make('thawani.secret_key')
                                ->label(__('Secret Key'))
                                ->password()
                                ->revealable()
                                ->required(fn ($get) => $get('active_gateway') === 'thawani')
                                ->maxLength(255),

                            TextInput::make('thawani.publishable_key')
                                ->label(__('Publishable Key'))
                                ->required(fn ($get) => $get('active_gateway') === 'thawani')
                                ->maxLength(255),
                        ])->columns(2),

                        TextInput::make('thawani.base_url')
                            ->label(__('Base URL Override'))
                            ->helperText(__('Leave blank to use the default URL based on test-mode setting.'))
                            ->url()
                            ->placeholder(fn ($get) => $get('thawani.test_mode')
                                ? 'https://uatcheckout.thawani.om'
                                : 'https://checkout.thawani.om')
                            ->maxLength(255),

                        TextInput::make('thawani.webhook_secret')
                            ->label(__('Webhook Secret'))
                            ->helperText(__('Optional HMAC-SHA256 secret used to verify incoming Thawani webhook events.'))
                            ->password()
                            ->revealable()
                            ->maxLength(255),
                    ]),

                Section::make(__('NBO Settings'))
                    ->description(__('Credentials for the NBO Unified Checkout payment gateway.'))
                    ->visible(fn ($get) => $get('active_gateway') === 'nbo')
                    ->schema([
                        Toggle::make('nbo.test_mode')
                            ->label(__('Test Mode'))
                            ->helperText(__('When enabled, requests go to the NBO sandbox endpoint instead of the live endpoint.'))
                            ->default(true)
                            ->live(),

                        Group::make([
                            TextInput::make('nbo.tranportal_id')
                                ->label(__('Tranportal ID'))
                                ->required(fn ($get) => $get('active_gateway') === 'nbo')
                                ->maxLength(255),

                            TextInput::make('nbo.tranportal_password')
                                ->label(__('Tranportal Password'))
                                ->password()
                                ->revealable()
                                ->required(fn ($get) => $get('active_gateway') === 'nbo')
                                ->maxLength(255),
                        ])->columns(2),

                        TextInput::make('nbo.resource_key')
                            ->label(__('Resource Key'))
                            ->helperText(__('AES encryption key provided by NBO. Must be exactly 16 characters.'))
                            ->password()
                            ->revealable()
                            ->required(fn ($get) => $get('active_gateway') === 'nbo')
                            ->maxLength(255),

                        TextInput::make('nbo.endpoint_url')
                            ->label(__('Endpoint URL Override'))
                            ->helperText(__('Leave blank to use the default URL based on test-mode setting.'))
                            ->url()
                            ->placeholder(fn ($get) => $get('nbo.test_mode')
                                ? 'https://unifiedpg.nbo.om/OLTPSTG/payment/hosted.htm'
                                : 'https://unifiedpg.nbo.om/OLTP/payment/hosted.htm')
                            ->maxLength(255),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state   = $this->form->getState();
        $thawani = $state['thawani'] ?? [];
        $nbo     = $state['nbo']     ?? [];

        BookingSetting::set('active_gateway', $state['active_gateway']);

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

        Notification::make()
            ->title(__('Payment gateway settings saved.'))
            ->success()
            ->send();
    }
}
