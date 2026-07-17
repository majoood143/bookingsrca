<?php

use App\Console\Commands\CancelExpiredBookings;
use App\Console\Commands\SendScheduledEventReports;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(CancelExpiredBookings::class)->everyFiveMinutes();

Schedule::command(SendScheduledEventReports::class)->everyMinute();

Artisan::command('project:refresh', function () {
    $runIfAvailable = function (string $command) {
        if (array_key_exists($command, Artisan::all())) {
            Artisan::call($command);
        }
    };

    $this->info('Cleaning everything...');
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    $runIfAvailable('filament:clear-cached-components');
    $runIfAvailable('icons:clear');

    $this->info('Rebuilding cache for production...');
    Artisan::call('config:cache');
    Artisan::call('route:cache');
    Artisan::call('view:cache');
    $runIfAvailable('filament:cache-components');

    $this->info('Project successfully refreshed!');
})->purpose('Clear and rebuild all Laravel and Filament caches');
