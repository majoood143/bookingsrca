@php
    use App\Models\BookingSetting;

    $locale = app()->getLocale();
    $siteName = BookingSetting::get('site_name_' . $locale, config('app.name', 'Bookings'));
    $siteLogo = BookingSetting::get('site_logo');
    $favicon = BookingSetting::get('favicon');
    $primaryColor = BookingSetting::get('primary_color', '#05602b');
    $secondaryColor = BookingSetting::get('secondary_color', '#0da74c');

    $metaDescription = BookingSetting::get('seo_meta_description', '');
    $ogImage = BookingSetting::get('seo_og_image');

    $socialLinks = array_filter([
        'facebook' => BookingSetting::get('social_facebook_url'),
        'instagram' => BookingSetting::get('social_instagram_url'),
        'x' => BookingSetting::get('social_x_url'),
        'linkedin' => BookingSetting::get('social_linkedin_url'),
        'youtube' => BookingSetting::get('social_youtube_url'),
        'tiktok' => BookingSetting::get('social_tiktok_url'),
        'whatsapp' => BookingSetting::get('social_whatsapp_url'),
    ]);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale) }}" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="{{ $favicon ? asset('storage/' . $favicon) : asset('favicon.ico') }}" sizes="any">
    <title>{{ $siteName }} — {{ __('home.meta_title') }}</title>
    @if ($metaDescription)
        <meta name="description" content="{{ $metaDescription }}">
    @endif

    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $siteName }} — {{ __('home.meta_title') }}">
    <meta property="og:url" content="{{ url('/') }}">
    @if ($metaDescription)
        <meta property="og:description" content="{{ $metaDescription }}">
    @endif
    @if ($ogImage)
        <meta property="og:image" content="{{ asset('storage/' . $ogImage) }}">
    @endif

    @if (!empty($socialLinks))
        <script type="application/ld+json">
            {!! json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'Organization',
                'name' => $siteName,
                'url' => url('/'),
                'sameAs' => array_values($socialLinks),
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
        </script>
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50 min-h-screen" style="--color-brand: {{ $primaryColor }}; --color-brand-hover: {{ $secondaryColor }};">

    {{-- ── Header ── --}}
    <header class="bg-white border-b border-gray-100 sticky top-0 z-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between gap-4">
            <a href="{{ url('/') }}" class="flex items-center gap-3 min-w-0">
                <img src="{{ $siteLogo ? asset('storage/' . $siteLogo) : asset('storage/images/horizontalLogo-02.svg') }}"
                    alt="{{ $siteName }}" class="h-10 w-auto shrink-0">
                <span class="font-bold text-lg text-gray-900 truncate">{{ $siteName }}</span>
            </a>

            @if ($locale === 'ar')
                <a href="{{ route('lang.switch', 'en') }}"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-full transition-colors shrink-0">
                    🌐 {{ __('event_booking.switch_to_english') }}
                </a>
            @else
                <a href="{{ route('lang.switch', 'ar') }}"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-full transition-colors shrink-0">
                    🌐 {{ __('event_booking.switch_to_arabic') }}
                </a>
            @endif
        </div>
    </header>

    {{-- ── Hero ── --}}
    <div class="bg-gradient-to-r from-brand to-brand-hover text-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-14 sm:py-20 text-center">
            <h1 class="text-3xl sm:text-4xl font-bold leading-tight">{{ __('home.hero.heading') }}</h1>
            <p class="mt-3 text-base sm:text-lg text-white/90 max-w-2xl mx-auto">{{ __('home.hero.subheading') }}</p>
        </div>
    </div>

    {{-- ── Events ── --}}
    <main class="max-w-6xl mx-auto px-4 sm:px-6 py-12 sm:py-16">
        <div class="text-center mb-10">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ __('home.section.heading') }}</h2>
            <p class="mt-2 text-gray-500">{{ __('home.section.subheading') }}</p>
        </div>

        @if ($events->isEmpty())
            <div class="text-center py-16">
                <div class="text-6xl mb-4">🗓️</div>
                <h3 class="text-xl font-semibold text-gray-900">{{ __('home.empty_state.heading') }}</h3>
                <p class="mt-2 text-gray-500">{{ __('home.empty_state.description') }}</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($events as $event)
                    @php
                        $remaining = max(0, $event->max_attendees - ($event->booked_quantity ?? 0));
                        $soldOut = $remaining <= 0;
                        $minPrice = $event->ticketTypes->where('is_active', true)->min('price');
                    @endphp
                    <a href="{{ route('event.booking', $event->slug) }}"
                        class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg transition-shadow overflow-hidden flex flex-col">
                        <div class="relative aspect-video bg-gray-100 overflow-hidden">
                            @if ($event->image)
                                <img src="{{ asset('storage/' . $event->image) }}" alt="{{ $event->title }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-5xl">🎟️</div>
                            @endif

                            @if ($soldOut)
                                <span
                                    class="absolute top-3 {{ $locale === 'ar' ? 'left-3' : 'right-3' }} bg-gray-900/80 text-white text-xs font-semibold px-3 py-1 rounded-full">
                                    {{ __('home.card.sold_out') }}
                                </span>
                            @endif
                        </div>

                        <div class="p-5 flex flex-col grow">
                            <h3 class="font-bold text-lg text-gray-900 line-clamp-2">{{ $event->title }}</h3>

                            <div class="mt-2 space-y-1 text-sm text-gray-500">
                                <div class="flex items-center gap-1.5">
                                    <span>📅</span>
                                    <span>{{ $event->start_date->locale($locale)->translatedFormat('M d') }} –
                                        {{ $event->end_date->locale($locale)->translatedFormat('M d, Y') }}</span>
                                </div>
                                @if ($event->location)
                                    <div class="flex items-center gap-1.5 truncate">
                                        <span>📍</span>
                                        <span class="truncate">{{ $event->location }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between gap-3">
                                <div class="text-sm">
                                    @if (is_null($minPrice))
                                        &nbsp;
                                    @elseif ($minPrice <= 0)
                                        <span class="font-bold text-gray-900">{{ __('home.card.free') }}</span>
                                    @else
                                        <span class="text-gray-500">{{ __('home.card.from') }}</span>
                                        <span class="font-bold text-gray-900">@include('partials.currency-amount', ['amount' => $minPrice])</span>
                                    @endif
                                </div>

                                <span
                                    class="inline-flex items-center gap-1 px-4 py-2 rounded-xl text-sm font-semibold transition-colors {{ $soldOut ? 'bg-gray-100 text-gray-400' : 'bg-brand text-white group-hover:bg-brand-hover' }}">
                                    {{ $soldOut ? __('home.card.sold_out') : __('home.card.book_now') }}
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </main>

    {{-- ── Footer ── --}}
    <footer class="border-t border-gray-100 py-8">
        @if (!empty($socialLinks))
            <div class="flex flex-col items-center gap-3 mb-4">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">{{ __('home.footer.follow_us') }}</span>
                <div class="flex items-center gap-3">
                    @if (!empty($socialLinks['facebook']))
                        <a href="{{ $socialLinks['facebook'] }}" target="_blank" rel="noopener noreferrer"
                            aria-label="{{ __('home.footer.social.facebook') }}"
                            class="inline-flex items-center justify-center h-9 w-9 rounded-full bg-gray-100 text-gray-500 hover:bg-brand hover:text-white transition-colors">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" />
                            </svg>
                        </a>
                    @endif

                    @if (!empty($socialLinks['instagram']))
                        <a href="{{ $socialLinks['instagram'] }}" target="_blank" rel="noopener noreferrer"
                            aria-label="{{ __('home.footer.social.instagram') }}"
                            class="inline-flex items-center justify-center h-9 w-9 rounded-full bg-gray-100 text-gray-500 hover:bg-brand hover:text-white transition-colors">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" />
                            </svg>
                        </a>
                    @endif

                    @if (!empty($socialLinks['x']))
                        <a href="{{ $socialLinks['x'] }}" target="_blank" rel="noopener noreferrer"
                            aria-label="{{ __('home.footer.social.x') }}"
                            class="inline-flex items-center justify-center h-9 w-9 rounded-full bg-gray-100 text-gray-500 hover:bg-brand hover:text-white transition-colors">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M13.6823 10.6218L20.2391 3H18.6854L12.9921 9.61788L8.44486 3H3.2002L10.0765 13.0074L3.2002 21H4.75404L10.7663 14.0113L15.5685 21H20.8131L13.6823 10.6218ZM11.5541 13.0956L10.8574 12.0991L5.31391 4.16971H7.70053L12.1742 10.5689L12.8709 11.5655L18.6861 19.8835H16.2995L11.5541 13.0956Z" />
                            </svg>
                        </a>
                    @endif

                    @if (!empty($socialLinks['linkedin']))
                        <a href="{{ $socialLinks['linkedin'] }}" target="_blank" rel="noopener noreferrer"
                            aria-label="{{ __('home.footer.social.linkedin') }}"
                            class="inline-flex items-center justify-center h-9 w-9 rounded-full bg-gray-100 text-gray-500 hover:bg-brand hover:text-white transition-colors">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M19 3a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h14zM8.339 18.337V8.667H5.667v9.67h2.672zM7.003 7.5c.929 0 1.5-.615 1.5-1.383-.017-.784-.571-1.384-1.482-1.384-.911 0-1.5.6-1.5 1.384 0 .768.571 1.383 1.465 1.383h.017zM18.336 18.337V12.999c0-2.865-1.532-4.199-3.573-4.199-1.647 0-2.383.906-2.795 1.541v-1.32h-2.671c.035.734 0 9.67 0 9.67h2.671v-5.401c0-.288.021-.577.106-.784.232-.577.762-1.175 1.652-1.175 1.166 0 1.633.886 1.633 2.187v5.173h2.671z" />
                            </svg>
                        </a>
                    @endif

                    @if (!empty($socialLinks['youtube']))
                        <a href="{{ $socialLinks['youtube'] }}" target="_blank" rel="noopener noreferrer"
                            aria-label="{{ __('home.footer.social.youtube') }}"
                            class="inline-flex items-center justify-center h-9 w-9 rounded-full bg-gray-100 text-gray-500 hover:bg-brand hover:text-white transition-colors">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M19.812 5.418c.861.23 1.538.907 1.768 1.768C21.998 8.746 22 12 22 12s0 3.255-.418 4.814a2.504 2.504 0 01-1.768 1.768c-1.56.419-7.814.419-7.814.419s-6.255 0-7.814-.419a2.505 2.505 0 01-1.768-1.768C2 15.255 2 12 2 12s0-3.255.417-4.814a2.507 2.507 0 011.768-1.768C5.744 5 11.998 5 11.998 5s6.255 0 7.814.418ZM15.194 12 10 15V9l5.194 3Z" />
                            </svg>
                        </a>
                    @endif

                    @if (!empty($socialLinks['tiktok']))
                        <a href="{{ $socialLinks['tiktok'] }}" target="_blank" rel="noopener noreferrer"
                            aria-label="{{ __('home.footer.social.tiktok') }}"
                            class="inline-flex items-center justify-center h-9 w-9 rounded-full bg-gray-100 text-gray-500 hover:bg-brand hover:text-white transition-colors">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M16.6 5.82s.51.5 0 0A4.278 4.278 0 0115.54 3h-3.09v12.4a2.592 2.592 0 01-2.59 2.5c-1.42 0-2.6-1.16-2.6-2.6 0-1.72 1.66-3.01 3.37-2.48V9.66c-3.45-.46-6.47 2.22-6.47 5.64 0 3.33 2.76 5.7 5.69 5.7 3.14 0 5.69-2.55 5.69-5.7V9.01a7.35 7.35 0 004.3 1.38V7.3s-1.88.09-3.24-1.48z" />
                            </svg>
                        </a>
                    @endif

                    @if (!empty($socialLinks['whatsapp']))
                        <a href="{{ $socialLinks['whatsapp'] }}" target="_blank" rel="noopener noreferrer"
                            aria-label="{{ __('home.footer.social.whatsapp') }}"
                            class="inline-flex items-center justify-center h-9 w-9 rounded-full bg-gray-100 text-gray-500 hover:bg-brand hover:text-white transition-colors">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M20.52 3.449C12.831-3.984.106 1.407.101 11.893c0 2.096.549 4.14 1.595 5.945L0 24l6.335-1.652a11.882 11.882 0 005.723 1.467h.005c9.68 0 15.795-10.617 12.457-19.366zm-8.483 17.518h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.976.999-3.648-.235-.374a9.86 9.86 0 01-1.51-5.257c.004-5.45 4.437-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.887-9.886 9.887zm5.422-7.403c-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.148-.669.15-.198.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.149-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.372-.025-.521-.074-.148-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01a1.093 1.093 0 00-.792.372c-.272.297-1.04 1.016-1.04 2.479s1.065 2.876 1.213 3.074c.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z" />
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
        @endif

        <p class="text-center text-sm text-gray-400">© {{ now()->year }} {{ $siteName }}. {{ __('home.footer.rights') }}</p>
    </footer>

</body>

</html>
