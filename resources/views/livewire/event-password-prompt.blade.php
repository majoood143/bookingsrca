@push('meta')
    <meta name="robots" content="noindex, nofollow">
@endpush
<div dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
        <div class="max-w-md w-full text-center py-12">
            <div class="flex justify-end mb-6">
                @if (app()->getLocale() === 'ar')
                    <a href="{{ route('lang.switch', 'en') }}"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-full transition-colors">
                        🌐 {{ __('event_booking.switch_to_english') }}
                    </a>
                @else
                    <a href="{{ route('lang.switch', 'ar') }}"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-full transition-colors">
                        🌐 {{ __('event_booking.switch_to_arabic') }}
                    </a>
                @endif
            </div>

            <img src="{{ asset('storage/images/horizontalLogo-03.svg') }}" alt="Logo"
                class="h-20 w-auto mx-auto mb-8">

            <div class="text-6xl mb-4">🔒</div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3">
                {{ __('event_booking.private.heading') }}
            </h1>
            <p class="text-gray-500 text-base leading-relaxed mb-6">
                {{ __('event_booking.private.message') }}
            </p>

            <form wire:submit.prevent="submitEventPassword" class="text-start">
                <label for="eventPasswordInput" class="sr-only">
                    {{ __('event_booking.private.password_label') }}
                </label>
                <input
                    type="password"
                    id="eventPasswordInput"
                    wire:model="eventPasswordInput"
                    placeholder="{{ __('event_booking.private.password_placeholder') }}"
                    autofocus
                    class="w-full rounded-xl border-gray-300 focus:border-brand focus:ring-brand shadow-sm px-4 py-3"
                >
                @if ($eventPasswordError)
                    <p class="mt-2 text-sm text-red-600">{{ $eventPasswordError }}</p>
                @endif

                <button type="submit"
                    class="mt-4 w-full inline-flex items-center justify-center gap-1.5 px-6 py-3 bg-brand text-white font-semibold rounded-xl hover:bg-brand-hover transition-colors shadow-sm">
                    {{ __('event_booking.private.submit') }}
                </button>
            </form>
        </div>
    </div>
</div>
