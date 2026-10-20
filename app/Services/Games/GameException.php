<?php

namespace App\Services\Games;

use RuntimeException;

/**
 * A rule a player ran into (campaign closed, no attempts left, shot out of order...). The code is
 * stable for the browser; the message is translated for the player.
 */
class GameException extends RuntimeException
{
    public function __construct(public readonly string $reason, ?string $message = null, public readonly int $status = 422)
    {
        parent::__construct($message ?? __('games.errors.' . $reason));
    }
}
