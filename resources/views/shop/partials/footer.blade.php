@php
    $siteName = \App\Models\BookingSetting::get('site_name_' . app()->getLocale(), config('app.name', 'Bookings'));
@endphp
<footer class="border-t border-gray-100 py-8 mt-4">
    <p class="text-center text-sm text-gray-400">© {{ now()->year }} {{ $siteName }}. {{ __('home.footer.rights') }}</p>
</footer>
