<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Spatie\Health\Facades\Health;
use Spatie\Health\Checks\Checks\OptimizedAppCheck;
use Spatie\Health\Checks\Checks\DebugModeCheck;
use Spatie\Health\Checks\Checks\EnvironmentCheck;
use Spatie\Health\Checks\Checks\UsedDiskSpaceCheck;
use Spatie\Health\Checks\Checks\PingCheck;
use Spatie\Health\Checks\Checks\QueueCheck;
use Spatie\Health\Checks\Checks\DatabaseCheck;
use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Gate;
use RickDBCN\FilamentEmail\Models\Email;
use App\Policies\EmailPolicy;
use Croustibat\FilamentJobsMonitor\Models\QueueMonitor;
use App\Policies\QueueMonitorPolicy;
use App\Models\BookingSetting;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Email::class, EmailPolicy::class);
        Gate::policy(QueueMonitor::class, QueueMonitorPolicy::class);

        // shuvroroy/filament-spatie-laravel-backup checks this ability to show
        // the "Create Backup" button; tie it to the same permission that already
        // gates access to the Backups page.
        Gate::define('create-backup', fn ($user) => $user->can('View:Backups'));

        // dedoc/scramble only auto-allows /docs/api when APP_ENV=local; since this
        // app runs with APP_ENV=production even in dev, restrict docs to any
        // authenticated panel user (there is no public-facing auth guard/user).
        Gate::define('viewApiDocs', fn ($user) => (bool) $user);

        // Force HTTPS if you are on production SSL
        // if (config('app.env') === 'production' || str_contains(config('app.url'), 'https://')) {
        //     URL::forceScheme('https');
        // }

        // Force Laravel to generate asset/action URLs with the /events prefix.
        // This is not gated by APP_ENV because the app is always served from the
        // /events subdirectory via an Apache Alias, regardless of environment.
        URL::forceRootUrl(config('app.url'));

        // Apply the configurable timezone from booking settings, falling back
        // to config/app.php when the settings table isn't migrated yet.
        try {
            $timezone = BookingSetting::get('timezone');

            if ($timezone) {
                config(['app.timezone' => $timezone]);
                date_default_timezone_set($timezone);
            }
        } catch (\Throwable $e) {
            // Settings table not ready yet (e.g. during initial migration).
        }

        // Direct Livewire to send its network polling/submits through the alias
        Livewire::setUpdateRoute(function ($handle) {
            // return Route::post('/events/livewire/update', $handle);
            return Route::post('/events/livewire/update', $handle)->middleware('web');
        });

        // Livewire's own package provider boots after this one and unconditionally
        // re-registers its default (non-aliased) script route, silently overwriting
        // this override if set here directly. Registering it once every provider has
        // booted guarantees this one is the one Livewire actually keeps.
        $this->app->booted(function () {
            Livewire::setScriptRoute(function ($handle) {
                $filename = config('app.debug') ? 'livewire.js' : 'livewire.min.js';

                return Route::get("/events/livewire/{$filename}", $handle);
            });
        });

        //
        Health::checks([
            OptimizedAppCheck::new(),
            DebugModeCheck::new(),
            EnvironmentCheck::new(),
            UsedDiskSpaceCheck::new(),
            PingCheck::new()->url('https://www.google.com'),
            QueueCheck::new(),
            DatabaseCheck::new(),
        ]);

        // Add RTL support for Arabic
        if (app()->getLocale() === 'ar') {
            view()->share('direction', 'rtl');
        }
        // } else {
        //     view()->share('direction', 'ltr');
        // }

        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(['ar', 'en']) // also accepts a closure
                ->visible(insidePanels: true, outsidePanels: true);
        });
    }
}
