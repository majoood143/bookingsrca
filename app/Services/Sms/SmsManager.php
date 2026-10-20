<?php

namespace App\Services\Sms;

use App\Models\MessageLog;
use App\Services\Sms\Gateways\LogSmsGateway;
use App\Services\Sms\Gateways\TamimahSmsGateway;
use App\Support\MessagingSettings;
use Throwable;

/**
 * The one way the app sends an SMS: picks the gateway chosen in General Settings → Messaging,
 * keeps a log of every attempt, and never lets a failing gateway break the caller.
 *
 * Drivers: "tamimah" (real messages), "log" (development: to the application log), "demo"
 * (nothing is sent; the game shows the code on screen, for presentations).
 */
class SmsManager
{
    public const TAMIMAH = 'tamimah';

    public const LOG = 'log';

    public const DEMO = 'demo';

    /** @return array<string, string> driver => label, for the settings form */
    public static function driverOptions(): array
    {
        $options = [self::TAMIMAH => 'Tamimah Telecom'];

        if (! app()->isProduction()) {
            $options[self::LOG] = 'Log (development)';
            $options[self::DEMO] = 'Demo (code shown on screen)';
        }

        return $options;
    }

    /**
     * The chosen driver. On a live site only real messages: "demo" hands the code to whoever asks,
     * so anyone could verify any phone; "log" writes codes to the log.
     */
    public function driver(): ?string
    {
        $driver = MessagingSettings::smsDriver();

        return array_key_exists($driver, self::driverOptions()) ? $driver : null;
    }

    public function isDemo(): bool
    {
        return $this->driver() === self::DEMO;
    }

    private function gateway(): ?SmsGateway
    {
        return match ($this->driver()) {
            self::TAMIMAH => app(TamimahSmsGateway::class),
            self::LOG => app(LogSmsGateway::class),
            default => null,
        };
    }

    /** Whether a message can actually reach a phone (the demo driver shows the code instead). */
    public function canSend(): bool
    {
        return $this->isDemo() || ($this->gateway()?->isConfigured() ?? false);
    }

    /**
     * @param  string  $type  what the message is for (otp, game_award, test...), for the log
     * @param  bool  $sensitive  keep the text out of the log (one-time codes)
     */
    public function send(string $to, string $message, string $type, bool $sensitive = false): SmsResult
    {
        $gateway = $this->gateway();

        if ($this->isDemo()) {
            $result = new SmsResult(true, 'demo', null, ['note' => 'demo driver: nothing was sent']);
        } elseif (! $gateway || ! $gateway->isConfigured()) {
            $result = SmsResult::failed('No SMS gateway is set up.');
        } else {
            try {
                $result = $gateway->send($to, $message);
            } catch (Throwable $e) {
                $result = SmsResult::failed('SMS gateway error: ' . $e->getMessage());
            }
        }

        MessageLog::create([
            'channel' => 'sms',
            'type' => $type,
            'recipient' => $to,
            'status' => $result->ok ? 'sent' : 'failed',
            'message' => $sensitive ? null : $message,
            'provider_reference' => $result->reference,
            'error' => $result->error,
            'response' => $result->response ?: null,
        ]);

        return $result;
    }
}
