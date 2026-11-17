<x-layouts.app :title="$zone->title . ' | ' . $destination->title">
    @push('meta')
        <meta name="robots" content="noindex, nofollow">
        @vite('resources/js/ar/scanner.js')
    @endpush

    <script type="application/json" id="ar-data">{!! json_encode([
        'mindUrl' => $zone->getFirstMediaUrl('mind_file'),
        'cards' => $cards,
        'i18n' => [
            'loading' => __('explore.scan.loading'),
            'point' => __('explore.scan.point'),
            'camera_denied' => __('explore.scan.camera_denied'),
            'error' => __('explore.scan.error'),
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>

    <div id="ar-scanner" class="fixed inset-0 bg-black text-white overflow-hidden">
        <video playsinline muted autoplay class="absolute inset-0 w-full h-full object-cover"></video>

        <div class="absolute top-0 inset-x-0 p-4 flex items-center gap-3 bg-gradient-to-b from-black/70 to-transparent">
            <a href="{{ route('ar.destination', $destination->slug) }}" class="px-3 py-1.5 rounded-full bg-white/20 text-sm font-semibold">
                {{ __('explore.scan.back') }}
            </a>
            <span class="font-semibold truncate">{{ $destination->title }} · {{ $zone->title }}</span>
        </div>

        <p data-status role="status" aria-live="polite"
            class="absolute bottom-8 inset-x-4 text-center text-sm bg-black/60 rounded-full py-2 px-4">
            {{ __('explore.scan.loading') }}
        </p>

        <div data-card hidden class="absolute bottom-0 inset-x-0 bg-white text-gray-900 rounded-t-3xl p-6 shadow-2xl max-h-[70vh] overflow-y-auto">
            <div class="flex items-start gap-3">
                <h2 data-title class="text-xl font-bold flex-1"></h2>
                <button type="button" data-close aria-label="{{ __('explore.scan.close') }}" class="text-2xl leading-none text-gray-400">&times;</button>
            </div>
            <p data-summary class="mt-2 text-gray-700"></p>
            <div class="mt-5 flex flex-wrap gap-3">
                <button type="button" data-listen hidden class="px-5 py-2.5 rounded-full bg-brand text-white font-semibold"
                    data-label-play="🔊 {{ __('explore.item.listen') }}" data-label-pause="⏸ {{ __('explore.item.pause') }}">
                    🔊 {{ __('explore.item.listen') }}
                </button>
                <a data-link href="#" class="px-5 py-2.5 rounded-full bg-gray-100 font-semibold">{{ __('explore.scan.read_more') }}</a>
            </div>
        </div>
    </div>
</x-layouts.app>
