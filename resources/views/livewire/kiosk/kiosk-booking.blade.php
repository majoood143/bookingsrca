@php
    use App\Models\BookingSetting;
    use Illuminate\Support\Facades\Storage;

    $primaryColor = BookingSetting::get('primary_color', '#05602b');
    $secondaryColor = BookingSetting::get('secondary_color', '#0da74c');

    // Welcome-screen background slideshow — up to 5 images, managed from the
    // kiosk's admin settings (Kiosk::slideshow_images). Falls back to the
    // plain bg-gray-50 background when none are configured.
    $slideshowImages = collect($kiosk->slideshow_images ?? [])->filter()->values();

    // Sticky progress bar nodes — the kiosk skips the shared wizard's date
    // picker (step 1) entirely since it auto-locks to today, so "Date" never
    // appears here even though the underlying $step numbering still reserves it.
    $progressSteps = [];
    if ($kiosk->is_active && !$eventLocked) {
        $progressSteps[] = ['value' => 0, 'label' => __('kiosk_booking.choose_event.heading')];
    }
    if ($kiosk->is_active) {
        $progressSteps[] = ['value' => 2, 'label' => __('kiosk_booking.progress.time')];
        $progressSteps[] = ['value' => 3, 'label' => __('kiosk_booking.progress.tickets')];
        if (isset($extraServices) && $extraServices->isNotEmpty()) {
            $progressSteps[] = ['value' => 4, 'label' => __('kiosk_booking.progress.extras')];
        }
        $progressSteps[] = ['value' => 5, 'label' => __('kiosk_booking.progress.details')];
        $progressSteps[] = ['value' => 6, 'label' => __('kiosk_booking.progress.payment')];
        $progressSteps[] = ['value' => 7, 'label' => __('kiosk_booking.progress.done')];
    }
@endphp
<div
    style="--color-brand: {{ $primaryColor }}; --color-brand-hover: {{ $secondaryColor }};"
    x-data="{
        timeoutSeconds: {{ (int) $kiosk->idle_timeout_seconds }},
        warnSeconds: {{ (int) min(10, max(3, floor(((int) $kiosk->idle_timeout_seconds) / 3))) }},
        timer: null,
        tickInterval: null,
        secondsLeft: null,
        idleTemplate: @js(__('kiosk_booking.idle.resuming_in', ['seconds' => '__N__'])),
        get idleMessage() {
            return this.idleTemplate.replace('__N__', this.secondsLeft);
        },
        ping() {
            clearTimeout(this.timer);
            clearInterval(this.tickInterval);
            this.secondsLeft = null;
            this.timer = setTimeout(() => this.startWarning(), (this.timeoutSeconds - this.warnSeconds) * 1000);
        },
        startWarning() {
            this.secondsLeft = this.warnSeconds;
            this.tickInterval = setInterval(() => {
                this.secondsLeft--;
                if (this.secondsLeft <= 0) {
                    clearInterval(this.tickInterval);
                    $wire.resetKiosk();
                }
            }, 1000);
        },
    }"
    x-init="ping()"
    @click.window="ping()"
    @touchstart.window="ping()"
    dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"
    class="min-h-screen bg-gray-50 kiosk-shell"
>
@if (!$kiosk->is_active)
    {{-- ── Kiosk disabled ── --}}
    <div class="min-h-screen flex items-center justify-center px-6 text-center">
        <div>
            <div class="text-7xl mb-6">🛠️</div>
            <h1 class="text-3xl font-bold text-gray-900 mb-3">{{ __('kiosk_booking.inactive.heading') }}</h1>
            <p class="text-gray-500 text-lg">{{ __('kiosk_booking.inactive.body') }}</p>
        </div>
    </div>
@elseif (!$started)

    {{-- ════════════════════════════
         WELCOME SCREEN — bilingual dual-action start buttons
    ════════════════════════════ --}}
    <div class="relative min-h-screen flex flex-col items-center justify-center gap-10 sm:gap-14 px-6 py-10 overflow-hidden"
        x-data="{
            slides: @js($slideshowImages),
            active: 0,
            slideTimer: null,
            startSlideshow() {
                if (this.slides.length < 2) return;
                this.slideTimer = setInterval(() => {
                    const next = (this.active + 1) % this.slides.length;
                    if (window.gsap) {
                        gsap.to('.kiosk-slide-' + this.active, { opacity: 0, duration: 1.8, ease: 'power1.inOut' });
                        gsap.to('.kiosk-slide-' + next, { opacity: 1, duration: 1.8, ease: 'power1.inOut' });
                    }
                    this.active = next;
                }, 8000);
            },
        }"
        x-init="startSlideshow(); $nextTick(() => {
            if (!window.gsap) return;
            gsap.set('.kiosk-welcome-btn', { opacity: 0, y: 26 });
            gsap.to('.kiosk-welcome-btn', { opacity: 1, y: 0, duration: 0.6, stagger: 0.12, ease: 'power2.out' });
            gsap.to('.kiosk-welcome-pulse', { scale: 1.03, duration: 1.6, repeat: -1, yoyo: true, ease: 'sine.inOut' });
        })">

        @if ($slideshowImages->isNotEmpty())
            <div class="absolute inset-0 z-0">
                @foreach ($slideshowImages as $i => $path)
                    <img src="{{ Storage::disk('public')->url($path) }}" alt=""
                        class="kiosk-slide-{{ $i }} absolute inset-0 w-full h-full object-cover"
                        style="opacity: {{ $i === 0 ? 1 : 0 }}">
                @endforeach
                {{-- Scrim so the logo/buttons stay legible over busy photos. --}}
                <div class="absolute inset-0 bg-gradient-to-b from-black/55 via-black/25 to-black/60"></div>
            </div>
        @endif

        <div class="relative z-10 text-center">
            <img src="{{ asset('storage/images/horizontalLogo-03.svg') }}" alt="Logo" class="h-20 sm:h-40 w-auto mx-auto mb-5">
            <p class="uppercase tracking-[0.3em] text-xs font-bold {{ $slideshowImages->isNotEmpty() ? 'text-white/70' : 'text-gray-400' }}">{{ __('kiosk_booking.welcome.kiosk_tag') }}</p>
        </div>

        <div class="relative z-10 grid grid-cols-1 sm:grid-cols-2 gap-6 sm:gap-8 w-full max-w-4xl">
            <button wire:click="startBooking('en')" wire:loading.attr="disabled" dir="ltr"
                class="kiosk-welcome-btn kiosk-welcome-pulse rounded-4xl bg-gradient-to-br from-brand to-brand-hover text-white p-10 sm:p-14 shadow-2xl shadow-brand/30 active:scale-[0.97] transition-transform text-center disabled:opacity-70">
                <div class="text-2xl sm:text-4xl font-black leading-tight">{{ __('kiosk_booking.welcome.en_cta') }}</div>
                <div class="mt-3 text-white/80 text-base sm:text-lg font-medium">{{ __('kiosk_booking.welcome.en_sub') }}</div>
            </button>

            <button wire:click="startBooking('ar')" wire:loading.attr="disabled" dir="rtl"
                class="kiosk-welcome-btn kiosk-welcome-pulse rounded-4xl bg-gray-900 text-white p-10 sm:p-14 shadow-2xl shadow-black/30 active:scale-[0.97] transition-transform text-center disabled:opacity-70">
                <div class="text-2xl sm:text-4xl font-black leading-tight">{{ __('kiosk_booking.welcome.ar_cta') }}</div>
                <div class="mt-3 text-white/80 text-base sm:text-lg font-medium">{{ __('kiosk_booking.welcome.ar_sub') }}</div>
            </button>
        </div>
    </div>
@else

    {{-- ── Header ── --}}
    <div class="bg-gradient-to-r from-brand to-brand-hover text-white">
        <div class="max-w-5xl mx-auto px-6 py-6 flex items-center justify-between">
            <img src="{{ asset('storage/images/horizontalLogo-03.svg') }}" alt="Logo" class="h-20 sm:h-24 w-auto shrink-0">

            <div class="flex items-center gap-3">
                @if (app()->getLocale() === 'ar')
                    <a href="{{ route('lang.switch', 'en') }}"
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold bg-white/20 hover:bg-white/30 text-white rounded-full transition-colors">
                        🌐 English
                    </a>
                @else
                    <a href="{{ route('lang.switch', 'ar') }}"
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold bg-white/20 hover:bg-white/30 text-white rounded-full transition-colors">
                        🌐 العربية
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Sticky wizard progress bar ── --}}
    @if (!$noAvailabilityToday && count($progressSteps))
        <div class="sticky top-0 z-30 bg-white/95 backdrop-blur border-b border-gray-100">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 py-3 sm:py-4">
                <ol class="flex items-center justify-between">
                    @foreach ($progressSteps as $idx => $node)
                        @php
                            $state = $step > $node['value'] ? 'complete' : ($step === $node['value'] ? 'current' : 'upcoming');
                        @endphp
                        <li class="flex items-center flex-1 last:flex-none">
                            <div class="flex flex-col items-center gap-1 shrink-0">
                                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full flex items-center justify-center font-bold text-sm sm:text-base transition-colors duration-300
                                    {{ $state === 'complete' ? 'bg-brand text-white' : ($state === 'current' ? 'bg-brand-hover text-white ring-4 ring-brand-hover/20' : 'bg-gray-100 text-gray-400') }}">
                                    @if ($state === 'complete')
                                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                        </svg>
                                    @else
                                        {{ $idx + 1 }}
                                    @endif
                                </div>
                                <span class="hidden sm:block text-[11px] font-semibold uppercase tracking-wide {{ $state === 'upcoming' ? 'text-gray-300' : 'text-gray-600' }}">
                                    {{ $node['label'] }}
                                </span>
                            </div>
                            @if (!$loop->last)
                                <div class="flex-1 h-1 mx-1 sm:mx-3 rounded-full transition-colors duration-300 {{ $state === 'complete' ? 'bg-brand' : 'bg-gray-100' }}"></div>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    @endif

    <div class="max-w-5xl mx-auto px-6 py-10 kiosk-wizard-shell">

        {{-- ════════════════════════════
             STEP 0 — Choose Event (only when the kiosk isn't pinned to one)
        ════════════════════════════ --}}
        @if ($step === 0)
            <h2 class="text-3xl font-bold text-gray-900 mb-1">{{ __('kiosk_booking.choose_event.heading') }}</h2>
            <p class="text-gray-500 text-lg mb-8">{{ __('kiosk_booking.choose_event.subheading') }}</p>

            @if ($pickableEvents->isEmpty())
                <div class="text-center py-16 bg-white rounded-2xl border border-gray-200">
                    <p class="text-gray-400 text-lg">{{ __('kiosk_booking.choose_event.no_events') }}</p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    @foreach ($pickableEvents as $ev)
                        <button wire:click="chooseEvent({{ $ev->id }})"
                            class="p-6 border-2 border-gray-200 bg-white rounded-2xl text-start hover:border-blue-400 hover:shadow-md transition-all">
                            <h3 class="text-xl font-bold text-gray-900">
                                {{ $ev->getTranslation('title', app()->getLocale()) }}</h3>
                            <p class="text-gray-500 mt-1">
                                {{ $ev->getTranslation('location', app()->getLocale()) }}</p>
                        </button>
                    @endforeach
                </div>
            @endif
        @endif

        {{-- ════════════════════════════
             STEP 1 fallback — nothing bookable today (kiosk never shows a calendar)
        ════════════════════════════ --}}
        @if ($step === 1 && $noAvailabilityToday)
            <div class="max-w-xl mx-auto text-center py-16">
                <div class="text-7xl mb-6">🕒</div>
                <h2 class="text-3xl font-bold text-gray-900 mb-3">{{ __('kiosk_booking.today.no_slots_heading') }}</h2>
                <p class="text-gray-500 text-lg mb-8">{{ __('kiosk_booking.today.no_slots_body') }}</p>

                @unless ($eventLocked)
                    <button wire:click="resetKiosk"
                        class="px-8 py-3 bg-brand text-white font-bold text-base rounded-xl hover:bg-brand-hover transition-colors shadow-md">
                        {{ __('kiosk_booking.today.back_to_events') }}
                    </button>
                @endunless
            </div>
        @endif

        {{-- "Today" lock badge shown just above the shared slot-selection step —
             reassures the customer the date is fixed, no calendar needed. --}}
        @if ($step === 2 && $selectedDate && !$noAvailabilityToday)
            <div class="inline-flex items-center gap-2 px-4 py-2 mb-4 bg-brand/10 text-brand font-bold rounded-full text-sm">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                {{ __('kiosk_booking.today.badge', ['date' => \Carbon\Carbon::parse($selectedDate)->locale(app()->getLocale())->translatedFormat('l, F j')]) }}
            </div>
        @endif

        {{-- ════════════════════════════
             STEPS 1–5 — shared with the public web booking flow
        ════════════════════════════ --}}
        @if ($step >= 1 && $step <= 5 && !$noAvailabilityToday)
            @include('livewire.partials.booking-wizard-steps')
        @endif

        {{-- ════════════════════════════
             STEP 6 — Payment Method
        ════════════════════════════ --}}
        @if ($step === 6)
            <div class="max-w-2xl mx-auto"
                x-data="{
                    pressAnimate(el) {
                        if (!window.gsap) return;
                        gsap.killTweensOf(el);
                        gsap.timeline()
                            .to(el, { scale: 0.94, duration: 0.1, ease: 'power2.out' })
                            .to(el, { scale: 1.015, duration: 0.18, ease: 'power2.out' })
                            .to(el, { scale: 1, duration: 0.12, ease: 'power2.out' });
                    },
                }"
                x-init="$nextTick(() => window.gsap && gsap.from('.kiosk-pay-option', { opacity: 0, y: 20, duration: 0.5, stagger: 0.12, ease: 'power2.out' }))">
                <h2 class="text-3xl font-bold text-gray-900 mb-1 text-center">{{ __('kiosk_booking.step6.heading') }}</h2>
                <p class="text-gray-500 text-lg mb-8 text-center">{{ __('kiosk_booking.step6.subheading') }}</p>

                @if (session()->has('error'))
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-center">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($awaitingCardTap)
                    {{-- Waiting for the reader to pick up a tap --}}
                    <div class="p-10 border-2 border-blue-400 bg-blue-50 rounded-3xl text-center">
                        <div class="text-7xl mb-4 animate-pulse">📲</div>
                        <p class="text-xl font-bold text-blue-800">{{ __('kiosk_booking.step6.wallet_waiting') }}</p>

                        @if (app()->isLocal())
                            {{-- Dev-only stand-in for the physical ACR122U, until the native bridge exists --}}
                            <form wire:submit.prevent="$dispatch('card-tapped', { uid: $refs.devUid.value })"
                                class="mt-6 flex items-center justify-center gap-2">
                                <input x-ref="devUid" type="text" placeholder="Card UID (dev only)"
                                    class="px-4 py-2 border border-gray-300 rounded-xl text-sm">
                                <button type="submit"
                                    class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-xl">Simulate
                                    Tap</button>
                            </form>
                        @endif

                        <button wire:click="cancelWalletPayment"
                            class="mt-6 inline-flex items-center gap-1.5 text-blue-700 hover:text-blue-900 font-medium text-sm">
                            {{ __('kiosk_booking.step6.wallet_cancel') }}
                        </button>
                    </div>
                @else
                    <div class="space-y-5">
                        @if (in_array('wallet', $kiosk->enabled_payment_methods ?? []))
                            @if ($kiosk->reader_connected)
                                <button wire:click="selectWalletPayment"
                                    wire:loading.attr="disabled" wire:target="selectWalletPayment"
                                    @click="pressAnimate($el)"
                                    class="kiosk-pay-option w-full p-6 border-2 border-gray-200 bg-white rounded-2xl flex items-center gap-5 hover:border-blue-400 hover:shadow-md transition-[border-color,box-shadow] duration-300 will-change-transform relative overflow-hidden disabled:cursor-wait">
                                    <div wire:loading wire:target="selectWalletPayment"
                                        class="absolute inset-0 bg-white/85 flex items-center justify-center rounded-2xl">
                                        <svg class="animate-spin h-9 w-9 text-blue-600" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                    </div>
                                    <div class="text-5xl">💳</div>
                                    <div class="text-start">
                                        <p class="text-xl font-bold text-gray-900">{{ __('kiosk_booking.step6.wallet_title') }}</p>
                                        <p class="text-gray-500">{{ __('kiosk_booking.step6.wallet_subtitle') }}</p>
                                    </div>
                                </button>
                            @else
                                <div class="kiosk-pay-option w-full p-6 border-2 border-gray-100 bg-gray-50 rounded-2xl flex items-center gap-5 opacity-60">
                                    <div class="text-5xl grayscale">💳</div>
                                    <div class="text-start">
                                        <p class="text-xl font-bold text-gray-500">{{ __('kiosk_booking.step6.wallet_title') }}</p>
                                        <p class="text-gray-400">{{ __('kiosk_booking.step6.wallet_unavailable') }}</p>
                                    </div>
                                </div>
                            @endif
                        @endif

                        @if (in_array('pay_at_counter', $kiosk->enabled_payment_methods ?? []))
                            <button wire:click="payAtCounter"
                                wire:loading.attr="disabled" wire:target="payAtCounter"
                                @click="pressAnimate($el)"
                                class="kiosk-pay-option kiosk-pay-cta w-full p-8 border-2 border-brand bg-brand/5 rounded-3xl flex items-center gap-6 hover:shadow-lg hover:shadow-brand/20 transition-[border-color,box-shadow] duration-300 will-change-transform relative overflow-hidden disabled:opacity-60 disabled:cursor-wait">
                                <div wire:loading wire:target="payAtCounter"
                                    class="absolute inset-0 bg-brand/10 backdrop-blur-[1px] flex flex-col items-center justify-center gap-2 rounded-3xl">
                                    <svg class="animate-spin h-10 w-10 text-brand" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                    <p class="text-brand font-bold text-sm">{{ __('kiosk_booking.step6.processing') }}</p>
                                </div>
                                <div class="text-6xl shrink-0">🧾</div>
                                <div class="text-start">
                                    <p class="text-2xl sm:text-3xl font-black text-gray-900">{{ __('kiosk_booking.step6.counter_title') }}</p>
                                    <p class="text-gray-500 text-lg mt-1">{{ __('kiosk_booking.step6.counter_subtitle') }}</p>
                                </div>
                            </button>
                        @endif
                    </div>

                    <button wire:click="previousStep"
                        class="mt-8 inline-flex items-center gap-1.5 text-gray-500 hover:text-gray-800 font-medium text-sm">
                        ← {{ __('event_booking.back') }}
                    </button>
                @endif
            </div>
        @endif

        {{-- ════════════════════════════
             STEP 7 — Confirmation, with a 5s auto-redirect back to the welcome screen
        ════════════════════════════ --}}
        @if ($step === 7 && $confirmedBooking)
            <div class="max-w-xl mx-auto text-center"
                x-data="{
                    seconds: 15,
                    total: 15,
                    interval: null,
                    init() {
                        if (window.gsap) {
                            gsap.from('.kiosk-confirm-icon', { scale: 0.4, opacity: 0, duration: 0.6, ease: 'back.out(1.7)' });
                            gsap.from('.kiosk-confirm-card', { y: 16, opacity: 0, duration: 0.5, delay: 0.15, ease: 'power2.out' });
                        }
                        this.interval = setInterval(() => {
                            this.seconds--;
                            if (this.seconds <= 0) {
                                clearInterval(this.interval);
                                $wire.resetKiosk();
                            }
                        }, 1000);
                    },
                    get ringOffset() {
                        const circumference = 2 * Math.PI * 26;
                        return circumference * (1 - this.seconds / this.total);
                    },
                }">
                @if ($confirmedBooking->payment_status === 'paid')
                    <div class="kiosk-confirm-icon text-7xl mb-4">✅</div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">{{ __('kiosk_booking.confirmation.paid_heading') }}</h2>
                    <p class="text-gray-500 text-lg mb-8">{{ __('kiosk_booking.confirmation.paid_body') }}</p>
                @else
                    <div class="kiosk-confirm-icon text-7xl mb-4">🧾</div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">{{ __('kiosk_booking.confirmation.pending_heading') }}</h2>
                    <p class="text-gray-500 text-lg mb-8">{{ __('kiosk_booking.confirmation.pending_body') }}</p>
                @endif

                <div class="kiosk-confirm-card p-8 bg-white border-2 border-gray-200 rounded-3xl">
                    <p class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-1">
                        {{ __('kiosk_booking.confirmation.reference') }}</p>
                    <p class="text-4xl font-black text-gray-900 tracking-wide">{{ $confirmedBooking->booking_reference }}</p>

                    @if ($confirmedBooking->payment_status !== 'paid')
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <p class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-1">
                                {{ __('kiosk_booking.confirmation.amount_due') }}</p>
                            <p class="text-2xl font-black text-amber-600">
                                @include('partials.currency-amount', ['amount' => $confirmedBooking->total_price])</p>
                        </div>
                    @endif
                </div>

                <div class="mt-8 flex flex-col items-center gap-3">
                    <div class="relative w-16 h-16">
                        <svg class="w-16 h-16 -rotate-90" viewBox="0 0 60 60">
                            <circle cx="30" cy="30" r="26" fill="none" stroke="#e5e7eb" stroke-width="5" />
                            <circle cx="30" cy="30" r="26" fill="none" stroke="currentColor" class="text-brand-hover" stroke-width="5"
                                stroke-linecap="round"
                                :stroke-dasharray="2 * Math.PI * 26"
                                :stroke-dashoffset="ringOffset"
                                style="transition: stroke-dashoffset 1s linear" />
                        </svg>
                        <span class="absolute inset-0 flex items-center justify-center text-xl font-black text-gray-800" x-text="seconds"></span>
                    </div>
                    <p class="text-gray-600 font-semibold">{{ __('kiosk_booking.confirmation.redirect_message') }}</p>
                    <button wire:click="resetKiosk" @click="clearInterval(interval)"
                        class="text-sm text-gray-400 underline hover:text-gray-600">
                        {{ __('kiosk_booking.confirmation.redirect_now') }}
                    </button>
                </div>
            </div>
        @endif

    </div>
@endif

{{-- ── Idle warning banner — visible reset countdown before resetKiosk() fires ── --}}
<div x-show="secondsLeft !== null" x-cloak
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-4"
    x-transition:enter-end="opacity-100 translate-y-0"
    class="fixed inset-x-0 bottom-0 z-50 p-4 sm:p-6">
    <div class="max-w-md mx-auto bg-gray-900 text-white rounded-2xl shadow-2xl p-5 flex items-center gap-4">
        <div class="relative w-12 h-12 shrink-0">
            <svg class="w-12 h-12 -rotate-90" viewBox="0 0 44 44">
                <circle cx="22" cy="22" r="18" fill="none" stroke="rgba(255,255,255,0.15)" stroke-width="4" />
                <circle cx="22" cy="22" r="18" fill="none" stroke="#fbbf24" stroke-width="4" stroke-linecap="round"
                    :stroke-dasharray="2 * Math.PI * 18"
                    :stroke-dashoffset="secondsLeft !== null ? (2 * Math.PI * 18) * (1 - secondsLeft / warnSeconds) : 0"
                    style="transition: stroke-dashoffset 1s linear" />
            </svg>
            <span class="absolute inset-0 flex items-center justify-center text-sm font-black" x-text="secondsLeft"></span>
        </div>
        <div class="flex-1 min-w-0">
            <p class="font-bold" x-text="idleMessage"></p>
            <p class="text-white/60 text-sm">{{ __('kiosk_booking.idle.still_there_body') }}</p>
        </div>
        <button @click="ping()"
            class="shrink-0 px-4 py-2.5 bg-white text-gray-900 font-bold rounded-xl hover:bg-gray-100 transition-colors">
            {{ __('kiosk_booking.idle.continue') }}
        </button>
    </div>
</div>

<style>
    /* Bigger touch targets across the shared wizard partial for kiosk use,
       scoped so the public booking site (which reuses that partial) is untouched. */
    .kiosk-shell button {
        min-height: 3rem;
    }
    .kiosk-shell .rounded-full.border-2 {
        width: 3.25rem !important;
        height: 3.25rem !important;
        font-size: 1.5rem !important;
    }
    .kiosk-shell .tabular-nums {
        font-size: 1.5rem !important;
    }
    .kiosk-shell input[type="text"],
    .kiosk-shell input[type="email"],
    .kiosk-shell input[type="tel"],
    .kiosk-shell input[type="date"],
    .kiosk-shell select {
        min-height: 3.5rem;
        font-size: 1.1rem;
    }

    /* A kiosk is read at arm's length by a walk-up guest, not a desk-bound
       web visitor — every text size used across the shared wizard partial
       (headings, labels, body copy, badges) steps up one notch here so
       nothing on screen needs close-up reading. Scoped to .kiosk-shell so
       the public booking site, which reuses the same partial, is untouched. */
    .kiosk-shell .text-xs   { font-size: 0.875rem !important; }
    .kiosk-shell .text-sm   { font-size: 1rem !important; }
    .kiosk-shell .text-base { font-size: 1.125rem !important; }
    .kiosk-shell .text-lg   { font-size: 1.25rem !important; }
    .kiosk-shell .text-xl   { font-size: 1.5rem !important; }
    .kiosk-shell .text-2xl  { font-size: 1.75rem !important; }
    .kiosk-shell .text-3xl  { font-size: 2.25rem !important; }
    .kiosk-shell .text-4xl  { font-size: 2.75rem !important; }
</style>
</div>

@script
<script>
    // Native app → web: relay a card UID read from the ACR122U into Livewire.
    window.addEventListener('kiosk:card-tapped', (e) => {
        $wire.dispatch('card-tapped', { uid: e.detail.uid });
    });

    // Web → native app: hand the receipt payload to the kiosk app's printer bridge.
    $wire.on('print-receipt', ({ receipt }) => {
        if (window.KioskPrinter && typeof window.KioskPrinter.print === 'function') {
            window.KioskPrinter.print(JSON.stringify(receipt));
        }
    });

    // Wizard entrance/exit animations, keyed off the server-side welcome-screen gate.
    $wire.on('kiosk-started', () => {
        requestAnimationFrame(() => {
            if (window.gsap) {
                gsap.from('.kiosk-wizard-shell', { opacity: 0, y: 18, duration: 0.5, ease: 'power2.out' });
            }
        });
    });

    $wire.on('kiosk-reset', () => {
        requestAnimationFrame(() => {
            if (window.gsap) {
                gsap.set('.kiosk-welcome-btn', { opacity: 0, y: 26 });
                gsap.to('.kiosk-welcome-btn', { opacity: 1, y: 0, duration: 0.6, stagger: 0.12, ease: 'power2.out' });
            }
        });
    });

    // Step-to-step transition — KioskBooking::updatedStep() dispatches this on
    // every $step change (nextStep, previousStep, selectDate, selectSlot,
    // chooseEvent...), so every forward/back navigation gets a consistent
    // settle-in instead of only the very first "start booking" tap.
    $wire.on('kiosk-step-changed', () => {
        requestAnimationFrame(() => {
            if (window.gsap) {
                gsap.from('.kiosk-wizard-shell', { opacity: 0, y: 14, duration: 0.45, ease: 'power2.out' });
            }
        });
    });

    // Progressive attendee flow (step 5) — slide/fade the newly focused
    // attendee card, decision prompt, or summary in whenever the guided
    // flow advances (see KioskBooking::confirmAttendee() and friends).
    $wire.on('attendee-focus-changed', () => {
        requestAnimationFrame(() => {
            if (!window.gsap) return;
            gsap.from('.kiosk-attendee-card, .kiosk-decision-card', { opacity: 0, x: 24, duration: 0.45, ease: 'power2.out' });
            gsap.from('.kiosk-tracker-dot', { scale: 0.8, duration: 0.35, stagger: 0.04, ease: 'back.out(2)' });
        });
    });
</script>
@endscript
