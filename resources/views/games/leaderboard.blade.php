@php
    /** @var \App\Models\GameCampaign $campaign */
    $c = $config['campaign'];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    @include('games.partials.head', [
        'entry' => 'resources/js/games/leaderboard.js',
        'title' => __('games.public.leaderboard') . ' · ' . $campaign->translated('name'),
        'canonical' => $campaign->leaderboardUrl(),
    ])
</head>
<body class="lb-page {{ $display ? 'lb-display' : '' }}">
@include('partials.gtm-body')
<div class="g-app" id="gApp">
    <canvas class="g-canvas" id="gScene" aria-hidden="true"></canvas>
    <div class="g-loading" id="gLoading">{{ __('games.public.loading') }}</div>

    <section class="g-screen">
        @unless ($display)
            <div class="g-topbar">
                <a class="g-chip" href="{{ $config['urls']['switchLocale'] }}">{{ __('games.public.switch_language') }}</a>
            </div>
        @endunless

        <div class="g-panel">
            <div style="display:flex;flex-direction:column;gap:14px">
                @include('games.partials.club')
                <div class="g-headrow">
                    <h2>{{ $c['headline'] }}</h2>
                    <span>{{ __('games.public.leaderboard') }}</span>
                </div>
                <div class="g-board" id="gBoard" aria-live="polite"></div>
                <div class="lb-meta"><span id="gBoardMeta"></span><span id="gUpdated"></span></div>

                @unless ($display)
                    @if ($c['phase'] === 'live')
                        <a class="g-btn g-btn-primary" href="{{ $config['urls']['play'] }}">{{ __('games.public.play') }}</a>
                    @endif
                @endunless
            </div>

            @if ($display)
                <div class="lb-qr">
                    <img src="{{ $qr }}" alt="">
                    <b>{{ __('games.public.scan_to_play') }}</b>
                    <span>{{ preg_replace('#^https?://#', '', $config['urls']['play']) }}</span>
                </div>
            @endif
        </div>
    </section>
</div>

<script>window.__GAME__ = @json($config);</script>
</body>
</html>
