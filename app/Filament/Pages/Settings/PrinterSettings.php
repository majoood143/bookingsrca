<?php

namespace App\Filament\Pages\Settings;

use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use App\Models\BookingSetting;
use App\Models\PrintJob;
use App\Services\Printing\ThermalPrintService;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Illuminate\Support\Str;
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
        if (empty(BookingSetting::get('printer.agent_token'))) {
            BookingSetting::set('printer.agent_token', Str::random(40));
        }

        $this->fillForm();
    }

    private function fillForm(): void
    {
        $this->form->fill([
            'enabled'          => (bool) BookingSetting::get('printer.enabled', false),
            'agent_token'      => BookingSetting::get('printer.agent_token', ''),
            'paper_width_dots' => (string) BookingSetting::get('printer.paper_width_dots', 576),
            'graphics_mode'    => (bool) BookingSetting::get('printer.graphics_mode', false),
            'chrome_path'      => BookingSetting::get('printer.chrome_path', ''),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Thermal Printer'))
                    ->description(__('The app is hosted online and cannot reach a printer on your on-site network directly. Attendee tickets are rendered here and queued; an on-site print agent (see print-agent/README.md) polls this app and delivers them to the printer over your local network.'))
                    ->schema([
                        Toggle::make('enabled')
                            ->label(__('Enable Thermal Printing'))
                            ->helperText(__('When disabled, printing attendee tickets will show an error instead of queuing a job.'))
                            ->live(),

                        Select::make('paper_width_dots')
                            ->label(__('Paper Width'))
                            ->options([
                                '384' => __('58mm (384 dots)'),
                                '576' => __('80mm (576 dots)'),
                            ])
                            ->required()
                            ->native(false),

                        Toggle::make('graphics_mode')
                            ->label(__('Use Raster Graphics Mode'))
                            ->helperText(__('Enable if your printer supports GS ( L raster graphics for higher quality output. Leave off to use the more widely compatible bit-image method.')),

                        TextInput::make('chrome_path')
                            ->label(__('Chrome/Chromium Executable Path (optional)'))
                            ->helperText(__('Leave blank to let Puppeteer auto-detect a browser. Set this if ticket/receipt rendering fails with a "Could not find chrome-headless-shell" error — point it at an already-installed Chrome, e.g. "C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe" on Windows or "/usr/bin/google-chrome" on Linux.'))
                            ->maxLength(500),
                    ]),

                Section::make(__('On-Site Print Agent'))
                    ->description(__('This token authenticates the on-site agent script when it polls this app for print jobs. Copy it into the agent\'s config.ini.'))
                    ->schema([
                        TextInput::make('agent_token')
                            ->label(__('Agent Token'))
                            ->password()
                            ->revealable()
                            ->readOnly()
                            ->maxLength(255),

                        Placeholder::make('agent_last_seen_at')
                            ->label(__('Agent Last Seen'))
                            ->content(fn () => $this->formatLastSeen()),

                        Placeholder::make('pending_jobs')
                            ->label(__('Pending / Failed Jobs'))
                            ->content(fn () => $this->formatJobCounts()),
                    ]),
            ])
            ->statePath('data');
    }

    private function formatLastSeen(): string
    {
        $lastSeen = BookingSetting::get('printer.agent_last_seen_at');

        if (!$lastSeen) {
            return __('Never — the on-site agent has not polled this app yet.');
        }

        return \Illuminate\Support\Carbon::parse($lastSeen)->diffForHumans();
    }

    private function formatJobCounts(): string
    {
        $pending = PrintJob::whereIn('status', ['pending', 'claimed'])->count();
        $failed  = PrintJob::where('status', 'failed')->count();

        return __(':pending pending, :failed failed', ['pending' => $pending, 'failed' => $failed]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('regenerate_token')
                ->label(__('Regenerate Agent Token'))
                ->icon('heroicon-o-key')
                ->color('gray')
                ->requiresConfirmation()
                ->modalDescription(__('The current token will stop working immediately — update the on-site agent\'s config.ini with the new value right after.'))
                ->action(function () {
                    BookingSetting::set('printer.agent_token', Str::random(40));
                    $this->fillForm();

                    Notification::make()
                        ->success()
                        ->title(__('Agent token regenerated.'))
                        ->send();
                }),

            Action::make('send_test_print')
                ->label(__('Send Test Print'))
                ->icon('heroicon-o-signal')
                ->color('gray')
                ->action(function () {
                    try {
                        app(ThermalPrintService::class)->sendTestPrint();

                        Notification::make()
                            ->success()
                            ->title(__('Test print queued — it will print once the on-site agent picks it up.'))
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
        $state = $this->form->getState();

        BookingSetting::set('printer.enabled', !empty($state['enabled']) ? '1' : '0');
        BookingSetting::set('printer.paper_width_dots', $state['paper_width_dots']);
        BookingSetting::set('printer.graphics_mode', !empty($state['graphics_mode']) ? '1' : '0');
        BookingSetting::set('printer.chrome_path', trim($state['chrome_path'] ?? ''));

        Notification::make()
            ->title(__('Printer settings saved.'))
            ->success()
            ->send();
    }
}
