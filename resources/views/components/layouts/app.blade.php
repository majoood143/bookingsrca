@php
    $favicon = \App\Models\BookingSetting::get('favicon');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    @include('partials.gtm-head')
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="{{ $favicon ? asset('storage/' . $favicon) : asset('favicon.ico') }}" sizes="any">
    <title>{{ $title ?? config('app.name', 'Bookings') }}</title>
    @stack('meta')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-100 min-h-screen">
    @include('partials.gtm-body')
    {{ $slot }}

    @livewireScripts
</body>
</html>
