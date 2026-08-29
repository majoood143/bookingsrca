@php
    $locale = app()->getLocale();
    $columns = (int) ($data['columns'] ?? 2);
    $gridClass = match ($columns) {
        1 => 'grid-cols-1',
        3 => 'sm:grid-cols-3',
        default => 'sm:grid-cols-2',
    };
@endphp

<section class="cms-block px-4 py-8 sm:px-6 lg:px-8">
    <div class="prose prose-slate mx-auto grid max-w-(--cms-w,64rem) gap-8 {{ $gridClass }}">
        <div>{!! $data['column_1'][$locale] ?? $data['column_1']['en'] ?? '' !!}</div>
        @if ($columns >= 2)
            <div>{!! $data['column_2'][$locale] ?? $data['column_2']['en'] ?? '' !!}</div>
        @endif
        @if ($columns >= 3)
            <div>{!! $data['column_3'][$locale] ?? $data['column_3']['en'] ?? '' !!}</div>
        @endif
    </div>
</section>
