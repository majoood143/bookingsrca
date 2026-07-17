<?php

namespace App\Mail;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class EventInsightsReport extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Event $event,
        public array $reportData,
        public Carbon $periodFrom,
        public Carbon $periodTo,
        public string $pdfContent,
    ) {
    }

    public function build()
    {
        $locale = app()->getLocale();
        $title = $this->event->getTranslation('title', $locale);
        $filename = __('event_insights.export.filename') . '-' . $this->event->slug . '-' . $this->periodTo->format('Y-m-d') . '.pdf';

        return $this->locale($locale)
            ->subject(__('event_insights.email.subject', ['event' => $title], $locale))
            ->view('emails.event-insights-report')
            ->with([
                'event' => $this->event,
                'reportData' => $this->reportData,
                'from' => $this->periodFrom,
                'to' => $this->periodTo,
                'locale' => $locale,
            ])
            ->attachData($this->pdfContent, $filename, ['mime' => 'application/pdf']);
    }
}
