@php
    $targets = $zone->targetItems()->map(fn ($item) => ['id' => $item->id, 'url' => $item->getFirstMediaUrl('target')])->values();
    $tooMany = $targets->count() > \App\Models\ArZone::MAX_TARGETS;
@endphp
<div class="space-y-4 text-sm"
    x-data="arCompiler({ uploadUrl: @js(route('ar.zone.targets', $zone)), targets: @js($targets), csrf: @js(csrf_token()) })">
    @if ($targets->isEmpty())
        <p>{{ __('explore.compile.none') }}</p>
    @elseif ($tooMany)
        <p class="text-danger-600">{{ __('explore.compile.too_many', ['count' => $targets->count(), 'max' => \App\Models\ArZone::MAX_TARGETS]) }}</p>
    @else
        <p>{{ __('explore.compile.intro', ['count' => $targets->count()]) }}</p>

        <x-filament::button x-show="state === 'idle' || state === 'error'" x-on:click="run()" icon="heroicon-o-cpu-chip">
            {{ __('explore.compile.start') }}
        </x-filament::button>

        <div x-show="state === 'running'" x-cloak class="space-y-2">
            <p x-text="@js(__('explore.compile.running', ['progress' => '__P__'])).replace('__P__', progress)"></p>
            <div class="h-2 rounded-full bg-gray-200 overflow-hidden">
                <div class="h-full bg-primary-600 transition-all" :style="`width: ${progress}%`"></div>
            </div>
        </div>

        <p x-show="state === 'done'" x-cloak class="text-success-600 font-semibold">{{ __('explore.compile.done') }}</p>
        <p x-show="state === 'error'" x-cloak class="text-danger-600">{{ __('explore.compile.failed') }} <span x-text="message"></span></p>
    @endif
</div>
