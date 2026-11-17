@php
    $cover = $destination->getFirstMediaUrl('cover');
    $canonical = route('ar.destination', $destination->slug);
    $description = \Illuminate\Support\Str::of($destination->summary ?: $destination->description)->squish()->limit(160)->toString();
@endphp
<x-layouts.app :title="$destination->title . ' | ' . $siteName">
    @include('ar.partials.meta', [
        'pageTitle' => $destination->title,
        'description' => $description,
        'canonical' => $canonical,
        'image' => $cover,
        'jsonLd' => array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'TouristDestination',
            'name' => $destination->title,
            'description' => $description,
            'url' => $canonical,
            'image' => $cover ?: null,
            'includesAttraction' => $zones->flatMap->items->map(fn ($item) => [
                '@type' => 'TouristAttraction',
                'name' => $item->title,
                'url' => route('ar.item', [$destination->slug, $item->slug]),
            ])->values()->all(),
        ]),
    ])
    @include('ar.partials.header')

    @if ($cover)
        <div class="w-full max-h-96 overflow-hidden bg-gray-200">
            <img src="{{ $cover }}" alt="{{ $destination->title }}" class="w-full h-96 object-cover">
        </div>
    @endif

    <main class="max-w-6xl mx-auto px-4 sm:px-6 py-10">
        <h1 class="text-3xl font-bold text-gray-900">{{ $destination->title }}</h1>
        @if ($destination->description)
            <div class="mt-4 text-gray-700 leading-relaxed max-w-3xl">{!! nl2br(e($destination->description)) !!}</div>
        @endif

        @foreach ($zones as $zone)
            @continue($zone->items->isEmpty())
            <section class="mt-12">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-xl font-bold text-gray-900">{{ $zone->title }}</h2>
                    @if ($zone->isScannable())
                        <a href="{{ route('ar.scan', [$destination->slug, $zone]) }}"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-brand text-white font-semibold shadow hover:opacity-90">
                            📷 {{ __('explore.scan.open_camera') }}
                        </a>
                    @endif
                </div>

                <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($zone->items as $item)
                        <a href="{{ route('ar.item', [$destination->slug, $item->slug]) }}"
                            class="group block bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                            <div class="aspect-square bg-gray-100 overflow-hidden">
                                @if ($thumb = $item->getFirstMediaUrl('gallery') ?: $item->getFirstMediaUrl('target'))
                                    <img src="{{ $thumb }}" alt="{{ $item->title }}" loading="lazy"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                                @endif
                            </div>
                            <div class="p-4">
                                <h3 class="font-semibold text-gray-900">{{ $item->title }}</h3>
                                @if ($item->audioUrl())
                                    <span class="mt-1 inline-block text-xs text-gray-500">🔊 {{ __('explore.item.has_audio') }}</span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endforeach
    </main>
</x-layouts.app>
