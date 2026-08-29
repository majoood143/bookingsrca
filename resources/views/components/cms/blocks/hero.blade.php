@php
    $locale = app()->getLocale();
    $heading = $data['heading'][$locale] ?? $data['heading']['en'] ?? '';
    $subheading = $data['subheading'][$locale] ?? $data['subheading']['en'] ?? '';
    $ctaText = $data['cta_text'][$locale] ?? $data['cta_text']['en'] ?? '';
    $ctaUrl = $data['cta_url'] ?? null;
    $backgroundImage = $data['background_image'] ?? null;
    $bgUrl = $backgroundImage ? asset('storage/'.$backgroundImage) : null;
@endphp

<section class="cms-block relative isolate flex min-h-96 items-center justify-center overflow-hidden bg-gray-900 px-4 py-20 text-center sm:px-6 lg:px-8"
    @if ($bgUrl) style="background-image:linear-gradient(rgba(17,24,39,.55),rgba(17,24,39,.55)),url('{{ $bgUrl }}');background-size:cover;background-position:center;" @endif>
    <div class="mx-auto max-w-(--cms-w,42rem)">
        @if ($heading)
            <h1 class="text-4xl font-bold tracking-tight text-white sm:text-5xl">{{ $heading }}</h1>
        @endif
        @if ($subheading)
            <p class="mt-4 text-lg text-gray-200">{{ $subheading }}</p>
        @endif
        @if ($ctaText && $ctaUrl)
            <a href="{{ $ctaUrl }}"
                class="mt-8 inline-flex items-center rounded-md bg-brand px-6 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-brand-hover">
                {{ $ctaText }}
            </a>
        @endif
    </div>
</section>
