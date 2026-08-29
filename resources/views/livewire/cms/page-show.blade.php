@push('meta')
    <x-cms.seo-meta :model="$page" />
    @if ($page->custom_css)
        <style>{!! $page->custom_css !!}</style>
    @endif
@endpush

@php
    $locale = app()->getLocale();
    $featuredImageUrl = $page->getFirstMediaUrl('featured_image');
    $isFullWidth = $page->container_width === 'full_width';
@endphp

<div dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}" class="cms-page bg-white"
    @if ($isFullWidth) style="--cms-w: none;" @endif>
    <article>
        @if ($featuredImageUrl)
            <div class="w-full max-h-112 overflow-hidden">
                <img src="{{ $featuredImageUrl }}" alt="{{ $page->getTranslation('title', $locale) }}"
                    class="w-full h-full object-cover">
            </div>
        @endif

        @if ($page->show_title && !($page->content && collect($page->content)->contains(fn ($block) => ($block['type'] ?? null) === 'hero')))
            <header class="mx-auto max-w-(--cms-w,48rem) px-4 pt-10 sm:px-6 lg:px-8">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                    {{ $page->getTranslation('title', $locale) }}
                </h1>
            </header>
        @endif

        {!! $renderedContent !!}

        @if ($page->getMedia('gallery')->isNotEmpty())
            <section class="px-4 py-8 sm:px-6 lg:px-8">
                <div class="mx-auto grid max-w-(--cms-w,64rem) grid-cols-2 gap-4 sm:grid-cols-3">
                    @foreach ($page->getMedia('gallery') as $media)
                        <div class="aspect-square overflow-hidden rounded-lg bg-gray-100">
                            <img src="{{ $media->getUrl() }}" alt="" loading="lazy" class="h-full w-full object-cover">
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </article>
</div>
