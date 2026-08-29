@php $locale = app()->getLocale(); @endphp

<div dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}" class="cms-blog-index bg-white">
    <div class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold tracking-tight text-gray-900">
            {{ $category ? $category->getTranslation('name', $locale) : __('cms_public.blog.title') }}
        </h1>

        <div class="mt-8 grid gap-8 sm:grid-cols-2">
            @forelse ($posts as $post)
                @php $thumbUrl = $post->getFirstMediaUrl('featured_image', 'thumb') ?: $post->getFirstMediaUrl('featured_image'); @endphp
                <a href="{{ url('blog/'.$post->slug) }}" class="group block">
                    @if ($thumbUrl)
                        <div class="mb-4 aspect-video w-full overflow-hidden rounded-lg bg-gray-100">
                            <img src="{{ $thumbUrl }}" alt="" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                        </div>
                    @endif
                    <h2 class="text-xl font-semibold text-gray-900 group-hover:text-brand">
                        {{ $post->getTranslation('title', $locale) }}
                    </h2>
                    @if ($excerpt = $post->getTranslation('excerpt', $locale))
                        <p class="mt-2 text-gray-600">{{ $excerpt }}</p>
                    @endif
                </a>
            @empty
                <p class="col-span-2 text-gray-500">{{ __('cms_public.blog.empty') }}</p>
            @endforelse
        </div>

        <div class="mt-10">
            {{ $posts->links() }}
        </div>
    </div>
</div>
