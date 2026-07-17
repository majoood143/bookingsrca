<?php

namespace App\Jobs;

use App\Mail\EventInsightsReport as EventInsightsReportMail;
use App\Models\Event;
use App\Models\EventReportSubscription;
use App\Support\EventInsightsReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Spatie\LaravelPdf\Facades\Pdf;

class SendEventInsightsReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $eventId,
        public array $recipients,
        public string $from,
        public string $to,
    ) {
    }

    public function handle(): void
    {
        $event = Event::findOrFail($this->eventId);
        $from = Carbon::parse($this->from);
        $to = Carbon::parse($this->to);
        $locale = app()->getLocale();

        $reportData = EventInsightsReport::build($event, $from, $to);

        $pdfContent = Pdf::view('reports.event-insights-pdf', [
            ...$reportData,
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
            ->withBrowsershot(fn ($browsershot) => $browsershot->waitForFunction('window.pdfReady === true', null, 15000))
            ->generatePdfContent();

        foreach ($this->recipients as $recipient) {
            Mail::to($recipient)->send(new EventInsightsReportMail($event, $reportData, $from, $to, $pdfContent));
        }

        EventReportSubscription::where('event_id', $event->id)->update(['last_sent_at' => now()]);
    }
}
