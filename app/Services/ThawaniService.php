<?php

namespace App\Services;

use RuntimeException;
use App\Models\Booking;
use App\Models\BookingSetting;
use App\Models\PaymentGatewayLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ThawaniService
{
    private string $secretKey;
    private string $publishableKey;
    private string $baseUrl;
    private bool $testMode;

    public function __construct()
    {
        $this->secretKey      = (string) BookingSetting::get('thawani.secret_key', '');
        $this->publishableKey = (string) BookingSetting::get('thawani.publishable_key', '');
        $this->testMode       = (bool)   BookingSetting::get('thawani.test_mode', true);
        $this->baseUrl        = $this->testMode
            ? 'https://uatcheckout.thawani.om'
            : 'https://checkout.thawani.om';
    }

    /**
     * Create a Thawani checkout session.
     *
     * @param  array{
     *   client_reference_id: string,
     *   products: array<array{name: string, quantity: int, unit_amount: int}>,
     *   success_url: string,
     *   cancel_url: string,
     *   metadata?: array<string, mixed>
     * } $params
     * @return array<string, mixed>
     * @throws RuntimeException
     */
    public function createSession(array $params, ?Booking $booking = null): array
    {
        $response = Http::withHeaders([
            'thawani-api-key' => $this->secretKey,
            'Content-Type'    => 'application/json',
        ])->post("{$this->baseUrl}/api/v1/checkout/session", $params);

        PaymentGatewayLog::log(
            $booking,
            'thawani',
            'create_session',
            $params,
            $response->json() ?? ['raw' => $response->body()],
            $response->status(),
        );

        if (!$response->successful()) {
            Log::error('Thawani createSession failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new RuntimeException('Thawani payment gateway error: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Retrieve a Thawani checkout session by ID.
     *
     * @return array<string, mixed>
     * @throws RuntimeException
     */
    public function getSession(string $sessionId, ?Booking $booking = null): array
    {
        $response = Http::withHeaders([
            'thawani-api-key' => $this->secretKey,
        ])->get("{$this->baseUrl}/api/v1/checkout/session/{$sessionId}");

        PaymentGatewayLog::log(
            $booking,
            'thawani',
            'get_session',
            ['session_id' => $sessionId],
            $response->json() ?? ['raw' => $response->body()],
            $response->status(),
        );

        if (!$response->successful()) {
            Log::error('Thawani getSession failed', [
                'session_id' => $sessionId,
                'status'     => $response->status(),
                'body'       => $response->body(),
            ]);
            throw new RuntimeException('Thawani session fetch failed: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Build the hosted checkout redirect URL for a session.
     */
    public function getCheckoutUrl(string $sessionId): string
    {
        return "{$this->baseUrl}/pay/{$sessionId}?key={$this->publishableKey}";
    }

    /**
     * Convert an OMR amount to baisa (Thawani's unit_amount).
     * 1 OMR = 1000 baisa.
     */
    public function toBasisa(float $omr): int
    {
        return (int) round($omr * 1000);
    }

    public function isConfigured(): bool
    {
        return !empty($this->secretKey) && !empty($this->publishableKey);
    }

    public function getPublishableKey(): string
    {
        return $this->publishableKey;
    }

    public function isTestMode(): bool
    {
        return $this->testMode;
    }

    /**
     * Verify an incoming webhook HMAC-SHA256 signature.
     * Returns true if no secret is configured (open webhooks).
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $secret = (string) BookingSetting::get('thawani.webhook_secret', '');

        if (empty($secret)) {
            return true;
        }

        $expected = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expected, $signature);
    }
}
