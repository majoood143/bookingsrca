{{-- The client's logo and name (and the sponsor, when the campaign has one). --}}
<div class="g-club">
    @if ($brand['logo'])
        <img src="{{ $brand['logo'] }}" alt="{{ $brand['site_name'] }}">
    @endif
    <div>
        <b>{{ $brand['site_name'] }}</b>
        @if ($campaign->translated('sponsor_name') || $brand['sponsor_logo'])
            <span class="g-sponsor">
                @if ($brand['sponsor_logo'])
                    <img src="{{ $brand['sponsor_logo'] }}" alt="">
                @endif
                @if ($campaign->translated('sponsor_name'))
                    {{ __('games.public.presented_by', ['name' => $campaign->translated('sponsor_name')]) }}
                @endif
            </span>
        @endif
    </div>
</div>
