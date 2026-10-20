<?php

namespace App\Services\Messaging;

/** How a customer message went out (or why it did not). */
final class MessageResult
{
    public const WHATSAPP = 'whatsapp';

    public const SMS = 'sms';

    public function __construct(
        public readonly bool $ok,
        public readonly ?string $channel = null,
        public readonly ?string $error = null,
        /** sent through the demo SMS driver: nothing reached the phone */
        public readonly bool $demo = false,
    ) {}

    public static function failed(string $error): self
    {
        return new self(false, null, $error);
    }
}
