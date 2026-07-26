@php
    $t = fn (string $key, array $params = []) => __('signage.' . $key, $params, $lang);
    $eventTitle = $event->getTranslation('title', $lang);
    $meetingPoint = $signage->getTranslation('meeting_point', $lang);
    $welcomeMessage = $signage->getTranslation('welcome_message', $lang) ?: $t('default_welcome_message');
    $dir = $lang === 'ar' ? 'rtl' : 'ltr';
    $videoEmbedId = $signage->getVideoEmbedId();
@endphp

<div dir="{{ $dir }}" class="flex flex-col min-h-screen p-4 sm:p-6 gap-4 sm:gap-6">

    {{-- Header --}}
    <header class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="h-16 w-16 sm:h-20 sm:w-20 xl:h-24 xl:w-24 rounded-2xl bg-white/10 border border-white/20 backdrop-blur flex items-center justify-center overflow-hidden shrink-0">
                @if($signage->logo_path)
                    <img src="{{ asset('storage/' . $signage->logo_path) }}" alt="{{ $eventTitle }}" class="h-full w-full object-contain p-1.5">
                @elseif($event->image)
                    <img src="{{ asset('storage/' . $event->image) }}" alt="{{ $eventTitle }}" class="h-full w-full object-cover">
                @else
                    <span class="text-2xl xl:text-3xl font-extrabold">{{ mb_substr($eventTitle, 0, 1) }}</span>
                @endif
            </div>
            <h1 class="text-white/80 text-xl sm:text-3xl xl:text-4xl 2xl:text-5xl font-extrabold leading-tight">{{ $eventTitle }}</h1>
        </div>

        <div class="text-end shrink-0">
            <div class="text-white/80 text-3xl sm:text-4xl xl:text-5xl 2xl:text-6xl font-extrabold tabular-nums" x-text="clockTime"></div>
            <div class="text-white/80 text-xs sm:text-sm xl:text-base 2xl:text-lg mt-1" x-text="clockDate"></div>
        </div>
    </header>

    {{-- Next trip: full-width hero bar so the countdown is readable across the room --}}
    <div class="bg-black/30 backdrop-blur rounded-3xl p-5 sm:p-7 border border-white/10 shrink-0">
        <span class="inline-flex items-center gap-2 bg-amber-400 text-emerald-950 font-bold text-sm sm:text-base xl:text-lg px-4 py-1.5 rounded-full">
            {{ $t('next_trip') }}
        </span>

        @if($nextTrip)
            <div class="flex flex-wrap items-center justify-between gap-3 mt-4">
                <h2 class="text-white/85 text-xl sm:text-3xl xl:text-4xl 2xl:text-5xl font-extrabold">{{ $eventTitle }}</h2>
                <div class="flex items-center gap-2 text-sm sm:text-lg xl:text-xl 2xl:text-2xl font-semibold">
                    <span class="bg-white/15 px-3 py-1.5 sm:px-4 sm:py-2 rounded-lg">🕐 {{ $nextTrip['time_range'] }}</span>
                    <span class="bg-white/15 px-3 py-1.5 sm:px-4 sm:py-2 rounded-lg">🚌 {{ $nextTrip['label'][$lang] }}</span>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-8 sm:gap-20 py-3 sm:py-5">
                <div class="flex flex-col items-center justify-center h-20 w-20 sm:h-28 sm:w-28 xl:h-32 xl:w-32 2xl:h-36 2xl:w-36 rounded-full bg-white/10 border border-white/25 text-center px-2 shrink-0">
                    <span class="text-white/80 text-xl sm:text-3xl xl:text-4xl 2xl:text-5xl font-extrabold">{{ $nextTrip['booked_count'] }}</span>
                    <span class="text-[10px] sm:text-xs xl:text-sm 2xl:text-base text-white/70 mt-1 leading-tight">{{ $t('bookings_count') }}</span>
                </div>

                <div class="text-center">
                    <div class="text-white/85 text-6xl sm:text-9xl 2xl:text-[10rem] tabular-nums tracking-wider" x-text="countdownText">--:--</div>
                    <div class="flex justify-center gap-10 sm:gap-16 text-xs sm:text-base xl:text-lg 2xl:text-xl text-white/70 mt-1 sm:mt-2">
                        <span>{{ $t('minutes_label') }}</span>
                        <span>{{ $t('seconds_label') }}</span>
                    </div>
                </div>

                <div class="flex flex-col items-center justify-center h-20 w-20 sm:h-28 sm:w-28 xl:h-32 xl:w-32 2xl:h-36 2xl:w-36 rounded-full bg-white/10 border border-white/25 text-center px-2 shrink-0">
                    <span class="text-white/80 text-xl sm:text-3xl xl:text-4xl 2xl:text-5xl font-extrabold">{{ $nextTrip['remaining_seats'] }}</span>
                    <span class="text-[10px] sm:text-xs xl:text-sm 2xl:text-base text-white/70 mt-1 leading-tight">{{ $t('remaining_seats') }}</span>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-3">
                <div class="flex items-center gap-2 bg-amber-400/15 border border-amber-300/40 text-amber-200 rounded-xl px-4 py-2 sm:py-2.5 text-sm sm:text-lg xl:text-xl 2xl:text-2xl font-semibold">
                    🔊 {{ $t('ready_alert', ['label' => $nextTrip['label'][$lang]]) }}
                </div>
                <div class="flex items-center gap-2 bg-amber-500/90 text-emerald-950 rounded-xl px-4 py-2 sm:py-3 font-bold text-sm sm:text-lg xl:text-xl 2xl:text-2xl">
                    ⚠️ {{ $t('gathering_alert', ['minutes' => $signage->gathering_alert_minutes]) }}
                </div>
            </div>
        @else
            <div class="flex items-center justify-center text-white/70 text-lg sm:text-2xl xl:text-3xl font-semibold text-center py-6 sm:py-10">
                {{ $t('no_next_trip') }}
            </div>
        @endif
    </div>

    {{-- Main grid: looping promo video + upcoming trips --}}
    <div class="flex-1 grid grid-cols-1 lg:grid-cols-[62%_38%] gap-4 sm:gap-6 min-h-0">

        {{-- Promo video --}}
        <div class="relative bg-black rounded-3xl border border-white/10 overflow-hidden aspect-video lg:aspect-auto">
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
                        <span class="text-white/50 text-lg sm:text-2xl xl:text-3xl font-semibold text-center">{{ $eventTitle }}</span>
                    </div>
                </div>
            @endif
        </div>

        {{-- Upcoming trips --}}
        <div class="bg-black/20 backdrop-blur rounded-3xl p-4 sm:p-5 border border-white/10 flex flex-col gap-3 min-h-0">
            <h3 class="text-base sm:text-lg xl:text-xl 2xl:text-2xl font-extrabold px-1">{{ $t('upcoming_trips') }}</h3>

            <div class="flex-1 flex flex-col gap-3 overflow-y-auto pe-1">
                @forelse($upcomingTrips as $trip)
                    <div class="rounded-2xl bg-white text-emerald-950 p-3 sm:p-4 xl:p-5 flex items-center justify-between gap-3 shadow">
                        <div class="min-w-0">
                            <span @class([
                                'inline-flex items-center gap-1 text-xs xl:text-sm font-bold px-3 py-1 rounded-full mb-2',
                                'bg-emerald-100 text-emerald-700' => $trip['status'] === 'ready',
                                'bg-amber-100 text-amber-700' => $trip['status'] === 'soon',
                                'bg-gray-100 text-gray-500' => $trip['status'] === 'waiting',
                            ])>
                                @if($trip['status'] === 'ready') 🔔 @elseif($trip['status'] === 'soon') ⏳ @else 🕐 @endif
                                {{ $t('status.' . $trip['status']) }}
                            </span>
                            <p class="font-bold text-base sm:text-lg xl:text-xl truncate">{{ $eventTitle }}</p>
                            <p class="text-sm sm:text-base xl:text-lg text-gray-500 truncate">{{ $trip['label'][$lang] }}</p>
                        </div>
                        <div class="text-end shrink-0">
                            <p class="font-extrabold text-sm sm:text-base xl:text-lg 2xl:text-xl">{{ $trip['time_range'] }}</p>
                        </div>
                    </div>
                @empty
                    <div class="flex-1 flex items-center justify-center text-white/60 text-sm xl:text-base text-center">
                        {{ $t('no_upcoming_trips') }}
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <footer class="flex flex-col gap-3">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 bg-black/30 backdrop-blur rounded-2xl p-4 xl:p-5 border border-white/10 text-sm xl:text-base 2xl:text-lg">
            <div class="flex items-center gap-2 min-w-0">
                <span class="text-xl xl:text-2xl shrink-0">📍</span>
                <div class="min-w-0">
                    <p class="text-white/80 font-bold">{{ $t('meeting_point') }}</p>
                    <p class="text-white/70 truncate">{{ $meetingPoint ?: '—' }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2 min-w-0">
                <span class="text-xl xl:text-2xl shrink-0">⏱️</span>
                <div class="min-w-0">
                    <p class="text-white/80 font-bold">{{ $t('early_arrival', ['minutes' => $signage->early_arrival_minutes]) }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2 min-w-0">
                <span class="text-xl xl:text-2xl shrink-0">📞</span>
                <div class="min-w-0">
                    <p class="text-white/80 font-bold">{{ $t('contact_help') }}</p>
                    <p class="text-white/70" dir="ltr">{{ $signage->contact_phone ?: '—' }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2 min-w-0">
                @if($signage->qr_code_image_path)
                    <img src="{{ asset('storage/' . $signage->qr_code_image_path) }}" alt="QR" class="h-12 w-12 xl:h-14 xl:w-14 rounded-lg bg-white p-1 shrink-0">
                @else
                    <span class="text-xl xl:text-2xl shrink-0">🔗</span>
                @endif
                <p class="text-white/70 truncate">{{ $t('scan_qr') }}</p>
            </div>
        </div>

        <div class="bg-brand rounded-2xl px-6 py-3 xl:py-4 text-center text-white/80 font-bold text-sm sm:text-base xl:text-lg 2xl:text-xl">
            {{ $welcomeMessage }}
        </div>
    </footer>
</div>
