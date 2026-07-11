<?php

namespace App\Filament\Pages\Settings;

use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use App\Models\BookingSetting;
use App\Services\Printing\AttendeeTicketPrintService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Throwable;

class PrinterSettings extends Page implements HasForms
{
    use InteractsWithForms;
    use HasPageShield;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-printer';

    protected static string | \UnitEnum | null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 30;

    protected string $view = 'filament.pages.settings.printer-settings';

    public ?array $data = [];

    public function getTitle(): string
    {
        return __('Printer Settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('Printer Settings');
    }

    public function mount(): void
    {
        $this->form->fill([
            'enabled'        => (bool) BookingSetting::get('printer.enabled', false),
            'driver'         => BookingSetting::get('printer.driver', 'network'),
            'paper_width_dots' => (string) BookingSetting::get('printer.paper_width_dots', 576),
            'timeout_seconds' => BookingSetting::get('printer.timeout_seconds', 5),
            'graphics_mode'  => (bool) BookingSetting::get('printer.graphics_mode', false),
            'network'        => [
                'host' => BookingSetting::get('printer.network.host', ''),
                'port' => BookingSetting::get('printer.network.port', 9100),
            ],
            'cups'           => [
                'printer_name' => BookingSetting::get('printer.cups.printer_name', ''),
            ],
            'windows'        => [
                'printer_name' => BookingSetting::get('printer.windows.printer_name', ''),
            ],
            'file'           => [
                'path' => BookingSetting::get('printer.file.path', storage_path('app/printing/last-print.bin')),
            ],
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Thermal Printer'))
                    ->description(__('Configure the ESC/POS thermal printer used to print attendee tickets.'))
                    ->schema([
                        Toggle::make('enabled')
                            ->label(__('Enable Thermal Printing'))
                            ->helperText(__('When disabled, printing attendee tickets will show an error instead of attempting to print.'))
                            ->live(),

                        Select::make('driver')
                            ->label(__('Connector'))
                            ->options([
                                'network' => __('Network (IP:Port)'),
                                'cups'    => __('CUPS (USB/Shared Linux Printer)'),
                                'windows' => __('Windows Shared Printer'),
                                'file'    => __('File (Diagnostic Only)'),
                            ])
                            ->required()
                            ->live()
                            ->native(false),

                        Select::make('paper_width_dots')
                            ->label(__('Paper Width'))
                            ->options([
                                '384' => __('58mm (384 dots)'),
                                '576' => __('80mm (576 dots)'),
                            ])
                            ->required()
                            ->native(false),

                        TextInput::make('timeout_seconds')
                            ->label(__('Connection Timeout (seconds)'))
                            ->numeric()
                            ->default(5)
                            ->required(),

                        Toggle::make('graphics_mode')
                            ->label(__('Use Raster Graphics Mode'))
                            ->helperText(__('Enable if your printer supports GS ( L raster graphics for higher quality output. Leave off to use the more widely compatible bit-image method.')),
                    ]),

                Section::make(__('Network Printer'))
                    ->visible(fn ($get) => $get('driver') === 'network')
                    ->schema([
                        TextInput::make('network.host')
                            ->label(__('Host / IP Address'))
                            ->required(fn ($get) => $get('driver') === 'network')
                            ->maxLength(255),

                        TextInput::make('network.port')
                            ->label(__('Port'))
                            ->numeric()
                            ->default(9100)
                            ->required(fn ($get) => $get('driver') === 'network'),
                    ]),

                Section::make(__('CUPS Printer'))
                    ->visible(fn ($get) => $get('driver') === 'cups')
                    ->schema([
                        TextInput::make('cups.printer_name')
                            ->label(__('CUPS Printer Name'))
                            ->helperText(__('Requires the "cups" PHP extension to be installed on this server.'))
                            ->required(fn ($get) => $get('driver') === 'cups')
                            ->maxLength(255),
                    ]),

                Section::make(__('Windows Shared Printer'))
                    ->visible(fn ($get) => $get('driver') === 'windows')
                    ->schema([
                        TextInput::make('windows.printer_name')
                            ->label(__('Windows Printer Name'))
                            ->helperText(__('Only works when this application server itself runs on Windows. Use the Network or CUPS driver otherwise.'))
                            ->required(fn ($get) => $get('driver') === 'windows')
                            ->maxLength(255),
                    ]),

                Section::make(__('Diagnostic File Output'))
                    ->visible(fn ($get) => $get('driver') === 'file')
                    ->schema([
                        TextInput::make('file.path')
                            ->label(__('Output File Path'))
                            ->helperText(__('Raw ESC/POS bytes are written to this file instead of being sent to a real printer — useful for testing without hardware.'))
                            ->required(fn ($get) => $get('driver') === 'file')
                            ->maxLength(255),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('send_test_print')
                ->label(__('Send Test Print'))
                ->icon('heroicon-o-signal')
                ->color('gray')
                ->action(function () {
                    try {
                        app(AttendeeTicketPrintService::class)->sendTestPrint();

                        Notification::make()
                            ->success()
                            ->title(__('Test print sent successfully.'))
                            ->send();
                    } catch (Throwable $e) {
                        Notification::make()
                            ->danger()
                            ->title(__('Test print failed.'))
                            ->body($e->getMessage())
                            ->send();
                    }
                }),
        ];
    }

    public function save(): void
    {
        $state   = $this->form->getState();
        $network = $state['network'] ?? [];
        $cups    = $state['cups']    ?? [];
        $windows = $state['windows'] ?? [];
        $file    = $state['file']    ?? [];

        BookingSetting::set('printer.enabled', !empty($state['enabled']) ? '1' : '0');
        BookingSetting::set('printer.driver', $state['driver']);
        BookingSetting::set('printer.paper_width_dots', $state['paper_width_dots']);
        BookingSetting::set('printer.timeout_seconds', $state['timeout_seconds']);
        BookingSetting::set('printer.graphics_mode', !empty($state['graphics_mode']) ? '1' : '0');

        BookingSetting::set('printer.network.host', $network['host'] ?? '');
        BookingSetting::set('printer.network.port', $network['port'] ?? 9100);

        BookingSetting::set('printer.cups.printer_name', $cups['printer_name'] ?? '');

        BookingSetting::set('printer.windows.printer_name', $windows['printer_name'] ?? '');

        BookingSetting::set('printer.file.path', $file['path'] ?? '');

        Notification::make()
            ->title(__('Printer settings saved.'))
            ->success()
            ->send();
    }
}
