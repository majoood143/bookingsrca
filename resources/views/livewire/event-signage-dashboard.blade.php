@php
    $initialLang = app()->getLocale() === 'ar' ? 'ar' : 'en';
    $switchSeconds = (int) ($signage->language_switch_seconds ?? 0);
@endphp

<style>[x-cloak] { display: none !important; }</style>

<div
    class="min-h-screen w-full relative bg-cover bg-center"
    @if($signage->background_image_path)
        style="background-image:url('{{ asset('storage/' . $signage->background_image_path) }}')"
    @else
        style="background-image:linear-gradient(160deg, #04220f 0%, #05602b 55%, #063a1d 100%)"
    @endif
    wire:poll.30s="refreshData"
>
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
