<?php

namespace App\Providers\Filament;

use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use App\Filament\Widgets\BookingOverviewWidget;
use App\Filament\Widgets\LowStockAlertsWidget;
use App\Filament\Widgets\RecentBookingsWidget;
use App\Filament\Widgets\RevenueChartWidget;
use App\Filament\Widgets\TicketStockWidget;
use App\Filament\Widgets\UpcomingEventsWidget;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use ShuvroRoy\FilamentSpatieLaravelHealth\FilamentSpatieLaravelHealthPlugin;
use ShuvroRoy\FilamentSpatieLaravelBackup\FilamentSpatieLaravelBackupPlugin;
use Joaopaulolndev\FilamentEditProfile\FilamentEditProfilePlugin;
use Swis\Filament\Backgrounds\FilamentBackgroundsPlugin;
use Swis\Filament\Backgrounds\ImageProviders\MyImages;
use Backstage\FilamentMails\Facades\FilamentMails;
use Backstage\FilamentMails\FilamentMailsPlugin;
use App\Filament\Pages\Auth\Login;
use MarcoGermani87\FilamentCaptcha\FilamentCaptcha;
use Croustibat\FilamentJobsMonitor\FilamentJobsMonitorPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->profile()
            ->passwordReset()
            ->font('Almarai', url: asset('fonts/almarai/almarai.css'), provider: LocalFontProvider::class)
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->brandLogo(fn() => view('filament.admin.logo'))
            ->brandLogoHeight('4rem')
            //->routes(fn() => FilamentMails::routes())
            ->colors([
                'primary' => Color::Green,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                //Widgets\AccountWidget::class,
                //Widgets\FilamentInfoWidget::class,
                BookingOverviewWidget::class,
                LowStockAlertsWidget::class,
                RevenueChartWidget::class,
                TicketStockWidget::class,
                UpcomingEventsWidget::class,
                RecentBookingsWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->plugins([
                FilamentCaptcha::make(),
                FilamentShieldPlugin::make(),
                FilamentSpatieLaravelHealthPlugin::make()
                    ->authorize(fn() => auth()->user()?->can('View:HealthCheckResults') ?? false),
                //FilamentMailsPlugin::make(),
                \RickDBCN\FilamentEmail\FilamentEmail::make(),
                FilamentSpatieLaravelBackupPlugin::make(),
                // ->authorize(fn () => auth()->user()?->can('View:Backups') ?? false),
                FilamentBackgroundsPlugin::make()
                    ->showAttribution(false),
                FilamentEditProfilePlugin::make()
                    ->setIcon('heroicon-o-user')
                    ->shouldShowAvatarForm(
                        value: true,
                        directory: 'avatars', // image will be stored in 'storage/app/public/avatars
                        rules: 'mimes:jpeg,png|max:1024' //only accept jpeg and png files with a maximum size of 1MB
                    ),
                FilamentJobsMonitorPlugin::make()
                    ->navigationIcon('heroicon-o-queue-list')
                    ->navigationGroup('System')
                    ->navigationSort(99)
                    ->navigationCountBadge()
                    ->enablePruning()
                    ->pruningRetention(7),

            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
