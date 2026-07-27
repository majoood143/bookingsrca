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

    {{-- Next trip: compact strip — keeps the countdown clear without competing with the video --}}
    <div class="bg-black/30 backdrop-blur rounded-2xl p-2.5 sm:p-3 border border-white/10 shrink-0">
        @if($nextTrip)
            <div class="flex flex-wrap items-center gap-3 sm:gap-5">
                <span class="inline-flex items-center gap-1.5 bg-amber-400 text-emerald-950 font-bold text-[11px] sm:text-xs px-2.5 py-1 rounded-full shrink-0">
                    {{ $t('next_trip') }}
                </span>

                <div class="min-w-0">
                    <p class="text-white/85 font-extrabold text-sm sm:text-lg xl:text-xl truncate">{{ $eventTitle }}</p>
                    <p class="text-white/60 text-[11px] sm:text-xs xl:text-sm truncate">🕐 {{ $nextTrip['time_range'] }} &middot; 🚌 {{ $nextTrip['label'][$lang] }}</p>
                </div>

                <div class="flex items-center gap-2 sm:gap-3 ms-auto shrink-0">
                    <div class="text-center px-1">
                        <div class="text-white/80 font-extrabold text-sm sm:text-lg xl:text-xl">{{ $nextTrip['booked_count'] }}</div>
                        <div class="text-[8px] sm:text-[9px] text-white/60 leading-tight">{{ $t('bookings_count') }}</div>
                    </div>

                    <div class="bg-white/10 border border-white/25 rounded-xl px-3 py-1 sm:px-4 sm:py-1.5 text-center">
                        <div class="text-white/90 text-xl sm:text-3xl xl:text-4xl font-extrabold tabular-nums leading-none" x-text="countdownText">--:--</div>
                        <div class="text-[8px] sm:text-[9px] text-white/60 mt-0.5">{{ $t('minutes_label') }} : {{ $t('seconds_label') }}</div>
                    </div>

                    <div class="text-center px-1">
                        <div class="text-white/80 font-extrabold text-sm sm:text-lg xl:text-xl">{{ $nextTrip['remaining_seats'] }}</div>
                        <div class="text-[8px] sm:text-[9px] text-white/60 leading-tight">{{ $t('remaining_seats') }}</div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 w-full lg:w-auto">
                    <span class="inline-flex items-center gap-1 bg-amber-400/15 border border-amber-300/40 text-amber-200 rounded-lg px-2.5 py-1 text-[11px] sm:text-xs xl:text-sm font-semibold">
                        🔊 {{ $t('ready_alert', ['label' => $nextTrip['label'][$lang]]) }}
                    </span>
                    <span class="inline-flex items-center gap-1 bg-amber-500/90 text-emerald-950 rounded-lg px-2.5 py-1 text-[11px] sm:text-xs xl:text-sm font-bold">
                        ⚠️ {{ $t('gathering_alert', ['minutes' => $signage->gathering_alert_minutes]) }}
                    </span>
                </div>
            </div>
        @else
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 bg-amber-400 text-emerald-950 font-bold text-[11px] sm:text-xs px-2.5 py-1 rounded-full shrink-0">
                    {{ $t('next_trip') }}
                </span>
                <span class="text-white/70 text-sm sm:text-base font-semibold">{{ $t('no_next_trip') }}</span>
            </div>
        @endif
    </div>

    {{-- Main grid: video is the primary focal point, upcoming trips as a companion list --}}
    <div class="flex-1 grid grid-cols-1 lg:grid-cols-[72%_28%] gap-3 sm:gap-4 min-h-0">

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
