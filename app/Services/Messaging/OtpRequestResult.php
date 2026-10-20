<?php

namespace App\Services\Messaging;

final class OtpRequestResult
{
    public const SENT = 'sent';

    public const THROTTLED = 'throttled';

    public const INVALID_PHONE = 'invalid_phone';

    public const UNAVAILABLE = 'unavailable';

    public function __construct(
        public readonly string $status,
        /** seconds until another code may be asked for */
        public readonly int $retryAfter = 0,
        /** the code itself, only under the "demo" SMS driver */
        public readonly ?string $demoCode = null,
        /** how the code went out: MessageResult::WHATSAPP or MessageResult::SMS */
        public readonly ?string $channel = null,
    ) {}

    public function sent(): bool
    {
        return $this->status === self::SENT;
    }
}
