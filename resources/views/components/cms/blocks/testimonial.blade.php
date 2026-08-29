@php
    $locale = app()->getLocale();
    $quote = $data['quote'][$locale] ?? $data['quote']['en'] ?? '';
    $attribution = $data['attribution'][$locale] ?? $data['attribution']['en'] ?? '';
@endphp

<section class="cms-block px-4 py-12 sm:px-6 lg:px-8">
    <blockquote class="mx-auto max-w-(--cms-w,42rem) text-center">
        @if (!empty($data['avatar']))
            <img src="{{ asset('storage/'.$data['avatar']) }}" alt="{{ $attribution }}"
                class="mx-auto h-16 w-16 rounded-full object-cover">
        @endif
        <p class="mt-4 text-xl font-medium text-gray-900">&ldquo;{{ $quote }}&rdquo;</p>
        @if ($attribution)
            <cite class="mt-4 block text-sm font-semibold text-gray-500 not-italic">{{ $attribution }}</cite>
        @endif
    </blockquote>
</section>
