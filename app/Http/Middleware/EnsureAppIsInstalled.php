<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAppIsInstalled
{
    // This app's Livewire update/script routes are rerouted under an
    // "/events" prefix in AppServiceProvider (for a subdirectory deployment),
    // so both the default and custom paths need to stay reachable pre-install.
    protected const ALLOWED_WHEN_NOT_INSTALLED = ['install', 'livewire/*', 'events/livewire/*', 'up'];

    public function handle(Request $request, Closure $next): Response
    {
        $installed = file_exists(storage_path('installed'));

        $isLivewireRequest = $request->hasHeader('X-Livewire') || $request->is(...self::ALLOWED_WHEN_NOT_INSTALLED);

        if (! $installed && ! $isLivewireRequest) {
            return redirect('/install');
        }

        if ($installed && $request->is('install')) {
            return redirect('/admin/login');
        }

        return $next($request);
    }
}
