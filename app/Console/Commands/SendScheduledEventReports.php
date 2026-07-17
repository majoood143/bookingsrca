<?php

namespace App\Console\Commands;

use App\Jobs\SendEventInsightsReport;
use App\Models\EventReportSubscription;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendScheduledEventReports extends Command
{
    protected $signature = 'event-reports:send-scheduled';

    protected $description = 'Dispatch the weekly event insights PDF report by email for every enabled subscription whose recurring day/time matches now.';

    public function handle(): int
    {
        $now = now();

        $due = EventReportSubscription::query()
            ->where('is_enabled', true)
            ->where('send_day', $now->dayOfWeek)
            ->get()
            ->filter(fn (EventReportSubscription $subscription) => Carbon::parse($subscription->send_time)->format('H:i') === $now->format('H:i'))
            ->filter(fn (EventReportSubscription $subscription) => ! $subscription->last_sent_at?->isToday());

        foreach ($due as $subscription) {
            SendEventInsightsReport::dispatch(
                $subscription->event_id,
                $subscription->recipients,
                $now->copy()->subDays(7)->toIso8601String(),
                $now->toIso8601String(),
            );
        }

        $this->info("Dispatched {$due->count()} scheduled event report(s).");

        return self::SUCCESS;
    }
}
