<div
    x-data="{
        timeoutSeconds: {{ (int) $kiosk->idle_timeout_seconds }},
        timer: null,
        ping() {
            clearTimeout(this.timer);
            this.timer = setTimeout(() => { $wire.resetKiosk() }, this.timeoutSeconds * 1000);
        },
    }"
    x-init="ping()"
    @click.window="ping()"
    @touchstart.window="ping()"
    dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"
    class="min-h-screen bg-gray-50"
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
@else

    {{-- ── Header ── --}}
    <div class="bg-gradient-to-r from-brand to-brand-hover text-white">
        <div class="max-w-5xl mx-auto px-6 py-6 flex items-center justify-between">
            <img src="{{ asset('storage/images/horizontalLogo-03.svg') }}" alt="Logo" class="h-14 w-auto shrink-0">

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

    <div class="max-w-5xl mx-auto px-6 py-10">

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
             STEPS 1–5 — shared with the public web booking flow
        ════════════════════════════ --}}
        @if ($step >= 1 && $step <= 5)
            @include('livewire.partials.booking-wizard-steps')
        @endif

        {{-- ════════════════════════════
             STEP 6 — Payment Method
        ════════════════════════════ --}}
        @if ($step === 6)
            <div class="max-w-2xl mx-auto">
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
                                    class="w-full p-6 border-2 border-gray-200 bg-white rounded-2xl flex items-center gap-5 hover:border-blue-400 hover:shadow-md transition-all">
                                    <div class="text-5xl">💳</div>
                                    <div class="text-start">
                                        <p class="text-xl font-bold text-gray-900">{{ __('kiosk_booking.step6.wallet_title') }}</p>
                                        <p class="text-gray-500">{{ __('kiosk_booking.step6.wallet_subtitle') }}</p>
                                    </div>
                                </button>
                            @else
                                <div class="w-full p-6 border-2 border-gray-100 bg-gray-50 rounded-2xl flex items-center gap-5 opacity-60">
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
                                class="w-full p-6 border-2 border-gray-200 bg-white rounded-2xl flex items-center gap-5 hover:border-blue-400 hover:shadow-md transition-all disabled:opacity-60">
                                <div class="text-5xl">🧾</div>
                                <div class="text-start">
                                    <p class="text-xl font-bold text-gray-900">{{ __('kiosk_booking.step6.counter_title') }}</p>
                                    <p class="text-gray-500">{{ __('kiosk_booking.step6.counter_subtitle') }}</p>
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
             STEP 7 — Confirmation
        ════════════════════════════ --}}
        @if ($step === 7 && $confirmedBooking)
            <div class="max-w-xl mx-auto text-center">
                @if ($confirmedBooking->payment_status === 'paid')
                    <div class="text-7xl mb-4">✅</div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">{{ __('kiosk_booking.confirmation.paid_heading') }}</h2>
                    <p class="text-gray-500 text-lg mb-8">{{ __('kiosk_booking.confirmation.paid_body') }}</p>
                @else
                    <div class="text-7xl mb-4">🧾</div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">{{ __('kiosk_booking.confirmation.pending_heading') }}</h2>
                    <p class="text-gray-500 text-lg mb-8">{{ __('kiosk_booking.confirmation.pending_body') }}</p>
                @endif

                <div class="p-8 bg-white border-2 border-gray-200 rounded-3xl">
                    <p class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-1">
                        {{ __('kiosk_booking.confirmation.reference') }}</p>
                    <p class="text-4xl font-black text-gray-900 tracking-wide">{{ $confirmedBooking->booking_reference }}</p>

                    @if ($confirmedBooking->payment_status !== 'paid')
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <p class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-1">
                                {{ __('kiosk_booking.confirmation.amount_due') }}</p>
                            <p class="text-2xl font-black text-amber-600">
                                OMR{{ number_format($confirmedBooking->total_price, 3) }}</p>
                        </div>
                    @endif
                </div>

                <button wire:click="resetKiosk"
                    class="mt-8 px-8 py-3 bg-brand text-white font-bold text-base rounded-xl hover:bg-brand-hover transition-colors shadow-md">
                    {{ __('kiosk_booking.confirmation.new_booking') }}
                </button>
            </div>
        @endif

    </div>
@endif
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
</script>
@endscript
