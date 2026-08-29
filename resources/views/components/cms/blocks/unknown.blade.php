@props(['type' => null])

{{-- Unknown or removed block type — fail gracefully instead of a hard error. --}}
@if (config('app.debug'))
    <!-- CMS: unknown block type "{{ $type }}" -->
@endif
