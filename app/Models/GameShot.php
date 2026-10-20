<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One kick as the server decided it: the player's swipe, where it went, the keeper, the outcome. */
class GameShot extends Model
{
    public const OUTCOMES = ['goal', 'save', 'post', 'wide', 'over'];

    protected $fillable = [
        'game_attempt_id',
        'n',
        'input',
        'aim_x',
        'aim_y',
        'power',
        'keeper',
        'outcome',
        'gap_ms',
    ];

    protected $casts = [
        'n' => 'integer',
        'input' => 'array',
        'aim_x' => 'float',
        'aim_y' => 'float',
        'power' => 'float',
        'keeper' => 'array',
        'gap_ms' => 'integer',
    ];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(GameAttempt::class, 'game_attempt_id');
    }
}
