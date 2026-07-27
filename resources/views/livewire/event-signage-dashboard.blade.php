@php
    $initialLang = app()->getLocale() === 'ar' ? 'ar' : 'en';
    $switchSeconds = (int) ($signage->language_switch_seconds ?? 0);
    $bgStyle = $signage->background_image_path
        ? "background-image:url('" . asset('storage/' . $signage->background_image_path) . "')"
        : 'background-image:linear-gradient(160deg, #04220f 0%, #05602b 55%, #063a1d 100%)';
@endphp

<div
    class="tv-signage w-screen overflow-hidden relative bg-cover bg-center"
    style="{{ $bgStyle }}; height: calc(var(--real-vh, 1vh) * 100);"
    wire:poll.30s="refreshData"
>
    {{-- This must be the Blade view's ONLY top-level HTML element: Livewire
    detects the component root by scanning for the outermost tag, and a
    <style> tag rendered as a sibling before this div got mistaken for the
    root instead, so Livewire's wire:id/wire:snapshot ended up on the wrong
    element. That silently broke $wire lookups from any nested x-data (Alpine
    walks up the DOM for the nearest [wire:snapshot] element and throws if it
    can't find one), which in turn broke both wire:poll and the countdown's
    $wire.refreshData() call below. Keep the <style> and <script> below as
    DESCENDANTS of this div, never as siblings before it. --}}
    <style>
        [x-cloak] { display: none !important; }

        /*
         * Fluid design tokens for the TV signage screen. Every font-size below
         * is a clamp() driven by viewport width (vw), so text scales
         * continuously with the real screen size instead of jumping between
         * fixed breakpoints — the same markup renders proportionally on
         * 1366x768, 1920x1080 and 3840x2160 without extra breakpoint classes.
         * --tv-safe-x/--tv-safe-y hold back a margin from the edges so
         * content isn't clipped by TV overscan; tune those two values to
         * widen/narrow it.
         */
        .tv-signage {
            --tv-safe-x: 5vw;
            --tv-safe-y: 5vh;

            --tv-fs-2xs: clamp(0.6rem, 0.8vw, 0.85rem);
            --tv-fs-xs: clamp(0.7rem, 0.95vw, 1rem);
            --tv-fs-sm: clamp(0.85rem, 1.15vw, 1.2rem);
            --tv-fs-base: clamp(1rem, 1.35vw, 1.45rem);
            --tv-fs-md: clamp(1.15rem, 1.6vw, 1.75rem);
            --tv-fs-lg: clamp(1.35rem, 2vw, 2.2rem);
            --tv-fs-xl: clamp(1.7rem, 2.6vw, 3rem);
            --tv-fs-2xl: clamp(2.1rem, 3.4vw, 3.8rem);
            --tv-fs-clock: clamp(1.9rem, 3.2vw, 3.8rem);
            --tv-fs-countdown: clamp(2.3rem, 4.2vw, 4.8rem);
        }

        /* Contained internal scroll (upcoming trips list) with no visible
           scrollbar — nobody can drive a scrollbar with a TV remote anyway. */
        .tv-scroll-hidden { scrollbar-width: none; -ms-overflow-style: none; }
        .tv-scroll-hidden::-webkit-scrollbar { display: none; }
    </style>

    <script>
        // Some smart-TV browsers report a stale or incorrect `100vh` inside
        // fullscreen/kiosk mode. Recomputing the real pixel height in JS and
        // exposing it as --real-vh is the standard fix; `calc(var(--real-vh, 1vh) * 100)`
        // above falls back to plain 100vh on any browser where this hasn't run yet.
        (function () {
            function setRealVh() {
                document.documentElement.style.setProperty('--real-vh', (window.innerHeight * 0.01) + 'px');
            }
            setRealVh();
            window.addEventListener('resize', setRealVh);
            window.addEventListener('orientationchange', setRealVh);
        })();
    </script>

    {{-- x-data intentionally lives on this inner, non-root node (not the
    Livewire component's outer element) so wire:poll refreshes never
    re-trigger x-init and stack duplicate setInterval timers, which was
    causing the language auto-switch to drift/flicker after a few polls. --}}
    <div
        class="contents"
        data-next-trip-starts-at="{{ $nextTrip['starts_at'] ?? '' }}"
        x-data="{
            lang: '{{ $initialLang }}',
            switchSeconds: {{ $switchSeconds }},
            clockTime: '',
            clockDate: '',
            countdownText: '--:--',
            _tickTimer: null,
            _switchTimer: null,
            _advancing: false,
            tick() {
                const now = new Date();
                this.clockTime = now.toLocaleTimeString(this.lang === 'ar' ? 'ar-OM' : 'en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
                this.clockDate = now.toLocaleDateString(this.lang === 'ar' ? 'ar-OM-u-ca-gregory' : 'en-US', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
                const startsAt = this.$el.dataset.nextTripStartsAt;
                if (startsAt) {
                    const diff = Math.floor((new Date(startsAt) - now) / 1000);
                    if (diff <= 0) {
                        this.countdownText = '00 : 00';
                        // Slot has started: jump to the next slot right away instead of
                        // waiting for the next wire:poll tick.
                        if (!this._advancing) {
                            this._advancing = true;
                            this.$wire.refreshData().catch(() => {}).finally(() => { this._advancing = false; });
                        }
                    } else {
                        const m = String(Math.floor(diff / 60)).padStart(2, '0');
                        const s = String(diff % 60).padStart(2, '0');
                        this.countdownText = m + ' : ' + s;
                    }
                } else {
                    this.countdownText = '--:--';
                }
            },
            init() {
                clearInterval(this._tickTimer);
                clearInterval(this._switchTimer);
                this.tick();
                this._tickTimer = setInterval(() => this.tick(), 1000);
                if (this.switchSeconds > 0) {
                    this._switchTimer = setInterval(() => {
                        this.lang = this.lang === 'ar' ? 'en' : 'ar';
                    }, this.switchSeconds * 1000);
                }
                // This screen runs unattended for hours with no one around to
                // retry manually, so a failed request (e.g. an expired
                // session) must not leave it frozen forever: reload to recover.
                Livewire.hook('request', ({ fail }) => {
                    fail(() => setTimeout(() => window.location.reload(), 3000));
                });
            }
        }"
        x-init="init()"
    >
        <div class="absolute inset-0 bg-black/45 pointer-events-none"></div>

        <div class="relative z-10" x-show="lang === 'en'" x-cloak>
            @include('livewire.partials.signage-body', ['lang' => 'en', 'event' => $event, 'signage' => $signage, 'nextTrip' => $nextTrip, 'upcomingTrips' => $upcomingTrips])
        </div>

        <div class="relative z-10" x-show="lang === 'ar'" x-cloak>
            @include('livewire.partials.signage-body', ['lang' => 'ar', 'event' => $event, 'signage' => $signage, 'nextTrip' => $nextTrip, 'upcomingTrips' => $upcomingTrips])
        </div>
    </div>
</div>
