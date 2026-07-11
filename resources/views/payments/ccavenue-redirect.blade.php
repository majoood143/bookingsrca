<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('event_booking.step6.redirecting_ccavenue') }} — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md text-center">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <svg class="animate-spin h-10 w-10 text-teal-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <p class="text-gray-700 font-medium">{{ __('event_booking.step6.redirecting_ccavenue') }}</p>

            <form id="ccavenue-redirect-form" method="POST" action="{{ $url }}" class="mt-6">
                <input type="hidden" name="encRequest" value="{{ $encRequest }}">
                <input type="hidden" name="access_code" value="{{ $access_code }}">
                <noscript>
                    <button type="submit" class="mt-2 px-6 py-2 bg-teal-600 text-white rounded-lg font-medium">
                        {{ __('event_booking.step6.pay_ccavenue_btn') }}
                    </button>
                </noscript>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('ccavenue-redirect-form').submit();
    </script>
</body>

</html>
