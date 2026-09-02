<?php

namespace App\Services;

use RuntimeException;
use App\Models\Booking;
use App\Models\BookingSetting;
use App\Models\PaymentGatewayLog;
use App\Models\PosOrder;
use App\Models\PosOrderGatewayLog;
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

        $override      = rtrim((string) BookingSetting::get('thawani.base_url', ''), '/');
        $this->baseUrl = $override !== '' ? $override : ($this->testMode
            ? 'https://uatcheckout.thawani.om/api/v1'
            : 'https://checkout.thawani.om/api/v1');
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
    public function createSession(array $params, Booking|PosOrder|null $payable = null): array
    {
        $response = Http::withHeaders([
            'thawani-api-key' => $this->secretKey,
            'Content-Type'    => 'application/json',
        ])->post("{$this->baseUrl}/checkout/session", $params);

        $this->logGatewayCall($payable, 'create_session', $params, $response->json() ?? ['raw' => $response->body()], $response->status());

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
    public function getSession(string $sessionId, Booking|PosOrder|null $payable = null): array
    {
        $response = Http::withHeaders([
            'thawani-api-key' => $this->secretKey,
        ])->get("{$this->baseUrl}/checkout/session/{$sessionId}");

        $this->logGatewayCall($payable, 'get_session', ['session_id' => $sessionId], $response->json() ?? ['raw' => $response->body()], $response->status());

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
     * The hosted pay page lives on the bare checkout host, not under /api/v1.
     */
    public function getCheckoutUrl(string $sessionId): string
    {
        $host = preg_replace('#/api/v1/?$#', '', $this->baseUrl);

        return "{$host}/pay/{$sessionId}?key={$this->publishableKey}";
    }

    /**
     * Re-fetch a booking's Thawani session and, if it shows paid, apply the
     * paid/confirmed state. Used by every path that needs to settle a booking
     * against the gateway's own record rather than trusting a caller-supplied
     * status (browser callback, webhook, and the pending-booking expiry sweep).
     *
     * @return string|null the session's payment_status ('paid'/'unpaid'/'cancelled'), or null if the booking has no session id
     */
    public function reconcileBooking(Booking $booking): ?string
    {
        if (!$booking->payment_session_id) {
            return null;
        }

        $response      = $this->getSession($booking->payment_session_id, $booking);
        $paymentStatus = $response['data']['payment_status'] ?? null;

        if ($paymentStatus === 'paid' && $booking->status === 'pending') {
            $reference = $response['data']['payment_ref'] ?? null;

            $booking->update([
                'payment_status'    => 'paid',
                'payment_reference' => $reference,
            ]);

            $booking->payments()->firstOrCreate(
                ['payment_method' => 'thawani'],
                [
                    'amount'    => $booking->total_price,
                    'reference' => $reference,
                    'notes'     => 'Online payment via Thawani.',
                ]
            );

            $booking->confirm();
        } elseif ($paymentStatus === 'paid' && $booking->status !== 'confirmed') {
            // Thawani captured payment on a session whose booking is no longer
            // pending — most likely the expiry sweep already cancelled it while
            // the customer's checkout tab stayed open and they paid anyway.
            // Don't silently resurrect it: its slot/ticket capacity may already
            // be held by someone else. Surface this loudly for manual
            // reconciliation/refund instead.
            Log::critical('Thawani payment captured for a non-pending booking — needs manual reconciliation', [
                'booking_id'        => $booking->id,
                'booking_reference' => $booking->booking_reference,
                'booking_status'    => $booking->status,
                'session_id'        => $booking->payment_session_id,
                'payment_ref'       => $response['data']['payment_ref'] ?? null,
            ]);
        }

        return $paymentStatus;
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

    // Routes the log entry to the right table depending on which kind of
    // payable initiated the call — event bookings and POS shop orders keep
    // separate gateway-log tables (see PosOrderGatewayLog) so this session
    // creation/lookup logic can stay shared between both flows.
    private function logGatewayCall(Booking|PosOrder|null $payable, string $event, mixed $request, mixed $response, ?int $statusCode): void
    {
        if ($payable instanceof PosOrder) {
            PosOrderGatewayLog::log($payable, 'thawani', $event, $request, $response, $statusCode);

            return;
        }

        PaymentGatewayLog::log($payable, 'thawani', $event, $request, $response, $statusCode);
    }
}
