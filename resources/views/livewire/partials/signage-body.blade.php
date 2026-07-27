@php
    $t = fn (string $key, array $params = []) => __('signage.' . $key, $params, $lang);
    $eventTitle = $event->getTranslation('title', $lang);
    $meetingPoint = $signage->getTranslation('meeting_point', $lang);
    $welcomeMessage = $signage->getTranslation('welcome_message', $lang) ?: $t('default_welcome_message');
    $dir = $lang === 'ar' ? 'rtl' : 'ltr';
    $videoEmbedId = $signage->getVideoEmbedId();
@endphp

<div dir="{{ $dir }}" class="flex flex-col h-screen overflow-hidden p-3 sm:p-5 gap-3 sm:gap-4">

    {{-- Header --}}
    <header class="flex items-center justify-between gap-4 shrink-0">
        <div class="flex items-center gap-3 min-w-0">
            <div class="h-12 w-12 sm:h-16 sm:w-16 rounded-2xl bg-white/10 border border-white/20 backdrop-blur flex items-center justify-center overflow-hidden shrink-0">
                @if($signage->logo_path)
                    <img src="{{ asset('storage/' . $signage->logo_path) }}" alt="{{ $eventTitle }}" class="h-full w-full object-contain p-1.5">
                @elseif($event->image)
                    <img src="{{ asset('storage/' . $event->image) }}" alt="{{ $eventTitle }}" class="h-full w-full object-cover">
                @else
                    <span class="text-xl sm:text-2xl font-extrabold">{{ mb_substr($eventTitle, 0, 1) }}</span>
                @endif
            </div>
            <h1 class="text-white/80 text-lg sm:text-2xl xl:text-3xl font-extrabold leading-tight truncate">{{ $eventTitle }}</h1>
        </div>

        <div class="text-end shrink-0">
            <div class="text-white/80 text-2xl sm:text-3xl xl:text-4xl font-extrabold tabular-nums" x-text="clockTime"></div>
            <div class="text-white/80 text-[11px] sm:text-xs xl:text-sm mt-0.5" x-text="clockDate"></div>
        </div>
    </header>

    {{-- Next trip: full-width hero bar so the countdown is readable across the room --}}
    <div class="bg-black/30 backdrop-blur rounded-2xl sm:rounded-3xl p-3 sm:p-5 border border-white/10 shrink-0">
        <span class="inline-flex items-center gap-2 bg-amber-400 text-emerald-950 font-bold text-xs sm:text-sm px-3 py-1 rounded-full">
            {{ $t('next_trip') }}
        </span>

        @if($nextTrip)
            <div class="flex flex-wrap items-center justify-between gap-2 mt-2 sm:mt-3">
                <h2 class="text-white/85 text-lg sm:text-2xl xl:text-3xl font-extrabold truncate">{{ $eventTitle }}</h2>
                <div class="flex items-center gap-2 text-xs sm:text-base xl:text-lg font-semibold">
                    <span class="bg-white/15 px-2.5 py-1 sm:px-3 sm:py-1.5 rounded-lg">🕐 {{ $nextTrip['time_range'] }}</span>
                    <span class="bg-white/15 px-2.5 py-1 sm:px-3 sm:py-1.5 rounded-lg">🚌 {{ $nextTrip['label'][$lang] }}</span>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-6 sm:gap-14 py-2 sm:py-3">
                <div class="flex flex-col items-center justify-center h-14 w-14 sm:h-20 sm:w-20 xl:h-24 xl:w-24 rounded-full bg-white/10 border border-white/25 text-center px-2 shrink-0">
                    <span class="text-white/80 text-base sm:text-2xl xl:text-3xl font-extrabold">{{ $nextTrip['booked_count'] }}</span>
                    <span class="text-[9px] sm:text-[11px] xl:text-xs text-white/70 mt-0.5 leading-tight">{{ $t('bookings_count') }}</span>
                </div>

                <div class="text-center">
                    <div class="text-white/85 text-4xl sm:text-7xl xl:text-8xl tabular-nums tracking-wider" x-text="countdownText">--:--</div>
                    <div class="flex justify-center gap-8 sm:gap-14 text-[11px] sm:text-sm xl:text-base text-white/70 mt-0.5 sm:mt-1">
                        <span>{{ $t('minutes_label') }}</span>
                        <span>{{ $t('seconds_label') }}</span>
                    </div>
                </div>

                <div class="flex flex-col items-center justify-center h-14 w-14 sm:h-20 sm:w-20 xl:h-24 xl:w-24 rounded-full bg-white/10 border border-white/25 text-center px-2 shrink-0">
                    <span class="text-white/80 text-base sm:text-2xl xl:text-3xl font-extrabold">{{ $nextTrip['remaining_seats'] }}</span>
                    <span class="text-[9px] sm:text-[11px] xl:text-xs text-white/70 mt-0.5 leading-tight">{{ $t('remaining_seats') }}</span>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-2 sm:gap-3">
                <div class="flex items-center gap-2 bg-amber-400/15 border border-amber-300/40 text-amber-200 rounded-xl px-3 py-1.5 sm:py-2 text-xs sm:text-base xl:text-lg font-semibold">
                    🔊 {{ $t('ready_alert', ['label' => $nextTrip['label'][$lang]]) }}
                </div>
                <div class="flex items-center gap-2 bg-amber-500/90 text-emerald-950 rounded-xl px-3 py-1.5 sm:py-2 text-xs sm:text-base xl:text-lg font-bold">
                    ⚠️ {{ $t('gathering_alert', ['minutes' => $signage->gathering_alert_minutes]) }}
                </div>
            </div>
        @else
            <div class="flex items-center justify-center text-white/70 text-base sm:text-xl xl:text-2xl font-semibold text-center py-4 sm:py-6">
                {{ $t('no_next_trip') }}
            </div>
        @endif
    </div>

    {{-- Main grid: looping promo video + upcoming trips --}}
    <div class="flex-1 grid grid-cols-1 lg:grid-cols-[62%_38%] gap-3 sm:gap-4 min-h-0">

        {{-- Promo video --}}
        <div class="relative bg-black rounded-2xl sm:rounded-3xl border border-white/10 overflow-hidden aspect-video lg:aspect-auto min-h-0">
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
                        <span class="text-white/50 text-base sm:text-xl xl:text-2xl font-semibold text-center">{{ $eventTitle }}</span>
                    </div>
                </div>
            @endif
        </div>

        {{-- Upcoming trips --}}
        <div class="bg-black/20 backdrop-blur rounded-2xl sm:rounded-3xl p-3 sm:p-4 border border-white/10 flex flex-col gap-2 min-h-0">
            <h3 class="text-sm sm:text-base xl:text-lg font-extrabold px-1 shrink-0">{{ $t('upcoming_trips') }}</h3>

            <div class="flex-1 flex flex-col gap-2 overflow-y-auto pe-1 min-h-0">
                @forelse($upcomingTrips as $trip)
                    <div class="rounded-xl sm:rounded-2xl bg-white text-emerald-950 p-2.5 sm:p-3 flex items-center justify-between gap-3 shadow shrink-0">
                        <div class="min-w-0">
                            <span @class([
                                'inline-flex items-center gap-1 text-[10px] sm:text-xs font-bold px-2.5 py-0.5 rounded-full mb-1',
                                'bg-emerald-100 text-emerald-700' => $trip['status'] === 'ready',
                                'bg-amber-100 text-amber-700' => $trip['status'] === 'soon',
                                'bg-gray-100 text-gray-500' => $trip['status'] === 'waiting',
                            ])>
                                @if($trip['status'] === 'ready') 🔔 @elseif($trip['status'] === 'soon') ⏳ @else 🕐 @endif
                                {{ $t('status.' . $trip['status']) }}
                            </span>
                            <p class="font-bold text-sm sm:text-base truncate">{{ $eventTitle }}</p>
                            <p class="text-xs sm:text-sm text-gray-500 truncate">{{ $trip['label'][$lang] }}</p>
                        </div>
                        <div class="text-end shrink-0">
                            <p class="font-extrabold text-xs sm:text-sm xl:text-base">{{ $trip['time_range'] }}</p>
                        </div>
                    </div>
                @empty
                    <div class="flex-1 flex items-center justify-center text-white/60 text-xs sm:text-sm text-center">
                        {{ $t('no_upcoming_trips') }}
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <footer class="flex flex-col gap-2 shrink-0">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3 bg-black/30 backdrop-blur rounded-xl sm:rounded-2xl p-2.5 sm:p-3 border border-white/10 text-xs sm:text-sm">
            <div class="flex items-center gap-2 min-w-0">
                <span class="text-base sm:text-lg shrink-0">📍</span>
                <div class="min-w-0">
                    <p class="text-white/80 font-bold truncate">{{ $t('meeting_point') }}</p>
                    <p class="text-white/70 truncate">{{ $meetingPoint ?: '—' }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2 min-w-0">
                <span class="text-base sm:text-lg shrink-0">⏱️</span>
                <div class="min-w-0">
                    <p class="text-white/80 font-bold truncate">{{ $t('early_arrival', ['minutes' => $signage->early_arrival_minutes]) }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2 min-w-0">
                <span class="text-base sm:text-lg shrink-0">📞</span>
                <div class="min-w-0">
                    <p class="text-white/80 font-bold truncate">{{ $t('contact_help') }}</p>
                    <p class="text-white/70 truncate" dir="ltr">{{ $signage->contact_phone ?: '—' }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2 min-w-0">
                @if($signage->qr_code_image_path)
                    <img src="{{ asset('storage/' . $signage->qr_code_image_path) }}" alt="QR" class="h-8 w-8 sm:h-10 sm:w-10 rounded-lg bg-white p-1 shrink-0">
                @else
                    <span class="text-base sm:text-lg shrink-0">🔗</span>
                @endif
                <p class="text-white/70 truncate">{{ $t('scan_qr') }}</p>
            </div>
        </div>

        <div class="bg-brand rounded-xl sm:rounded-2xl px-4 py-2 sm:py-2.5 text-center text-white/80 font-bold text-xs sm:text-sm xl:text-base">
            {{ $welcomeMessage }}
        </div>
    </footer>
</div>
