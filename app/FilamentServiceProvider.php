<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;

class FilamentServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Add RTL support for Filament
        FilamentAsset::register([
            Css::make('rtl-styles', resource_path('css/rtl.css'))
                ->loadedOnRequest(app()->getLocale() === 'ar'),
        ]);
    }
}
