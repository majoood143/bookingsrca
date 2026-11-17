@php
    $images = $item->getMedia('gallery')->map->getUrl();
    if ($images->isEmpty() && $item->hasMedia('target')) {
        $images = collect([$item->getFirstMediaUrl('target')]);
    }
    $canonical = route('ar.item', [$destination->slug, $item->slug]);
    $description = \Illuminate\Support\Str::of($item->summary ?: $item->body)->squish()->limit(160)->toString();
    $audio = $item->audioUrl();
@endphp
<x-layouts.app :title="$item->title . ' | ' . $destination->title">
    @include('ar.partials.meta', [
        'pageTitle' => $item->title,
        'description' => $description,
        'canonical' => $canonical,
        'image' => $images->first(),
        'jsonLd' => array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'TouristAttraction',
            'name' => $item->title,
            'description' => $description,
            'url' => $canonical,
            'image' => $images->all() ?: null,
            'containedInPlace' => ['@type' => 'TouristDestination', 'name' => $destination->title, 'url' => route('ar.destination', $destination->slug)],
        ]),
    ])
    @include('ar.partials.header')

    <main class="max-w-4xl mx-auto px-4 sm:px-6 py-8">
        <nav class="text-sm text-gray-400 mb-6 flex items-center gap-2 flex-wrap">
            <a href="{{ route('ar.index') }}" class="hover:text-brand">{{ __('explore.nav.explore') }}</a>
            <span>/</span>
            <a href="{{ route('ar.destination', $destination->slug) }}" class="hover:text-brand">{{ $destination->title }}</a>
            <span>/</span>
            <span class="text-gray-600 font-medium">{{ $item->title }}</span>
        </nav>

        @if ($images->isNotEmpty())
            <div x-data="{ active: 0 }">
                <div class="aspect-video rounded-2xl bg-gray-100 overflow-hidden">
                    @foreach ($images as $i => $url)
                        <img src="{{ $url }}" alt="{{ $item->title }}" x-show="active === {{ $i }}" @if ($i) x-cloak @endif
                            class="w-full h-full object-cover">
                    @endforeach
                </div>
                @if ($images->count() > 1)
                    <div class="mt-3 flex gap-2 overflow-x-auto">
                        @foreach ($images as $i => $url)
                            <button type="button" @click="active = {{ $i }}" :class="active === {{ $i }} ? 'ring-2 ring-brand' : 'opacity-70'"
                                class="w-20 h-20 shrink-0 rounded-lg overflow-hidden">
                                <img src="{{ $url }}" alt="" class="w-full h-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        <h1 class="mt-8 text-3xl font-bold text-gray-900">{{ $item->title }}</h1>
        <p class="mt-1 text-sm text-gray-500">{{ $item->zone->title }}</p>

        @if ($audio)
            <div class="mt-6 bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-sm font-semibold text-gray-700 mb-2">🔊 {{ __('explore.item.listen') }}</p>
                <audio controls preload="none" src="{{ $audio }}" class="w-full"></audio>
            </div>
        @endif

        @if ($item->summary)
            <p class="mt-6 text-lg text-gray-700">{{ $item->summary }}</p>
        @endif
        @if ($item->body)
            <div class="mt-4 text-gray-700 leading-relaxed">{!! nl2br(e($item->body)) !!}</div>
        @endif

        @if ($item->zone->isScannable())
            <a href="{{ route('ar.scan', [$destination->slug, $item->zone]) }}"
                class="mt-10 inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-brand text-white font-semibold shadow hover:opacity-90">
                📷 {{ __('explore.scan.open_camera') }}
            </a>
        @endif
    </main>
</x-layouts.app>
