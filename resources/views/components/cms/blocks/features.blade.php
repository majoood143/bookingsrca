@php $locale = app()->getLocale(); @endphp

<section class="cms-block px-4 py-12 sm:px-6 lg:px-8">
    <div class="mx-auto grid max-w-(--cms-w,64rem) gap-8 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($data['items'] ?? [] as $item)
            <div class="text-center">
                @if (!empty($item['icon']))
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-brand/10 text-brand">
                        <x-dynamic-component :component="$item['icon']" class="h-6 w-6" />
                    </div>
                @endif
                <h3 class="mt-4 text-lg font-semibold text-gray-900">{{ $item['title'][$locale] ?? $item['title']['en'] ?? '' }}</h3>
                <p class="mt-2 text-gray-600">{{ $item['description'][$locale] ?? $item['description']['en'] ?? '' }}</p>
            </div>
        @endforeach
    </div>
</section>
