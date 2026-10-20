<?php

namespace App\Http\Controllers\Games;

use App\Http\Controllers\Controller;
use App\Models\BookingSetting;
use App\Models\GameAttempt;
use App\Models\GameCampaign;
use App\Models\GamePlayer;
use App\Services\Games\GameAttemptService;
use App\Services\Games\GameException;
use App\Services\Games\LeaderboardService;
use App\Services\Messaging\OtpRequestResult;
use App\Services\Messaging\OtpService;
use App\Support\PhoneNumber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * The game's JSON endpoints, on the web session: sign up with a one-time code, start an attempt,
 * send each shot, read the leaderboard. The browser only animates what the server decides here.
 */
class GameApiController extends Controller
{
    public const SESSION_PLAYER = 'games.player_id';

    public const SESSION_PENDING = 'games.pending';

    public function requestCode(Request $request, GameCampaign $campaign, OtpService $otp): JsonResponse
    {
        $this->ensureOpenModule($campaign);

        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:60'],
            'mobile' => ['required', 'string', 'max:30'],
            'accept_terms' => ['accepted'],
            'marketing_opt_in' => ['nullable', 'boolean'],
        ], [], [
            'name' => __('games.public.name'),
            'mobile' => __('games.public.mobile'),
            'accept_terms' => __('games.public.terms_label'),
        ]);

        $mobile = PhoneNumber::normalize($data['mobile']);

        if (! PhoneNumber::isOmaniMobile($mobile)) {
            throw ValidationException::withMessages(['mobile' => __('games.errors.invalid_mobile')]);
        }

        $result = $otp->request($mobile, $request->ip());

        if (! $result->sent()) {
            return response()->json([
                'error' => $result->status,
                'message' => match ($result->status) {
                    OtpRequestResult::THROTTLED => __('games.errors.code_throttled', ['seconds' => $result->retryAfter]),
                    OtpRequestResult::INVALID_PHONE => __('games.errors.invalid_mobile'),
                    default => __('games.errors.code_unavailable'),
                },
                'retry_after' => $result->retryAfter,
            ], $result->status === OtpRequestResult::THROTTLED ? 429 : 422);
        }

        $request->session()->put(self::SESSION_PENDING, [
            'mobile' => $mobile,
            'name' => trim(strip_tags($data['name'])),
            'marketing_opt_in' => (bool) ($data['marketing_opt_in'] ?? false),
        ]);

        return response()->json([
            'status' => 'sent',
            'channel' => $result->channel,
            'to' => '+' . PhoneNumber::DEFAULT_COUNTRY_CODE . ' ' . PhoneNumber::local($mobile),
            'retry_after' => $result->retryAfter,
            'demo_code' => $result->demoCode,
        ]);
    }

    public function verifyCode(Request $request, GameCampaign $campaign, OtpService $otp, GameAttemptService $attempts): JsonResponse
    {
        $this->ensureOpenModule($campaign);

        $data = $request->validate(['code' => ['required', 'string', 'max:10']]);
        $pending = $request->session()->get(self::SESSION_PENDING);

        if (! $pending) {
            return $this->error(new GameException('code_expired'));
        }

        $status = $otp->check($pending['mobile'], $data['code']);

        if ($status !== OtpService::VERIFIED) {
            return $this->error(new GameException($status === OtpService::WRONG ? 'code_wrong' : 'code_expired'));
        }

        $player = GamePlayer::firstOrNew(['mobile' => $pending['mobile']]);
        $player->fill([
            'name' => $pending['name'],
            'locale' => app()->getLocale(),
            'terms_accepted_at' => now(),
            'marketing_opt_in' => $pending['marketing_opt_in'],
            'last_ip' => $request->ip(),
            'last_seen_at' => now(),
        ]);
        $player->verified_at ??= now();
        $player->first_ip ??= $request->ip();
        $player->save();

        $request->session()->forget(self::SESSION_PENDING);
        // A new session id against fixation, but the same CSRF token: regenerate() would rotate it
        // and the page's next call (starting the attempt) would fail with 419.
        $request->session()->migrate(true);
        $request->session()->put(self::SESSION_PLAYER, $player->id);

        return response()->json(['player' => self::playerPayload($player, $campaign, $attempts)]);
    }

    public function me(Request $request, GameCampaign $campaign, GameAttemptService $attempts): JsonResponse
    {
        $player = $this->player($request);

        return response()->json(['player' => $player ? self::playerPayload($player, $campaign, $attempts) : null]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->session()->forget([self::SESSION_PLAYER, self::SESSION_PENDING]);

        return response()->json(['player' => null]);
    }

    public function start(Request $request, GameCampaign $campaign, GameAttemptService $attempts): JsonResponse
    {
        $this->ensureOpenModule($campaign);
        $player = $this->player($request);

        if (! $player) {
            return $this->error(new GameException('sign_in_required', status: 401));
        }

        $device = (string) $request->input('device', '');
        $deviceHash = $device !== '' ? hash('sha256', mb_substr($device, 0, 256) . '|' . $request->userAgent()) : null;

        try {
            $attempt = $attempts->start($campaign, $player, $request->ip(), $request->userAgent(), $deviceHash);
        } catch (GameException $e) {
            return $this->error($e);
        }

        $player->forceFill(['last_ip' => $request->ip(), 'last_device_hash' => $deviceHash, 'last_seen_at' => now()])->save();

        return response()->json([
            'attempt' => $attempt->uuid,
            'shots' => $campaign->shots_per_attempt,
            'remaining' => $attempts->remaining($campaign, $player),
        ]);
    }

    public function shoot(Request $request, GameCampaign $campaign, string $attempt, GameAttemptService $attempts): JsonResponse
    {
        $player = $this->player($request);

        if (! $player) {
            return $this->error(new GameException('sign_in_required', status: 401));
        }

        $data = $request->validate([
            'n' => ['required', 'integer', 'min:1', 'max:20'],
            'u' => ['required', 'numeric', 'between:-10,10'],
            'v' => ['required', 'numeric', 'between:0.05,10'],
            'speed' => ['required', 'numeric', 'between:0,30'],
            'ms' => ['required', 'integer', 'between:0,20000'],
            'points' => ['nullable', 'array', 'max:40'],
            'points.*' => ['array', 'size:3'],
            'points.*.*' => ['numeric', 'between:-100000,100000'],
            'webdriver' => ['nullable', 'boolean'],
        ]);

        $model = GameAttempt::query()
            ->where('uuid', $attempt)
            ->where('game_campaign_id', $campaign->id)
            ->where('game_player_id', $player->id)
            ->first();

        if (! $model) {
            return $this->error(new GameException('attempt_closed', status: 404));
        }

        $input = [
            'u' => round((float) $data['u'], 4),
            'v' => round((float) $data['v'], 4),
            'speed' => round((float) $data['speed'], 4),
            'ms' => (int) $data['ms'],
            'points' => array_map(fn (array $p) => [round((float) $p[0], 4), round((float) $p[1], 4), (int) $p[2]], array_values($data['points'] ?? [])),
            'webdriver' => (bool) ($data['webdriver'] ?? false),
        ];

        try {
            return response()->json($attempts->shoot($model, (int) $data['n'], $input));
        } catch (GameException $e) {
            return $this->error($e);
        }
    }

    public function leaderboard(Request $request, GameCampaign $campaign, LeaderboardService $leaderboard): JsonResponse
    {
        abort_unless((bool) BookingSetting::get('module_games_enabled', true), 404);
        abort_unless($campaign->status === GameCampaign::STATUS_PUBLISHED || $request->user(), 404);
        abort_unless($campaign->leaderboard_public || $request->user(), 404);

        $player = $this->player($request);
        $standing = $player ? $leaderboard->standing($campaign, $player) : null;

        return response()->json($leaderboard->publicBoard($campaign) + [
            'me' => $standing ? ['rank' => $standing['rank'], 'player' => $player->maskedMobile(), 'goals' => $standing['goals'], 'duration_ms' => $standing['duration_ms']] : null,
            'phase' => $campaign->phase(),
        ]);
    }

    /** @return array<string, mixed> */
    public static function playerPayload(GamePlayer $player, GameCampaign $campaign, GameAttemptService $attempts): array
    {
        $standing = app(LeaderboardService::class)->standing($campaign, $player);

        return [
            'name' => $player->name,
            'firstName' => explode(' ', trim($player->name))[0],
            'mobile' => $player->maskedMobile(),
            'blocked' => $player->isBlocked(),
            'remaining' => $attempts->remaining($campaign, $player),
            'standing' => $standing,
        ];
    }

    private function player(Request $request): ?GamePlayer
    {
        $id = $request->session()->get(self::SESSION_PLAYER);

        return $id ? GamePlayer::find($id) : null;
    }

    private function ensureOpenModule(GameCampaign $campaign): void
    {
        abort_unless((bool) BookingSetting::get('module_games_enabled', true), 404);
        abort_unless($campaign->status === GameCampaign::STATUS_PUBLISHED || auth()->check(), 404);
    }

    private function error(GameException $e): JsonResponse
    {
        return response()->json(['error' => $e->reason, 'message' => $e->getMessage()], $e->status);
    }
}
