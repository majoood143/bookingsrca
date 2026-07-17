@php
    $__currencyIcon = \App\Models\BookingSetting::currencyIconDataUri();
    $__currencySymbol = \App\Models\BookingSetting::currencySymbol();
@endphp
@if ($__currencyIcon)
    <img src="{{ $__currencyIcon }}" alt="{{ $__currencySymbol }}" style="display:inline-block;height:0.9em;width:0.9em;vertical-align:-0.05em;">{{ number_format($amount, 3) }}
@else
    {{ $__currencySymbol }}{{ number_format($amount, 3) }}
@endif
