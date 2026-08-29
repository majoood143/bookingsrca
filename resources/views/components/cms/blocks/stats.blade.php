@php $locale = app()->getLocale(); @endphp

<section class="cms-block bg-gray-50 px-4 py-12 sm:px-6 lg:px-8">
    <div class="mx-auto grid max-w-(--cms-w,64rem) grid-cols-2 gap-8 text-center sm:grid-cols-4">
        @foreach ($data['items'] ?? [] as $item)
            <div>
                @if (!empty($item['icon']))
                    <x-dynamic-component :component="$item['icon']" class="mx-auto h-8 w-8 text-brand" />
                @endif
                <strong class="mt-2 block text-3xl font-bold text-gray-900">{{ $item['number'] ?? '' }}</strong>
                <span class="text-sm text-gray-600">{{ $item['label'][$locale] ?? $item['label']['en'] ?? '' }}</span>
            </div>
        @endforeach
    </div>
</section>
