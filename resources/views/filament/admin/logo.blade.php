@php
    $logoPath = \App\Models\BookingSetting::get('app_logo');
@endphp
<img
    src="{{ $logoPath ? \Illuminate\Support\Facades\Storage::disk('public')->url($logoPath) : asset('storage/images/horizontalLogo-02.svg') }}"
    alt="{{ \App\Models\BookingSetting::get('site_name_en', 'Bookings') }}"
    class="h-20"
>
