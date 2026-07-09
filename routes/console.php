<?php

use App\Console\Commands\CancelExpiredBookings;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(CancelExpiredBookings::class)->everyFiveMinutes();

Artisan::command('project:refresh', function () {
    $this->info('Cleaning everything...');
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    Artisan::call('filament:clear-cached-components');
    Artisan::call('icons:clear');

    $this->info('Rebuilding cache for production...');
    Artisan::call('config:cache');
    Artisan::call('route:cache');
    Artisan::call('view:cache');
    Artisan::call('filament:cache-components');

    $this->info('Project successfully refreshed!');
})->purpose('Clear and rebuild all Laravel and Filament caches');
