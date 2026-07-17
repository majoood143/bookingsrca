<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use App\Filament\Resources\EventResource\Pages\EditEvent;
use App\Filament\Resources\EventResource\Pages\ListEventActivities;
use App\Filament\Resources\EventResource\Widgets\EventBookingsTrendChart;
use App\Filament\Resources\EventResource\Widgets\EventStatsOverview;
use App\Filament\Resources\EventResource\Widgets\EventTicketBreakdown;
use App\Jobs\SendEventInsightsReport;
use App\Support\EventInsightsReport;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Response;
use Spatie\LaravelPdf\Enums\Format;
use Spatie\LaravelPdf\Facades\Pdf;

class ViewEvent extends ViewRecord
{
    protected static string $resource = EventResource::class;

    public function getSubNavigation(): array
    {
        return $this->generateNavigationItems([
            self::class,
            EditEvent::class,
            ListEventActivities::class,
        ]);
    }

    public function getSubNavigationParameters(): array
    {
        return ['record' => $this->getRecord()];
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),

            Action::make('downloadInsightsPdf')
                ->label(__('event_insights.actions.download_pdf'))
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->action(function () {
                    $event = $this->getRecord();
                    $from = Carbon::now()->subDays(30);
                    $to = Carbon::now();
                    $report = EventInsightsReport::build($event, $from, $to);
                    $locale = app()->getLocale();
                    $filename = __('event_insights.export.filename') . '-' . $event->slug . '-' . now()->format('Y-m-d') . '.pdf';

                    $pdf = Pdf::view('reports.event-insights-pdf', [
                        ...$report,
                        'locale' => $locale,
                    ])
                        ->headerView('reports.partials.pdf-header', [
                            'title' => $event->getTranslation('title', $locale),
                            'periodLabel' => __('reports.document.period', [
                                'from' => $from->format('Y-m-d'),
                                'to' => $to->format('Y-m-d'),
                            ], $locale),
                            'locale' => $locale,
                            'logoBase64' => EventInsightsReport::logoBase64(),
                        ])
                        ->footerView('reports.partials.pdf-footer', ['locale' => $locale])
                        ->format(Format::A4)
                        ->margins(38, 12, 18, 12, 'mm')
                        ->withBrowsershot(fn ($browsershot) => $browsershot->waitForFunction('window.pdfReady === true', null, 15000));

                    return Response::streamDownload(
                        fn () => print($pdf->generatePdfContent()),
                        $filename,
                        ['Content-Type' => 'application/pdf'],
                    );
                }),

            Action::make('emailReportSettings')
                ->label(__('event_insights.actions.email_settings'))
                ->icon('heroicon-o-envelope')
                ->color('gray')
                ->modalWidth('lg')
                ->fillForm(fn () => [
                    'recipients' => $this->getRecord()->reportSubscription?->recipients ?? [],
                    'is_enabled' => $this->getRecord()->reportSubscription?->is_enabled ?? false,
                    'send_day' => $this->getRecord()->reportSubscription?->send_day ?? 1,
                    'send_time' => $this->getRecord()->reportSubscription?->send_time ?? '08:00',
                ])
                ->schema([
                    TagsInput::make('recipients')
                        ->label(__('event_insights.form.recipients'))
                        ->placeholder(__('event_insights.form.recipients_placeholder'))
                        ->required()
                        ->rule(function () {
                            return function (string $attribute, $value, \Closure $fail) {
                                foreach ((array) $value as $email) {
                                    if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                        $fail(__('event_insights.form.invalid_email', ['email' => $email]));
                                    }
                                }
                            };
                        }),

                    Toggle::make('is_enabled')
                        ->label(__('event_insights.form.is_enabled')),

                    Select::make('send_day')
                        ->label(__('event_insights.form.send_day'))
                        ->options([
                            0 => __('event_insights.days.sunday'),
                            1 => __('event_insights.days.monday'),
                            2 => __('event_insights.days.tuesday'),
                            3 => __('event_insights.days.wednesday'),
                            4 => __('event_insights.days.thursday'),
                            5 => __('event_insights.days.friday'),
                            6 => __('event_insights.days.saturday'),
                        ])
                        ->required(),

                    TimePicker::make('send_time')
                        ->label(__('event_insights.form.send_time'))
                        ->seconds(false)
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->getRecord()->reportSubscription()->updateOrCreate([], $data);

                    Notification::make()
                        ->success()
                        ->title(__('event_insights.notifications.settings_saved'))
                        ->send();
                }),

            Action::make('sendReportNow')
                ->label(__('event_insights.actions.send_now'))
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->requiresConfirmation()
                ->modalDescription(__('event_insights.actions.send_now_confirm'))
                ->visible(fn () => filled($this->getRecord()->reportSubscription?->recipients))
                ->action(function () {
                    $recipients = $this->getRecord()->reportSubscription->recipients;

                    SendEventInsightsReport::dispatch(
                        $this->getRecord()->id,
                        $recipients,
                        Carbon::now()->subDays(7)->toIso8601String(),
                        Carbon::now()->toIso8601String(),
                    );

                    Notification::make()
                        ->success()
                        ->title(__('event_insights.notifications.send_queued'))
                        ->send();
                }),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            EventStatsOverview::class,
            EventBookingsTrendChart::class,
            EventTicketBreakdown::class,
        ];
    }
}