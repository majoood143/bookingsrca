<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class EnsureAppIsInstalled
{
    // This app's Livewire update/script routes are rerouted under an
    // "/events" prefix in AppServiceProvider (for a subdirectory deployment),
    // so both the default and custom paths need to stay reachable pre-install.
    protected const ALLOWED_WHEN_NOT_INSTALLED = ['install', 'livewire/*', 'events/livewire/*', 'up'];

    public function handle(Request $request, Closure $next): Response
    {
        $installed = self::isInstalled();

        $isLivewireRequest = $request->hasHeader('X-Livewire') || $request->is(...self::ALLOWED_WHEN_NOT_INSTALLED);

        if (! $installed && ! $isLivewireRequest) {
            return redirect('/install');
        }

        if ($installed && $request->is('install')) {
            return redirect('/admin/login');
        }

        return $next($request);
    }

    // The marker file alone is too fragile (a test run or a wiped storage/
    // deletes it), and without it anyone could re-run the wizard and create a
    // super admin. A super admin already existing also counts as installed —
    // except for that admin themselves, who is logged in while finishing the wizard.
    public static function isInstalled(): bool
    {
        if (file_exists(storage_path('installed'))) {
            return true;
        }

        try {
            return Schema::hasTable('model_has_roles')
                && User::role('super_admin')->exists()
                && ! auth()->user()?->hasRole('super_admin');
        } catch (\Throwable) {
            return false; // no database yet: a genuinely fresh install
        }
    }
}
