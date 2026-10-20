<?php

namespace Tests\Feature\Games;

use App\Services\Games\PenaltyEngine;
use App\Support\PhoneNumber;
use PHPUnit\Framework\TestCase;

class PenaltyEngineTest extends TestCase
{
    public function test_the_same_seed_and_swipe_always_give_the_same_shot(): void
    {
        $engine = new PenaltyEngine();
        $swipe = ['u' => 0.31, 'v' => 0.84, 'speed' => 1.7];

        $this->assertSame($engine->shoot(123456, 2, 50, $swipe), $engine->shoot(123456, 2, 50, $swipe));
        $this->assertNotSame($engine->shoot(123456, 2, 50, $swipe)['keeper'], $engine->shoot(654321, 2, 50, $swipe)['keeper']);
    }

    public function test_swiping_the_ball_to_bar_distance_aims_at_crossbar_height(): void
    {
        $aim = PenaltyEngine::aim(0.0, 1.0, 0.8);

        $this->assertEqualsWithDelta(PenaltyEngine::GOAL_HEIGHT * 1.05, $aim['y'], 0.001);
        $this->assertSame(0.0, $aim['x']);
    }

    public function test_misses_are_decided_by_where_the_ball_goes(): void
    {
        $engine = new PenaltyEngine();

        $this->assertSame('over', $engine->shoot(1, 1, 50, ['u' => 0.0, 'v' => 3.0, 'speed' => 1.5])['outcome']);
        $this->assertSame('wide', $engine->shoot(1, 1, 50, ['u' => 2.5, 'v' => 0.3, 'speed' => 1.5])['outcome']);
    }

    public function test_power_is_clamped(): void
    {
        $this->assertSame(0.05, PenaltyEngine::power(0));
        $this->assertSame(1.4, PenaltyEngine::power(50));
    }

    public function test_a_better_keeper_saves_more(): void
    {
        $engine = new PenaltyEngine();
        $saves = fn (int $skill) => collect(range(1, 400))
            ->filter(fn (int $seed) => $engine->shoot($seed, 1, $skill, ['u' => 0.15, 'v' => 0.6, 'speed' => 1.4])['outcome'] === 'save')
            ->count();

        $this->assertGreaterThan($saves(0), $saves(100));
    }

    public function test_mobile_numbers_are_normalized_and_masked_for_public_screens(): void
    {
        $this->assertSame('96895512928', PhoneNumber::normalize('9551 2928'));
        $this->assertSame('96895512928', PhoneNumber::normalize('+968 ٩٥٥١٢٩٢٨'));
        $this->assertTrue(PhoneNumber::isOmaniMobile('96895512928'));
        $this->assertFalse(PhoneNumber::isOmaniMobile('96824512928'));
        $this->assertSame('955**928', PhoneNumber::mask('96895512928'));
    }
}
