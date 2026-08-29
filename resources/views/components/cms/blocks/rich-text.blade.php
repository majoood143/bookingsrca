@php
    $locale = app()->getLocale();
    $content = $data['content'][$locale] ?? $data['content']['en'] ?? '';
@endphp

<section class="cms-block px-4 py-8 sm:px-6 lg:px-8">
    <div class="prose prose-slate mx-auto max-w-(--cms-w,48rem)">
        {!! $content !!}
    </div>
</section>
