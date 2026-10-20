<?php

namespace App\Http\Controllers\Games;

use App\Http\Controllers\Controller;
use App\Models\BookingSetting;
use App\Models\GameCampaign;
use App\Models\GamePlayer;
use App\Models\GamePrize;
use App\Services\Games\GameAttemptService;
use App\Services\Games\LeaderboardService;
use App\Services\Messaging\CustomerMessenger;
use App\Support\QrCode;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The public game page and the public leaderboard (also a full-screen display for club and
 * stadium screens with ?display=1). Drafts are only visible to signed-in admins, for previews.
 */
class GamePageController extends Controller
{
    public function play(Request $request, GameCampaign $campaign, GameAttemptService $attempts, CustomerMessenger $messenger): View
    {
        $this->ensureVisible($campaign);

        $player = $this->player($request);

        return view('games.penalty', [
            'campaign' => $campaign,
            'brand' => $campaign->brand(),
            'config' => [
                'campaign' => $this->campaignPayload($campaign),
                'brand' => $campaign->brand(),
                'player' => $player ? GameApiController::playerPayload($player, $campaign, $attempts) : null,
                'demo' => $messenger->isDemo(),
                'locale' => app()->getLocale(),
                'urls' => [
                    'requestCode' => route('games.api.code', $campaign->slug),
                    'verifyCode' => route('games.api.verify', $campaign->slug),
                    'me' => route('games.api.me', $campaign->slug),
                    'logout' => route('games.api.logout', $campaign->slug),
                    'start' => route('games.api.start', $campaign->slug),
                    'shot' => route('games.api.shot', [$campaign->slug, '__ATTEMPT__']),
                    'leaderboard' => route('games.api.leaderboard', $campaign->slug),
                    'leaderboardPage' => route('games.leaderboard', $campaign->slug),
                    'play' => $campaign->playUrl(),
                    'switchLocale' => route('lang.switch', app()->getLocale() === 'ar' ? 'en' : 'ar'),
                ],
                'i18n' => trans('games.public'),
            ],
        ]);
    }

    public function leaderboard(Request $request, GameCampaign $campaign, LeaderboardService $leaderboard): View
    {
        $this->ensureVisible($campaign);
        abort_unless($campaign->leaderboard_public || $request->user(), 404);

        $display = $request->boolean('display');

        return view('games.leaderboard', [
            'campaign' => $campaign,
            'brand' => $campaign->brand(),
            'display' => $display,
            'qr' => $display ? QrCode::dataUri($campaign->playUrl(), 480) : null,
            'config' => [
                'campaign' => $this->campaignPayload($campaign),
                'brand' => $campaign->brand(),
                'board' => $leaderboard->publicBoard($campaign),
                'display' => $display,
                'locale' => app()->getLocale(),
                'urls' => [
                    'leaderboard' => route('games.api.leaderboard', $campaign->slug),
                    'play' => $campaign->playUrl(),
                    'switchLocale' => route('lang.switch', app()->getLocale() === 'ar' ? 'en' : 'ar'),
                ],
                'i18n' => trans('games.public'),
            ],
        ]);
    }

    /** @return array<string, mixed> the campaign as the browser needs it, in the current language */
    public static function campaignPayload(GameCampaign $campaign): array
    {
        return [
            'slug' => $campaign->slug,
            'name' => $campaign->translated('name'),
            'description' => $campaign->translated('description'),
            'headline' => $campaign->translated('headline') ?: __('games.public.default_headline'),
            'tagline' => $campaign->translated('tagline'),
            'sponsor' => $campaign->translated('sponsor_name'),
            'boardText' => $campaign->translated('board_text') ?: $campaign->translated('name'),
            'prizeText' => $campaign->translated('prize_text'),
            'terms' => $campaign->translated('terms'),
            'shareText' => $campaign->translated('share_text'),
            'phase' => $campaign->phase(),
            'startsAt' => $campaign->starts_at->toIso8601String(),
            'endsAt' => $campaign->ends_at->toIso8601String(),
            'shots' => $campaign->shots_per_attempt,
            'attemptsPerDay' => $campaign->attempts_per_day,
            'attemptsTotal' => $campaign->attempts_total,
            'leaderboardPublic' => $campaign->leaderboard_public,
            'frozen' => $campaign->isFrozen(),
            'prizes' => $campaign->prizes->map(fn (GamePrize $p) => [
                'rank' => $p->rank,
                'title' => $p->getTranslation('title', app()->getLocale(), false) ?: $p->getTranslation('title', 'en', false),
                'discount' => $p->discountLabel(withCap: false),
                'cap' => $p->capLabel(),
            ])->values()->all(),
            'currency' => BookingSetting::currencySymbol(),
        ];
    }

    private function ensureVisible(GameCampaign $campaign): void
    {
        abort_unless((bool) BookingSetting::get('module_games_enabled', true), 404);
        abort_unless($campaign->status === GameCampaign::STATUS_PUBLISHED || auth()->check(), 404);
    }

    private function player(Request $request): ?GamePlayer
    {
        $id = $request->session()->get(GameApiController::SESSION_PLAYER);

        return $id ? GamePlayer::find($id) : null;
    }
}
