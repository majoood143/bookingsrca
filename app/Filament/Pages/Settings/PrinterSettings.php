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
        return __('printer_settings.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('printer_settings.title');
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
                Section::make(__('printer_settings.sections.thermal_printer'))
                    ->description(__('printer_settings.sections.thermal_printer_desc'))
                    ->schema([
                        Toggle::make('enabled')
                            ->label(__('printer_settings.fields.enabled'))
                            ->helperText(__('printer_settings.fields.enabled_helper'))
                            ->live(),

                        Select::make('paper_width_dots')
                            ->label(__('printer_settings.fields.paper_width'))
                            ->options([
                                '384' => __('printer_settings.fields.paper_width_384'),
                                '576' => __('printer_settings.fields.paper_width_576'),
                            ])
                            ->required()
                            ->native(false),

                        Toggle::make('graphics_mode')
                            ->label(__('printer_settings.fields.graphics_mode'))
                            ->helperText(__('printer_settings.fields.graphics_mode_helper')),

                        TextInput::make('chrome_path')
                            ->label(__('printer_settings.fields.chrome_path'))
                            ->helperText(__('printer_settings.fields.chrome_path_helper'))
                            ->maxLength(500),
                    ]),

                Section::make(__('printer_settings.sections.print_agent'))
                    ->description(__('printer_settings.sections.print_agent_desc'))
                    ->schema([
                        TextInput::make('agent_token')
                            ->label(__('printer_settings.fields.agent_token'))
                            ->password()
                            ->revealable()
                            ->readOnly()
                            ->maxLength(255),

                        Placeholder::make('agent_last_seen_at')
                            ->label(__('printer_settings.fields.agent_last_seen'))
                            ->content(fn () => $this->formatLastSeen()),

                        Placeholder::make('pending_jobs')
                            ->label(__('printer_settings.fields.pending_jobs'))
                            ->content(fn () => $this->formatJobCounts()),
                    ]),
            ])
            ->statePath('data');
    }

    private function formatLastSeen(): string
    {
        $lastSeen = BookingSetting::get('printer.agent_last_seen_at');

        if (!$lastSeen) {
            return __('printer_settings.messages.agent_never_seen');
        }

        return \Illuminate\Support\Carbon::parse($lastSeen)->diffForHumans();
    }

    private function formatJobCounts(): string
    {
        $pending = PrintJob::whereIn('status', ['pending', 'claimed'])->count();
        $failed  = PrintJob::where('status', 'failed')->count();

        return __('printer_settings.messages.job_counts', ['pending' => $pending, 'failed' => $failed]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('regenerate_token')
                ->label(__('printer_settings.actions.regenerate_token'))
                ->icon('heroicon-o-key')
                ->color('gray')
                ->requiresConfirmation()
                ->modalDescription(__('printer_settings.actions.regenerate_token_confirm'))
                ->action(function () {
                    BookingSetting::set('printer.agent_token', Str::random(40));
                    $this->fillForm();

                    Notification::make()
                        ->success()
                        ->title(__('printer_settings.notifications.token_regenerated'))
                        ->send();
                }),

            Action::make('send_test_print')
                ->label(__('printer_settings.actions.send_test_print'))
                ->icon('heroicon-o-signal')
                ->color('gray')
                ->action(function () {
                    try {
                        app(ThermalPrintService::class)->sendTestPrint();

                        Notification::make()
                            ->success()
                            ->title(__('printer_settings.notifications.test_print_queued'))
                            ->send();
                    } catch (Throwable $e) {
                        Notification::make()
                            ->danger()
                            ->title(__('printer_settings.notifications.test_print_failed'))
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
            ->title(__('printer_settings.notifications.saved'))
            ->success()
            ->send();
    }
}
