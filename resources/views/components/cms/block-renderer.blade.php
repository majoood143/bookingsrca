@props(['blocks' => []])

<div {{ $attributes->class(['cms-blocks']) }}>
    @foreach ($blocks ?? [] as $block)
        @php
            $type = $block['type'] ?? null;
            $data = $block['data'] ?? [];
            $view = 'components.cms.blocks.'.str_replace('_', '-', (string) $type);
        @endphp

        @if ($type && \Illuminate\Support\Facades\View::exists($view))
            @include($view, ['data' => $data])
        @else
            @include('components.cms.blocks.unknown', ['type' => $type])
        @endif
    @endforeach
</div>
