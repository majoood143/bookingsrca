@php
    use App\Models\BookingSetting;

    $locale = app()->getLocale();
    $siteName = BookingSetting::get('site_name_' . $locale, config('app.name', 'Bookings'));
    $siteLogo = BookingSetting::get('site_logo');
    $siteLogoUrl = $siteLogo ? asset('storage/' . $siteLogo) : asset('storage/images/horizontalLogo-02.svg');
@endphp
<header class="bg-white border-b border-gray-100 sticky top-0 z-20">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between gap-4">
        <a href="{{ url('/') }}" class="flex items-center gap-3 min-w-0 shrink-0">
            <img src="{{ $siteLogoUrl }}" alt="{{ $siteName }}" class="h-9 w-auto shrink-0">
            <span class="font-bold text-lg text-gray-900 truncate hidden sm:inline">{{ $siteName }}</span>
        </a>

        <a href="{{ route('shop.index') }}"
            class="text-sm font-semibold {{ request()->routeIs('shop.*') ? 'text-brand' : 'text-gray-600 hover:text-brand' }} transition-colors">
            {{ __('shop.nav.shop') }}
        </a>

        <div class="ms-auto flex items-center gap-2 shrink-0">
            @if ($locale === 'ar')
                <a href="{{ route('lang.switch', 'en') }}"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-full transition-colors">
                    🌐 {{ __('event_booking.switch_to_english') }}
                </a>
            @else
                <a href="{{ route('lang.switch', 'ar') }}"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-full transition-colors">
                    🌐 {{ __('event_booking.switch_to_arabic') }}
                </a>
            @endif

            <livewire:shop.cart-drawer />
        </div>
    </div>
</header>
