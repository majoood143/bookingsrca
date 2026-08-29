@push('meta')
    <x-cms.seo-meta :model="$post" />
    @if ($post->custom_css)
        <style>{!! $post->custom_css !!}</style>
    @endif
@endpush

@php
    $locale = app()->getLocale();
    $featuredImageUrl = $post->getFirstMediaUrl('featured_image');
    $isFullWidth = $post->container_width === 'full_width';
@endphp

<div dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}" class="cms-post bg-white"
    @if ($isFullWidth) style="--cms-w: none;" @endif>
    <article>
        @if ($featuredImageUrl)
            <div class="w-full max-h-112 overflow-hidden">
                <img src="{{ $featuredImageUrl }}" alt="{{ $post->getTranslation('title', $locale) }}"
                    class="w-full h-full object-cover">
            </div>
        @endif

        @if ($post->show_title)
            <header class="mx-auto max-w-(--cms-w,48rem) px-4 pt-10 sm:px-6 lg:px-8">
                @if ($post->category)
                    <a href="{{ url('blog/category/'.$post->category->slug) }}"
                        class="text-sm font-semibold uppercase tracking-wide text-brand hover:text-brand-hover">
                        {{ $post->category->getTranslation('name', $locale) }}
                    </a>
                @endif
                <h1 class="mt-2 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                    {{ $post->getTranslation('title', $locale) }}
                </h1>
                @if ($post->published_at)
                    <p class="mt-3 text-sm text-gray-500">
                        {{ $post->published_at->translatedFormat('F j, Y') }}
                    </p>
                @endif
            </header>
        @endif

        {!! $renderedContent !!}

        @if ($post->getMedia('gallery')->isNotEmpty())
            <section class="px-4 py-8 sm:px-6 lg:px-8">
                <div class="mx-auto grid max-w-(--cms-w,64rem) grid-cols-2 gap-4 sm:grid-cols-3">
                    @foreach ($post->getMedia('gallery') as $media)
                        <div class="aspect-square overflow-hidden rounded-lg bg-gray-100">
                            <img src="{{ $media->getUrl() }}" alt="" loading="lazy" class="h-full w-full object-cover">
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($post->tags->isNotEmpty())
            <footer class="mx-auto max-w-(--cms-w,48rem) px-4 pb-10 sm:px-6 lg:px-8">
                <div class="flex flex-wrap gap-2 border-t border-gray-200 pt-6">
                    @foreach ($post->tags as $tag)
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                            {{ $tag->getTranslation('name', $locale) }}
                        </span>
                    @endforeach
                </div>
            </footer>
        @endif
    </article>
</div>
