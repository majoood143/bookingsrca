@php
    $t = fn (string $key, array $params = []) => __('signage.' . $key, $params, $lang);
    $eventTitle = $event->getTranslation('title', $lang);
    $meetingPoint = $signage->getTranslation('meeting_point', $lang);
    $welcomeMessage = $signage->getTranslation('welcome_message', $lang) ?: $t('default_welcome_message');
    $dir = $lang === 'ar' ? 'rtl' : 'ltr';
@endphp

<div dir="{{ $dir }}" class="flex flex-col min-h-screen p-4 sm:p-6 gap-4 sm:gap-6">

    {{-- Header --}}
    <header class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="h-16 w-16 rounded-2xl bg-white/10 border border-white/20 backdrop-blur flex items-center justify-center overflow-hidden shrink-0">
                @if($signage->logo_path)
                    <img src="{{ asset('storage/' . $signage->logo_path) }}" alt="{{ $eventTitle }}" class="h-full w-full object-contain p-1.5">
                @elseif($event->image)
                    <img src="{{ asset('storage/' . $event->image) }}" alt="{{ $eventTitle }}" class="h-full w-full object-cover">
                @else
                    <span class="text-2xl font-extrabold">{{ mb_substr($eventTitle, 0, 1) }}</span>
                @endif
            </div>
            <h1 class="text-white/80 text-xl sm:text-3xl font-extrabold leading-tight">{{ $eventTitle }}</h1>
        </div>

        <div class="text-end shrink-0">
            <div class="text-white/80 text-3xl sm:text-4xl font-extrabold tabular-nums" x-text="clockTime"></div>
            <div class="text-white/80 text-xs sm:text-sm mt-1" x-text="clockDate"></div>
        </div>
    </header>

    {{-- Main grid --}}
    <div class="flex-1 grid grid-cols-1 lg:grid-cols-[65%_35%] gap-4 sm:gap-6 min-h-0">

        {{-- Next trip --}}
        <div class="bg-black/30 backdrop-blur rounded-3xl p-5 sm:p-7 border border-white/10 flex flex-col gap-5">
            <span class="self-start inline-flex items-center gap-2 bg-amber-400 text-emerald-950 font-bold text-sm px-4 py-1.5 rounded-full">
                {{ $t('next_trip') }}
            </span>

            @if($nextTrip)
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class=" text-white/80 text-xl sm:text-2xl font-extrabold">{{ $eventTitle }}</h2>
                    <div class="flex items-center gap-2 text-sm sm:text-base font-semibold">
                        <span class="bg-white/15 px-3 py-1.5 rounded-lg">🕐 {{ $nextTrip['time_range'] }}</span>
                        <span class="bg-white/15 px-3 py-1.5 rounded-lg">🚌 {{ $nextTrip['label'][$lang] }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-2 bg-amber-400/15 border border-amber-300/40 text-amber-200 rounded-xl px-4 py-2.5 text-sm sm:text-base font-semibold">
                    🔊 {{ $t('ready_alert', ['label' => $nextTrip['label'][$lang]]) }}
                </div>

                <div class="flex-1 flex items-center justify-center gap-6 sm:gap-12 py-2">
                    <div class="flex flex-col items-center justify-center h-24 w-24 sm:h-32 sm:w-32 rounded-full bg-white/10 border border-white/25 text-center px-2">
                        <span class="text-white/80 text-2xl sm:text-4xl font-extrabold">{{ $nextTrip['booked_count'] }}</span>
                        <span class="text-[10px] sm:text-xs text-white/70 mt-1 leading-tight">{{ $t('bookings_count') }}</span>
                    </div>

                    <div class="text-center">
                        <div class="text-white/80 text-5xl sm:text-8xl tabular-nums tracking-wider" x-text="countdownText">--:--</div>
                        <div class="flex justify-center gap-8 sm:gap-14 text-xs sm:text-sm text-white/70 mt-2">
                            <span>{{ $t('minutes_label') }}</span>
                            <span>{{ $t('seconds_label') }}</span>
                        </div>
                    </div>

                    <div class="flex flex-col items-center justify-center h-24 w-24 sm:h-32 sm:w-32 rounded-full bg-white/10 border border-white/25 text-center px-2">
                        <span class=" text-white/80 text-2xl sm:text-4xl font-extrabold">{{ $nextTrip['remaining_seats'] }}</span>
                        <span class="text-[10px] sm:text-xs text-white/70 mt-1 leading-tight">{{ $t('remaining_seats') }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-2 bg-amber-500/90 text-emerald-950 rounded-xl px-4 py-3 font-bold text-sm sm:text-base">
                    ⚠️ {{ $t('gathering_alert', ['minutes' => $signage->gathering_alert_minutes]) }}
                </div>
            @else
                <div class="flex-1 flex items-center justify-center text-white/70 text-lg sm:text-xl font-semibold text-center">
                    {{ $t('no_next_trip') }}
                </div>
            @endif
        </div>

        {{-- Upcoming trips --}}
        <div class="bg-black/20 backdrop-blur rounded-3xl p-4 sm:p-5 border border-white/10 flex flex-col gap-3 min-h-0">
            <h3 class="text-base sm:text-lg font-extrabold px-1">{{ $t('upcoming_trips') }}</h3>

            <div class="flex-1 flex flex-col gap-3 overflow-y-auto pe-1">
                @forelse($upcomingTrips as $trip)
                    <div class="rounded-2xl bg-white text-emerald-950 p-3 sm:p-4 flex items-center justify-between gap-3 shadow">
                        <div class="min-w-0">
                            <span @class([
                                'inline-flex items-center gap-1 text-xs font-bold px-3 py-1 rounded-full mb-2',
                                'bg-emerald-100 text-emerald-700' => $trip['status'] === 'ready',
                                'bg-amber-100 text-amber-700' => $trip['status'] === 'soon',
                                'bg-gray-100 text-gray-500' => $trip['status'] === 'waiting',
                            ])>
                                @if($trip['status'] === 'ready') 🔔 @elseif($trip['status'] === 'soon') ⏳ @else 🕐 @endif
                                {{ $t('status.' . $trip['status']) }}
                            </span>
                            <p class="font-bold truncate">{{ $eventTitle }}</p>
                            <p class="text-sm text-gray-500 truncate">{{ $trip['label'][$lang] }}</p>
                        </div>
                        <div class="text-end shrink-0">
                            <p class="font-extrabold text-sm sm:text-base">{{ $trip['time_range'] }}</p>
                        </div>
                    </div>
                @empty
                    <div class="flex-1 flex items-center justify-center text-white/60 text-sm text-center">
                        {{ $t('no_upcoming_trips') }}
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <footer class="flex flex-col gap-3">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 bg-black/30 backdrop-blur rounded-2xl p-4 border border-white/10 text-sm">
            <div class="flex items-center gap-2 min-w-0">
                <span class="text-xl shrink-0">📍</span>
                <div class="min-w-0">
                    <p class="text-white/80 font-bold">{{ $t('meeting_point') }}</p>
                    <p class="text-white/70 truncate">{{ $meetingPoint ?: '—' }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2 min-w-0">
                <span class="text-xl shrink-0">⏱️</span>
                <div class="min-w-0">
                    <p class="text-white/80 font-bold">{{ $t('early_arrival', ['minutes' => $signage->early_arrival_minutes]) }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2 min-w-0">
                <span class="text-xl shrink-0">📞</span>
                <div class="min-w-0">
                    <p class="text-white/80 font-bold">{{ $t('contact_help') }}</p>
                    <p class="text-white/70" dir="ltr">{{ $signage->contact_phone ?: '—' }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2 min-w-0">
                @if($signage->qr_code_image_path)
                    <img src="{{ asset('storage/' . $signage->qr_code_image_path) }}" alt="QR" class="h-12 w-12 rounded-lg bg-white p-1 shrink-0">
                @else
                    <span class="text-xl shrink-0">🔗</span>
                @endif
                <p class="text-white/70 truncate">{{ $t('scan_qr') }}</p>
            </div>
        </div>

        <div class="bg-brand rounded-2xl px-6 py-3 text-center text-white/80 font-bold text-sm sm:text-base">
            {{ $welcomeMessage }}
        </div>
    </footer>
</div>
