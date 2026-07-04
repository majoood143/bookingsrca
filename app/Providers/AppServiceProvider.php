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

        // Force HTTPS if you are on production SSL
        // if (config('app.env') === 'production' || str_contains(config('app.url'), 'https://')) {
        //     URL::forceScheme('https');
        // }

        // Force Laravel to generate asset/action URLs with the /events prefix.
        // This is not gated by APP_ENV because the app is always served from the
        // /events subdirectory via an Apache Alias, regardless of environment.
        URL::forceRootUrl(config('app.url'));

        // Direct Livewire to send its network polling/submits through the alias
        Livewire::setUpdateRoute(function ($handle) {
            // return Route::post('/events/livewire/update', $handle);
            return Route::post('/events/livewire/update', $handle)->middleware('web');
        });

        // Livewire::setScriptRoute(function ($handle) {
        //     return Route::get('/events/livewire/livewire.js', $handle);
        // });

        Livewire::setScriptRoute(function ($handle) {
            $filename = config('app.debug') ? 'livewire.js' : 'livewire.min.js';

            return Route::get("/events/livewire/{$filename}", $handle);
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
