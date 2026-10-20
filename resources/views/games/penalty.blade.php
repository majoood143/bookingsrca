@php
    /** @var \App\Models\GameCampaign $campaign */
    $c = $config['campaign'];
    $phase = $c['phase'];
    $phaseLabel = match ($phase) {
        'live' => __('games.public.live') . ' · ' . __('games.public.ends_in'),
        'upcoming' => __('games.public.upcoming'),
        'ended' => __('games.public.ended'),
        default => __('games.campaign.phases.draft'),
    };
    // Admin-written rich text: keep simple formatting only, no scripts or event handlers.
    $terms = $c['terms']
        ? preg_replace(['/\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '/javascript:/i'], '', strip_tags($c['terms'], '<p><br><strong><b><em><i><ul><ol><li><a><h3><h4>'))
        : null;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    @include('games.partials.head', ['entry' => 'resources/js/games/penalty.js', 'canonical' => $campaign->playUrl()])
</head>
<body>
@include('partials.gtm-body')
<div class="g-app" id="gApp">
    <canvas class="g-canvas" id="gScene" aria-label="{{ $c['headline'] }}"></canvas>
    <canvas class="g-trail" id="gTrail"></canvas>
    <div class="g-loading" id="gLoading">{{ __('games.public.loading') }}</div>

    {{-- HUD while playing --}}
    <div class="g-hud" id="gHud" hidden>
        <div class="g-hudbar">
            <div class="g-pill"><div class="g-dots" id="gDots"></div></div>
            <div class="g-pill"><span class="g-num g-score" id="gScore">0</span><span class="g-num" id="gShotsTotal" style="color:var(--muted)">/{{ $c['shots'] }}</span></div>
            <div style="display:flex;gap:8px">
                <div class="g-pill"><span class="g-num" id="gTime">0:00.00</span></div>
                <button class="g-chip" id="gSound" type="button" aria-label="{{ __('games.public.sound_off') }}">🔊</button>
            </div>
        </div>
        <div class="g-banner" id="gBanner"><div class="big" id="gBnBig"></div><div class="sub" id="gBnSub"></div></div>
        <div class="g-hint" id="gHint"><div class="g-pill">{{ __('games.public.hint') }}</div></div>
    </div>

    {{-- START: campaign, prizes, sign up / welcome --}}
    <section class="g-screen" id="scrStart" hidden>
        <div class="g-topbar">
            <a class="g-chip" href="{{ $config['urls']['switchLocale'] }}">{{ __('games.public.switch_language') }}</a>
        </div>
        <div class="g-panel">
            @include('games.partials.club')

            <div class="g-eyebrow {{ $phase === 'live' ? 'is-live' : 'is-off' }}" id="gPhase">
                <span class="g-dot"></span>
                <span id="gPhaseText">{{ $phaseLabel }}</span>
                <span class="g-countdown" id="gCountdown" style="font-size:.95rem"></span>
            </div>

            <h1 class="g-title">{{ $c['headline'] }}</h1>
            @if ($c['tagline'])
                <p class="g-tagline">{{ $c['tagline'] }}</p>
            @endif

            @if ($c['prizeText'] || count($c['prizes']))
                <div class="g-prizes" aria-label="{{ __('games.public.prizes_title') }}">
                    @if ($c['prizeText'])
                        <div class="g-prize" style="grid-column:1/-1"><small>{{ __('games.public.prizes_title') }}</small><b>{{ $c['prizeText'] }}</b></div>
                    @else
                        @foreach ($c['prizes'] as $prize)
                            <div class="g-prize">
                                <small>{{ __('games.public.rank_label', ['n' => $prize['rank']]) }}</small>
                                <b>{{ $prize['discount'] }}</b>
                                @if ($prize['cap'])
                                    <em>{{ $prize['cap'] }}</em>
                                @endif
                                @if ($prize['title'])
                                    <span>{{ $prize['title'] }}</span>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
            @endif

            <div class="g-rules">
                <div class="g-rule"><b>{{ $c['shots'] }}</b>{{ __('games.public.rules_shots') }}</div>
                <div class="g-rule">
                    @if ($c['attemptsPerDay'])
                        <b>{{ $c['attemptsPerDay'] }}</b>{{ __('games.public.rules_attempts_day') }}
                    @elseif ($c['attemptsTotal'])
                        <b>{{ $c['attemptsTotal'] }}</b>{{ __('games.public.rules_attempts_total') }}
                    @else
                        <b>∞</b>{{ __('games.public.rules_unlimited') }}
                    @endif
                </div>
                <div class="g-rule"><b>⏱</b>{{ __('games.public.rules_tiebreak') }}</div>
            </div>

            {{-- 1. name + mobile → code on WhatsApp --}}
            <form class="g-form" id="gReg" novalidate hidden>
                <div class="g-field">
                    <label for="gName">{{ __('games.public.name') }}</label>
                    <div class="g-input"><input id="gName" autocomplete="name" placeholder="{{ __('games.public.name_placeholder') }}" maxlength="60" required></div>
                </div>
                <div class="g-field">
                    <label for="gMobile">{{ __('games.public.mobile') }}</label>
                    <div class="g-input" dir="ltr"><span class="g-prefix">+968</span><input id="gMobile" inputmode="numeric" autocomplete="tel-national" placeholder="{{ __('games.public.mobile_placeholder') }}" maxlength="12" required></div>
                </div>
                <label class="g-check"><input type="checkbox" id="gTerms" required>
                    <span>{{ __('games.public.terms_label') }}@if ($terms) · <a href="#gTermsBox" onclick="document.getElementById('gTermsBox').open=true">{{ __('games.public.terms_link') }}</a>@endif</span>
                </label>
                <label class="g-check"><input type="checkbox" id="gMarketing"><span>{{ __('games.public.marketing_label') }}</span></label>
                <p class="g-err" id="gRegErr" role="alert"></p>
                <button class="g-btn g-btn-primary" type="submit" id="gRegBtn">{{ __('games.public.send_code_whatsapp') }}</button>
            </form>

            {{-- 2. the code --}}
            <form class="g-form" id="gOtp" novalidate hidden>
                <div class="g-field">
                    <label for="gCode" id="gOtpTo"></label>
                    <div class="g-input g-otp"><input id="gCode" inputmode="numeric" autocomplete="one-time-code" maxlength="4" placeholder="••••"></div>
                </div>
                <p class="g-note" id="gOtpVia"></p>
                <p class="g-note" id="gDemo" hidden></p>
                <p class="g-err" id="gOtpErr" role="alert"></p>
                <button class="g-btn g-btn-primary" type="submit" id="gVerifyBtn">{{ __('games.public.verify') }}</button>
                <div class="g-btns">
                    <button class="g-linkbtn" type="button" id="gResend">{{ __('games.public.resend') }}</button>
                    <button class="g-linkbtn" type="button" id="gChange">{{ __('games.public.change_number') }}</button>
                </div>
            </form>

            {{-- 3. signed in --}}
            <div class="g-welcome" id="gWelcome" hidden>
                <b id="gWelcomeName"></b>
                <span id="gWelcomeMobile"></span>
                <span id="gWelcomeBest"></span>
                <span id="gWelcomeAttempts" role="status"></span>
                <button class="g-btn g-btn-primary" type="button" id="gPlayBtn" style="margin-top:6px">{{ __('games.public.play') }}</button>
                <button class="g-linkbtn" type="button" id="gNotYou">{{ __('games.public.not_you') }} {{ __('games.public.change_number') }}</button>
            </div>

            <div class="g-welcome" id="gClosed" hidden>
                <b>{{ $phase === 'upcoming' ? __('games.public.upcoming') : __('games.public.ended') }}</b>
                @if ($c['description'])
                    <span>{{ $c['description'] }}</span>
                @endif
            </div>

            @if ($terms)
                <details id="gTermsBox">
                    <summary class="g-note" style="cursor:pointer">{{ __('games.public.terms_link') }}</summary>
                    <div class="g-terms">{!! $terms !!}</div>
                </details>
            @endif

            @if ($c['leaderboardPublic'])
                <button class="g-linkbtn" type="button" data-go="board">{{ __('games.public.leaderboard') }}</button>
            @endif
        </div>
    </section>

    {{-- RESULT --}}
    <section class="g-screen" id="scrResult" hidden>
        <div class="g-panel">
            <div class="g-eyebrow" id="gResTitle"></div>
            <div class="g-final">
                <div class="big" id="gResGoals">0<small>/{{ $c['shots'] }}</small></div>
                <div class="t"><span>{{ __('games.public.total_time') }}</span><b class="g-num" id="gResTime">0:00.00</b></div>
            </div>
            <div class="g-shots" id="gResShots"></div>
            <p class="g-rankline" id="gResRank"></p>
            <p class="g-review" id="gResReview" hidden>{{ __('games.public.under_review') }}</p>
            <p class="g-note" id="gResAttempts"></p>
            <div class="g-btns">
                <a class="g-btn g-btn-wa g-full" id="gShare" href="#" target="_blank" rel="noopener">{{ __('games.public.share') }}</a>
                @if ($c['leaderboardPublic'])
                    <button class="g-btn g-btn-ghost" type="button" data-go="board">{{ __('games.public.leaderboard') }}</button>
                @endif
                <button class="g-btn g-btn-ghost" type="button" id="gAgain">{{ __('games.public.play_again') }}</button>
            </div>
        </div>
    </section>

    {{-- LEADERBOARD --}}
    <section class="g-screen" id="scrBoard" hidden>
        <div class="g-panel">
            <div class="g-headrow"><h2>{{ __('games.public.leaderboard') }}</h2><span id="gBoardMeta"></span></div>
            <div class="g-board" id="gBoard"></div>
            <div class="g-btns">
                <button class="g-btn g-btn-ghost" type="button" id="gBoardBack">{{ __('games.public.back') }}</button>
                <a class="g-btn g-btn-ghost" href="{{ $config['urls']['leaderboardPage'] }}">{{ __('games.public.top_players') }}</a>
            </div>
        </div>
    </section>
</div>

<script>window.__GAME__ = @json($config);</script>
</body>
</html>
