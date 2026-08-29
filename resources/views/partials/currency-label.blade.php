@php
    $__currencyIcon = \App\Models\BookingSetting::currencyIconDataUri();
    $__currencySymbol = \App\Models\BookingSetting::currencySymbol();
@endphp
@if ($__currencyIcon)
    <img src="{{ $__currencyIcon }}" alt="{{ $__currencySymbol }}" style="display:inline-block;height:1em;width:1em;vertical-align:-0.125em;">
@else
    {{ $__currencySymbol }}
@endif
