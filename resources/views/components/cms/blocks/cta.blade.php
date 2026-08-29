@php
    $locale = app()->getLocale();
    $heading = $data['heading'][$locale] ?? $data['heading']['en'] ?? '';
    $buttonText = $data['button_text'][$locale] ?? $data['button_text']['en'] ?? '';
    $style = $data['style'] ?? 'primary';

    $buttonClasses = match ($style) {
        'secondary' => 'bg-gray-800 text-white hover:bg-gray-700',
        'outline' => 'border-2 border-brand text-brand hover:bg-brand hover:text-white',
        default => 'bg-brand text-white hover:bg-brand-hover',
    };
@endphp

<section class="cms-block bg-gray-50 px-4 py-14 text-center sm:px-6 lg:px-8">
    @if ($heading)
        <h2 class="mx-auto max-w-(--cms-w,42rem) text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">{{ $heading }}</h2>
    @endif
    @if ($buttonText && !empty($data['button_url']))
        <a href="{{ $data['button_url'] }}"
            class="mt-6 inline-flex items-center rounded-md px-6 py-3 text-base font-semibold shadow-sm transition {{ $buttonClasses }}">
            {{ $buttonText }}
        </a>
    @endif
</section>
