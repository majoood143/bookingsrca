<?php

namespace App\Services\Messaging;

use App\Services\Sms\SmsManager;
use App\Services\WhatsApp\WhatsAppNotifier;
use App\Support\MessagingSettings;
use App\Support\PhoneNumber;

/**
 * Sends a text to a customer's phone the way General Settings → Messaging says: WhatsApp first
 * when it is on and a number is connected, then SMS when the fallback is on. Every try is logged
 * (message_logs); nothing here throws.
 */
class CustomerMessenger
{
    public function __construct(private readonly SmsManager $sms) {}

    /**
     * Built only when needed: the Evolution client cannot even be constructed without its .env
     * settings, and the SMS path must keep working on a server that has none.
     */
    private function whatsapp(): ?WhatsAppNotifier
    {
        return rescue(fn () => app(WhatsAppNotifier::class), null, false);
    }

    /** Whether a message can go by WhatsApp right now. Any WhatsApp trouble only means "no". */
    public function whatsappAvailable(): bool
    {
        return MessagingSettings::whatsappEnabled()
            && (bool) rescue(fn () => ($notifier = $this->whatsapp()) && $notifier->isConfigured() && $notifier->instance() !== null, false, false);
    }

    public function smsAvailable(): bool
    {
        return MessagingSettings::smsFallbackEnabled() && $this->sms->canSend();
    }

    public function canSend(): bool
    {
        return $this->whatsappAvailable() || $this->smsAvailable();
    }

    /** Whether SMS goes through the demo driver (nothing sent; callers may show the text). */
    public function isDemo(): bool
    {
        return ! $this->whatsappAvailable() && $this->smsAvailable() && $this->sms->isDemo();
    }

    /**
     * @param  string  $type  what the message is for (otp, game_award, test...), for the log
     * @param  bool  $sensitive  keep the text out of the logs (one-time codes)
     */
    public function send(string $to, string $message, string $type, bool $sensitive = false): MessageResult
    {
        $phone = PhoneNumber::normalize($to);

        if ($phone === null) {
            return MessageResult::failed('Invalid phone number.');
        }

        $errors = [];

        if ($this->whatsappAvailable()) {
            $log = $this->whatsapp()?->send($phone, $message, $type, $sensitive);

            if ($log?->succeeded()) {
                return new MessageResult(true, MessageResult::WHATSAPP);
            }

            $errors[] = $log?->error ?? 'WhatsApp could not send.';
        }

        if ($this->smsAvailable()) {
            $result = $this->sms->send($phone, $message, $type, $sensitive);

            if ($result->ok) {
                return new MessageResult(true, MessageResult::SMS, null, $this->sms->isDemo());
            }

            $errors[] = $result->error;
        }

        return MessageResult::failed($errors === [] ? 'No messaging channel is set up (General Settings → Messaging).' : implode(' ', array_filter($errors)));
    }
}
