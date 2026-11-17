@php($pageTitle = __('explore.index.title'))
<x-layouts.app :title="$pageTitle . ' | ' . $siteName">
    @include('ar.partials.meta', [
        'description' => __('explore.index.subtitle'),
        'canonical' => route('ar.index'),
    ])
    @include('ar.partials.header')

    <main class="max-w-6xl mx-auto px-4 sm:px-6 py-10">
        <h1 class="text-3xl font-bold text-gray-900">{{ $pageTitle }}</h1>
        <p class="mt-2 text-gray-600">{{ __('explore.index.subtitle') }}</p>

        <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($destinations as $destination)
                <a href="{{ route('ar.destination', $destination->slug) }}"
                    class="group block bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                    <div class="aspect-video bg-gray-200 overflow-hidden">
                        @if ($cover = $destination->getFirstMediaUrl('cover'))
                            <img src="{{ $cover }}" alt="{{ $destination->title }}" loading="lazy"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                        @endif
                    </div>
                    <div class="p-5">
                        <h2 class="font-bold text-lg text-gray-900">{{ $destination->title }}</h2>
                        @if ($destination->summary)
                            <p class="mt-1 text-sm text-gray-600 line-clamp-2">{{ $destination->summary }}</p>
                        @endif
                    </div>
                </a>
            @empty
                <p class="text-gray-500">{{ __('explore.index.empty') }}</p>
            @endforelse
        </div>
    </main>
</x-layouts.app>
