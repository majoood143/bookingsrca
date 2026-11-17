<?php

namespace App\Services\Games;

use Random\Engine\Xoshiro256StarStar;
use Random\Randomizer;

/**
 * The penalty kick rules, decided on the server so nobody can forge a score or see the keeper
 * coming. Pure: the same seed, shot number, skill and swipe always give the same result, so any
 * shot can be replayed later (admin review, tests).
 *
 * The browser sends the swipe in goal-relative units, not pixels: u and v are the swipe's
 * horizontal and upward length divided by the on-screen distance from the ball to the crossbar,
 * and speed is px/ms scaled to an 800px-tall screen. That keeps the mapping independent of the
 * device and the camera (resources/js/games/penalty/engine.js mirrors aim() for drawing only).
 *
 * Units are metres; the goal is centred on x = 0, the goal line is z = 0, the spot is 11 m out.
 */
class PenaltyEngine
{
    /** Half the goal width (7.32 m / 2), the goal height and the ball radius. */
    public const GOAL_HALF_WIDTH = 3.66;

    public const GOAL_HEIGHT = 2.44;

    public const BALL_RADIUS = 0.11;

    public const SPOT_DISTANCE = 11.0;

    /**
     * How far below the goal line the ball sits on screen, in metres at the goal plane, for the
     * game's broadcast camera (1.7 m up, 15.6 m back). A swipe aimed at height y crosses the goal
     * plane (y + this) metres above the ball on screen.
     */
    public const BALL_SCREEN_DROP = 3.69;

    private const KEEPER_STANDS = [-2.5, -2.5, 0.0, 2.5, 2.5];

    /**
     * @param  array{u: float, v: float, speed: float}  $swipe
     * @return array{aim: array{x: float, y: float}, power: float, outcome: string, keeper: array{x: float, y: float, delay: float, dur: float, read: bool}, flight: array{speed: float, time: float}}
     */
    public function shoot(int $seed, int $shot, int $keeperSkill, array $swipe): array
    {
        $rng = $this->rng($seed, $shot);
        $s = max(0, min(100, $keeperSkill)) / 100;

        $power = self::power((float) $swipe['speed']);
        $aim = self::aim((float) $swipe['u'], (float) $swipe['v'], $power);

        // A hard strike is a little less precise: the harder, the more it drifts.
        $drift = max(0.0, $power - 0.85);
        $aim['x'] += ($rng() - 0.5) * 0.30 * $drift;
        $aim['y'] = max(self::BALL_RADIUS, $aim['y'] + ($rng() - 0.5) * 0.20 * $drift);

        // from the stored (3-decimal) power, so a replayed shot flies exactly the same
        $flight = self::flight(round($power, 3));
        $time = $flight['time'];

        $outcome = $this->woodworkOrOff($aim['x'], $aim['y']);

        // The keeper reads soft and slow kicks more often.
        $read = $rng() < 0.20 + 0.30 * $s + (1 - min($power, 1)) * 0.30;
        $reach = 2.4 + 0.6 * $s;

        if ($read) {
            $kx = $this->clamp($aim['x'] + ($rng() - 0.5) * 0.9, -$reach, $reach);
            $ky = $this->clamp($aim['y'], 0.45, 1.85);
        } else {
            $kx = self::KEEPER_STANDS[min(4, (int) floor($rng() * 5))];
            $ky = $rng() < 0.5 ? 0.5 : 1.7;
        }

        $delay = (0.16 - 0.08 * $s) + $rng() * 0.08;
        $dur = 0.56 - 0.12 * $s;

        if ($outcome === null) {
            $q = self::easeOut($this->clamp(($time - $delay) / $dur, 0, 1));
            $ex = $kx * $q;
            $ey = 1.0 + ($ky - 1.0) * $q;
            $reachRadius = (0.70 + 0.24 * $s) - min($power, 1.3) * 0.16 + ($read ? 0.08 : 0);
            $outcome = hypot($aim['x'] - $ex, $aim['y'] - $ey) < $reachRadius ? 'save' : 'goal';
        }

        return [
            'aim' => ['x' => round($aim['x'], 3), 'y' => round($aim['y'], 3)],
            'power' => round($power, 3),
            'outcome' => $outcome,
            'keeper' => ['x' => round($kx, 3), 'y' => round($ky, 3), 'delay' => round($delay, 3), 'dur' => round($dur, 3), 'read' => $read],
            'flight' => $flight,
        ];
    }

    /** @return array{speed: float, time: float} the ball's speed (m/s) and its time to the goal line */
    public static function flight(float $power): array
    {
        $speed = 15 + min($power, 1.4) * 17;

        return ['speed' => round($speed, 3), 'time' => round(self::SPOT_DISTANCE / $speed, 4)];
    }

    /** Swipe speed (px/ms on an 800px-tall screen) to power: 0.05 (a tap) to 1.4 (overhit). */
    public static function power(float $speed): float
    {
        return max(0.05, min(1.4, ($speed - 0.25) / 1.9));
    }

    /**
     * Where the swipe sends the ball on the goal plane. Swiping the ball-to-crossbar distance aims
     * at crossbar height; overhitting lifts the ball. Sideways follows the swipe's direction.
     *
     * @return array{x: float, y: float}
     */
    public static function aim(float $u, float $v, float $power): array
    {
        $y = max(self::BALL_RADIUS, min(5.0, self::GOAL_HEIGHT * $v * 1.05 + max(0.0, $power - 0.95) * 1.2));
        $x = ($u / max($v, 0.05)) * (self::BALL_SCREEN_DROP + $y);

        return ['x' => max(-9.0, min(9.0, $x)), 'y' => $y];
    }

    public static function easeOut(float $t): float
    {
        return 1 - (1 - $t) ** 3;
    }

    /** "over", "wide" or "post" when the ball misses or hits the frame; null when it is on target. */
    private function woodworkOrOff(float $x, float $y): ?string
    {
        $ax = abs($x);

        if ($y > self::GOAL_HEIGHT + self::BALL_RADIUS + 0.04 || $ax > self::GOAL_HALF_WIDTH + self::BALL_RADIUS + 0.04) {
            return ($y - self::GOAL_HEIGHT) > ($ax - self::GOAL_HALF_WIDTH) ? 'over' : 'wide';
        }

        if ($ax > self::GOAL_HALF_WIDTH - self::BALL_RADIUS || $y > self::GOAL_HEIGHT - self::BALL_RADIUS) {
            return 'post';
        }

        return null;
    }

    /** A float in [0, 1) from a generator seeded by the attempt's secret seed and the shot number. */
    private function rng(int $seed, int $shot): \Closure
    {
        $randomizer = new Randomizer(new Xoshiro256StarStar(hash('sha256', $seed.'|'.$shot, true)));

        return fn (): float => $randomizer->getInt(0, (1 << 53) - 1) / (1 << 53);
    }

    private function clamp(float $v, float $min, float $max): float
    {
        return max($min, min($max, $v));
    }
}
