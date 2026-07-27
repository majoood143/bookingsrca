@php
    $t = fn (string $key, array $params = []) => __('signage.' . $key, $params, $lang);
    $eventTitle = $event->getTranslation('title', $lang);
    $meetingPoint = $signage->getTranslation('meeting_point', $lang);
    $welcomeMessage = $signage->getTranslation('welcome_message', $lang) ?: $t('default_welcome_message');
    $dir = $lang === 'ar' ? 'rtl' : 'ltr';
    $videoEmbedId = $signage->getVideoEmbedId();
@endphp

{{-- All sizing below reads from the --tv-fs-* / --tv-safe-* custom properties
defined once on the .tv-signage root (event-signage-dashboard.blade.php).
They're clamp()'d to viewport width, so this same markup scales continuously
across 1366x768, 1920x1080 and 4K instead of jumping between breakpoints. --}}
{{-- Height is set directly from --real-vh (not h-full/100%) because the
x-show wrapper this sits inside (event-signage-dashboard.blade.php) never
gets an explicit height, so a percentage height here would have nothing to
resolve against and silently collapse to auto. --}}
<div
    dir="{{ $dir }}"
    class="w-full flex flex-col overflow-hidden text-white"
    style="height: calc(var(--real-vh, 1vh) * 100); padding: var(--tv-safe-y) var(--tv-safe-x); gap: 1.2vh;"
>
    {{-- Header --}}
    <header class="flex items-center justify-between gap-4 shrink-0">
        <div class="flex items-center gap-3 min-w-0">
            <div class="h-[clamp(2.5rem,5vw,4rem)] w-[clamp(2.5rem,5vw,4rem)] rounded-2xl bg-white/10 border border-white/20 backdrop-blur flex items-center justify-center overflow-hidden shrink-0">
                @if($signage->logo_path)
                    <img src="{{ asset('storage/' . $signage->logo_path) }}" alt="{{ $eventTitle }}" class="h-full w-full object-contain p-1.5">
                @elseif($event->image)
                    <img src="{{ asset('storage/' . $event->image) }}" alt="{{ $eventTitle }}" class="h-full w-full object-cover">
                @else
                    <span class="text-(length:--tv-fs-md) font-extrabold">{{ mb_substr($eventTitle, 0, 1) }}</span>
                @endif
            </div>
            <h1 class="text-white/80 text-(length:--tv-fs-xl) font-extrabold leading-tight truncate">{{ $eventTitle }}</h1>
        </div>

        <div class="text-end shrink-0">
            <div class="text-white/80 text-(length:--tv-fs-clock) font-extrabold tabular-nums leading-none" x-text="clockTime"></div>
            <div class="text-white/80 text-(length:--tv-fs-xs) mt-1" x-text="clockDate"></div>
        </div>
    </header>

    {{-- Next trip: compact strip — keeps the countdown clear without competing with the video --}}
    <div class="bg-black/30 backdrop-blur rounded-[1.2vw] border border-white/10 shrink-0" style="padding: 1vh 1.2vw;">
        @if($nextTrip)
            <div class="flex flex-wrap items-center" style="gap: 1vw;">
                <span class="inline-flex items-center bg-amber-400 text-emerald-950 font-bold text-(length:--tv-fs-2xs) rounded-full shrink-0" style="padding: 0.4vh 0.9vw;">
                    {{ $t('next_trip') }}
                </span>

                <div class="min-w-0">
                    <p class="text-white/85 font-extrabold text-(length:--tv-fs-md) truncate">{{ $eventTitle }}</p>
                    <p class="text-white/60 text-(length:--tv-fs-2xs) truncate">🕐 {{ $nextTrip['time_range'] }} &middot; 🚌 {{ $nextTrip['label'][$lang] }}</p>
                </div>

                <div class="flex items-center ms-auto shrink-0" style="gap: 0.8vw;">
                    <div class="text-center">
                        <div class="text-white/80 font-extrabold text-(length:--tv-fs-md) leading-none">{{ $nextTrip['booked_count'] }}</div>
                        <div class="text-(length:--tv-fs-2xs) text-white/60 leading-tight">{{ $t('bookings_count') }}</div>
                    </div>

                    <div class="bg-white/10 border border-white/25 rounded-xl text-center" style="padding: 0.4vh 1vw;">
                        <div class="text-white/90 text-(length:--tv-fs-countdown) font-extrabold tabular-nums leading-none" x-text="countdownText">--:--</div>
                        <div class="text-(length:--tv-fs-2xs) text-white/60 mt-0.5">{{ $t('minutes_label') }} : {{ $t('seconds_label') }}</div>
                    </div>

                    <div class="text-center">
                        <div class="text-white/80 font-extrabold text-(length:--tv-fs-md) leading-none">{{ $nextTrip['remaining_seats'] }}</div>
                        <div class="text-(length:--tv-fs-2xs) text-white/60 leading-tight">{{ $t('remaining_seats') }}</div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center w-full lg:w-auto" style="gap: 0.6vw;">
                    <span class="inline-flex items-center bg-amber-400/15 border border-amber-300/40 text-amber-200 rounded-lg text-(length:--tv-fs-xs) font-semibold" style="gap: 0.3vw; padding: 0.35vh 0.8vw;">
                        🔊 {{ $t('ready_alert', ['label' => $nextTrip['label'][$lang]]) }}
                    </span>
                    <span class="inline-flex items-center bg-amber-500/90 text-emerald-950 rounded-lg text-(length:--tv-fs-xs) font-bold" style="gap: 0.3vw; padding: 0.35vh 0.8vw;">
                        ⚠️ {{ $t('gathering_alert', ['minutes' => $signage->gathering_alert_minutes]) }}
                    </span>
                </div>
            </div>
        @else
            <div class="flex items-center" style="gap: 1vw;">
                <span class="inline-flex items-center bg-amber-400 text-emerald-950 font-bold text-(length:--tv-fs-2xs) rounded-full shrink-0" style="padding: 0.4vh 0.9vw;">
                    {{ $t('next_trip') }}
                </span>
                <span class="text-white/70 text-(length:--tv-fs-md) font-semibold">{{ $t('no_next_trip') }}</span>
            </div>
        @endif
    </div>

    {{-- Main grid: video is the primary focal point, upcoming trips as a companion list --}}
    <div class="flex-1 grid grid-cols-1 lg:grid-cols-[72%_28%] min-h-0" style="gap: 1.2vw;">

        {{-- Promo video --}}
        <div class="relative bg-black rounded-[1.2vw] border border-white/10 overflow-hidden aspect-video lg:aspect-auto min-h-0">
            @if($videoEmbedId)
                <iframe
                    class="absolute inset-0 w-full h-full pointer-events-none"
                    src="https://www.youtube.com/embed/{{ $videoEmbedId }}?autoplay=1&mute=1&loop=1&playlist={{ $videoEmbedId }}&controls=0&showinfo=0&rel=0&modestbranding=1&playsinline=1"
                    title="{{ $eventTitle }}"
                    frameborder="0"
                    allow="autoplay; encrypted-media"
                    referrerpolicy="strict-origin-when-cross-origin"
                ></iframe>
            @else
                <div class="absolute inset-0">
                    @if($event->image)
                        <img src="{{ asset('storage/' . $event->image) }}" alt="{{ $eventTitle }}" class="w-full h-full object-cover opacity-30">
                    @endif
                    <div class="absolute inset-0 flex items-center justify-center px-6">
                        <span class="text-white/50 text-(length:--tv-fs-lg) font-semibold text-center">{{ $eventTitle }}</span>
                    </div>
                </div>
            @endif
        </div>

        {{-- Upcoming trips --}}
        <div class="bg-black/20 backdrop-blur rounded-[1.2vw] border border-white/10 flex flex-col min-h-0" style="padding: 1.2vh 1vw; gap: 0.8vh;">
            <h3 class="text-(length:--tv-fs-md) font-extrabold px-1 shrink-0">{{ $t('upcoming_trips') }}</h3>

            <div class="tv-scroll-hidden flex-1 flex flex-col overflow-y-auto min-h-0" style="gap: 0.8vh;">
                @forelse($upcomingTrips as $trip)
                    <div class="rounded-[1vw] bg-white text-emerald-950 flex items-center justify-between shadow shrink-0" style="padding: 1vh 0.9vw; gap: 0.8vw;">
                        <div class="min-w-0">
                            <span @class([
                                'inline-flex items-center text-(length:--tv-fs-2xs) font-bold rounded-full mb-1',
                                'bg-emerald-100 text-emerald-700' => $trip['status'] === 'ready',
                                'bg-amber-100 text-amber-700' => $trip['status'] === 'soon',
                                'bg-gray-100 text-gray-500' => $trip['status'] === 'waiting',
                            ]) style="gap: 0.25vw; padding: 0.15vh 0.7vw;">
                                @if($trip['status'] === 'ready') 🔔 @elseif($trip['status'] === 'soon') ⏳ @else 🕐 @endif
                                {{ $t('status.' . $trip['status']) }}
                            </span>
                            <p class="font-bold text-(length:--tv-fs-base) truncate">{{ $eventTitle }}</p>
                            <p class="text-(length:--tv-fs-sm) text-gray-500 truncate">{{ $trip['label'][$lang] }}</p>
                        </div>
                        <div class="text-end shrink-0">
                            <p class="font-extrabold text-(length:--tv-fs-sm)">{{ $trip['time_range'] }}</p>
                        </div>
                    </div>
                @empty
                    <div class="flex-1 flex items-center justify-center text-white/60 text-(length:--tv-fs-sm) text-center">
                        {{ $t('no_upcoming_trips') }}
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <footer class="flex flex-col shrink-0" style="gap: 0.8vh;">
        <div class="grid grid-cols-2 lg:grid-cols-4 bg-black/30 backdrop-blur rounded-[1vw] border border-white/10 text-(length:--tv-fs-xs)" style="gap: 0.6vw; padding: 1vh 1vw;">
            <div class="flex items-center gap-2 min-w-0">
                <span class="text-(length:--tv-fs-md) shrink-0">📍</span>
                <div class="min-w-0">
                    <p class="text-white/80 font-bold truncate">{{ $t('meeting_point') }}</p>
                    <p class="text-white/70 truncate">{{ $meetingPoint ?: '—' }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2 min-w-0">
                <span class="text-(length:--tv-fs-md) shrink-0">⏱️</span>
                <div class="min-w-0">
                    <p class="text-white/80 font-bold truncate">{{ $t('early_arrival', ['minutes' => $signage->early_arrival_minutes]) }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2 min-w-0">
                <span class="text-(length:--tv-fs-md) shrink-0">📞</span>
                <div class="min-w-0">
                    <p class="text-white/80 font-bold truncate">{{ $t('contact_help') }}</p>
                    <p class="text-white/70 truncate" dir="ltr">{{ $signage->contact_phone ?: '—' }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2 min-w-0">
                @if($signage->qr_code_image_path)
                    <img src="{{ asset('storage/' . $signage->qr_code_image_path) }}" alt="QR" class="h-[clamp(1.75rem,3vw,2.75rem)] w-[clamp(1.75rem,3vw,2.75rem)] rounded-lg bg-white p-1 shrink-0">
                @else
                    <span class="text-(length:--tv-fs-md) shrink-0">🔗</span>
                @endif
                <p class="text-white/70 truncate">{{ $t('scan_qr') }}</p>
            </div>
        </div>

        <div class="bg-brand rounded-[1vw] text-center text-white/80 font-bold text-(length:--tv-fs-sm)" style="padding: 0.9vh 1.5vw;">
            {{ $welcomeMessage }}
        </div>
    </footer>
</div>
