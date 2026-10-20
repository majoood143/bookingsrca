<?php

namespace App\Services\Games;

/**
 * Signs that a shot was not made by a person swiping a phone: a scripted browser, swipes faster
 * than the game's own animations allow, perfectly straight or repeated paths, a path that does
 * not match the swipe it claims. Each sign adds points; an attempt whose total reaches the
 * campaign's threshold waits for an admin before it can rank. Nothing is rejected here, so a false
 * positive costs a review, never a real player's attempt.
 */
class RiskScorer
{
    /**
     * The quickest a real client can go from one shot request to the next: run-up, flight and the
     * result banner take about 2.4 s.
     */
    public const MIN_GAP_MS = 1800;

    public const WEIGHTS = [
        'webdriver' => 60,
        'too_fast' => 25,
        'too_few_points' => 15,
        'straight_path' => 8,
        'repeated_input' => 20,
        'inconsistent_input' => 20,
        'instant_swipe' => 15,
        'shared_device' => 20,
    ];

    /**
     * Flags for one shot.
     *
     * @param  array{u: float, v: float, speed: float, ms: int, points?: array<int, array{0: float, 1: float, 2: int}>, webdriver?: bool}  $input
     * @param  array<int, array<string, mixed>>  $previousInputs  this attempt's earlier shot inputs
     * @return list<string>
     */
    public function shotFlags(array $input, ?int $gapMs, array $previousInputs): array
    {
        $flags = [];
        $points = $input['points'] ?? [];

        if (! empty($input['webdriver'])) {
            $flags[] = 'webdriver';
        }

        if ($gapMs !== null && $gapMs < self::MIN_GAP_MS) {
            $flags[] = 'too_fast';
        }

        if (count($points) < 4) {
            $flags[] = 'too_few_points';
        } else {
            if ($this->isStraight($points)) {
                $flags[] = 'straight_path';
            }

            $last = end($points);
            $first = reset($points);

            // the path's own end must agree with the swipe it reports (both in ball-to-bar units)
            if (abs(($last[0] - $first[0]) - $input['u']) > 0.08 || abs(($first[1] - $last[1]) - $input['v']) > 0.08) {
                $flags[] = 'inconsistent_input';
            }
        }

        if (($input['ms'] ?? 0) < 30) {
            $flags[] = 'instant_swipe';
        }

        foreach ($previousInputs as $previous) {
            if (round((float) $previous['u'], 3) === round((float) $input['u'], 3)
                && round((float) $previous['v'], 3) === round((float) $input['v'], 3)
                && round((float) $previous['speed'], 3) === round((float) $input['speed'], 3)) {
                $flags[] = 'repeated_input';
                break;
            }
        }

        return $flags;
    }

    /** @param  list<string>  $flags  all flags of an attempt (repeats allowed) */
    public function score(array $flags): int
    {
        $total = 0;

        foreach ($flags as $flag) {
            $total += self::WEIGHTS[$flag] ?? 0;
        }

        return min(100, $total);
    }

    /**
     * A finger never draws a perfectly straight line: every point within a hair of the chord from
     * the first to the last point means the path was generated.
     *
     * @param  array<int, array{0: float, 1: float, 2: int}>  $points
     */
    private function isStraight(array $points): bool
    {
        $first = reset($points);
        $last = end($points);
        $dx = $last[0] - $first[0];
        $dy = $last[1] - $first[1];
        $length = hypot($dx, $dy);

        if ($length < 0.05) {
            return false;
        }

        foreach ($points as $p) {
            $distance = abs($dy * ($p[0] - $first[0]) - $dx * ($p[1] - $first[1])) / $length;

            // 0.004 ball-to-bar units is about a pixel on a phone
            if ($distance > 0.004) {
                return false;
            }
        }

        return true;
    }
}
