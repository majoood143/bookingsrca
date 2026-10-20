<?php

namespace Tests\Feature\Games;

use App\Http\Middleware\EnsureAppIsInstalled;
use App\Models\BookingSetting;
use App\Models\GameCampaign;
use App\Models\GamePlayer;
use App\Services\Games\PenaltyEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class GameTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Other tests delete storage/installed; the games do not depend on the installer.
        $this->withoutMiddleware(EnsureAppIsInstalled::class);
        $this->withoutVite();

        // Codes go through the demo SMS driver: nothing is sent, the code comes back.
        BookingSetting::set('whatsapp_enabled', 'false');
        BookingSetting::set('otp_sms_fallback_enabled', 'true');
        BookingSetting::set('sms_driver', 'demo');
    }

    protected function campaign(array $overrides = []): GameCampaign
    {
        return GameCampaign::create(array_merge([
            'name' => ['en' => 'Penalty Challenge', 'ar' => 'تحدي ركلات الجزاء'],
            'slug' => 'penalty',
            'status' => GameCampaign::STATUS_PUBLISHED,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
            'shots_per_attempt' => 5,
            'attempts_per_day' => 3,
            'keeper_skill' => 50,
            'risk_threshold' => 60,
        ], $overrides));
    }

    protected function player(string $mobile = '96895512928', array $overrides = []): GamePlayer
    {
        return GamePlayer::create(array_merge([
            'mobile' => $mobile,
            'name' => 'Salim Al-Kumzari',
            'verified_at' => now(),
            'terms_accepted_at' => now(),
        ], $overrides));
    }

    protected function signIn(GamePlayer $player): static
    {
        return $this->withSession(['games.player_id' => $player->id]);
    }

    /** A plausible human swipe for a target in goal-relative units. */
    protected function swipe(int $n, float $u = 0.3, float $v = 0.9, float $speed = 1.9): array
    {
        $points = [];
        for ($i = 0; $i <= 8; $i++) {
            $k = $i / 8;
            // a slight curve, like a thumb
            $points[] = [round($u * $k + 0.03 * sin($k * M_PI), 4), round(-$v * $k + 0.02 * sin($k * M_PI), 4), (int) (12 * $i + $n)];
        }

        return ['n' => $n, 'u' => $u, 'v' => $v, 'speed' => $speed, 'ms' => 110, 'points' => $points, 'webdriver' => false];
    }

    /**
     * A swipe the engine scores as a goal (or a miss) for this seed and shot. Each shot gets its own
     * small offset, like a real thumb, so the repeated-swipe check does not fire.
     */
    protected function swipeWithOutcome(int $seed, int $n, int $skill, bool $goal): array
    {
        $engine = new PenaltyEngine();

        for ($v = 0.25; $v <= 0.95; $v += 0.05) {
            for ($u = -0.6; $u <= 0.6; $u += 0.05) {
                $uu = round($u + $n * 0.0013, 4);
                $vv = round($v + $n * 0.0011, 4);
                $outcome = $engine->shoot($seed, $n, $skill, ['u' => $uu, 'v' => $vv, 'speed' => 1.9])['outcome'];

                if (($outcome === 'goal') === $goal) {
                    return $this->swipe($n, $uu, $vv);
                }
            }
        }

        $this->fail('No swipe found for the wanted outcome.');
    }
}
