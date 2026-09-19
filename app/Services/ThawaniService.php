<?php

namespace App\Services;

use RuntimeException;
use App\Models\Booking;
use App\Models\BookingSetting;
use App\Models\PaymentGatewayLog;
use App\Models\PosOrder;
use App\Models\PosOrderGatewayLog;
use Illuminate\Support\Facades\DB;
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

    // What applying a "paid" Thawani session to a booking will do. Kept as named
    // outcomes so the repair command can report them before applying anything.
    public const ACTION_NONE            = 'none';
    public const ACTION_CONFIRM         = 'confirm';          // pending -> confirmed
    public const ACTION_RECOVER         = 'recover';          // cancelled by the system -> confirmed
    public const ACTION_NO_CAPACITY     = 'no_capacity';      // paid, but the seats went to someone else
    public const ACTION_ADMIN_CANCELLED = 'admin_cancelled';  // paid, but an admin cancelled it on purpose
    public const ACTION_COMPLETE_MANUAL = 'complete_manual';  // status edited to confirmed by hand, side effects skipped

    /**
     * Re-fetch a booking's Thawani session and, if it shows paid, settle the
     * booking against it. Used by every path that needs to settle a booking
     * against the gateway's own record rather than trusting a caller-supplied
     * status (browser callback, webhook, expiry sweep, late-payment recovery).
     *
     * A Thawani session stays payable for 24 hours, long after our own pending
     * hold has expired, so a "paid" answer for a booking we already cancelled is
     * expected and is honoured — see decidePaidAction() for when.
     *
     * @return string|null the session's payment_status ('paid'/'unpaid'/'cancelled'), or null if the booking has no session id
     */
    public function reconcileBooking(Booking $booking): ?string
    {
        if (!$booking->payment_session_id) {
            return null;
        }

        $response      = $this->getSession($booking->payment_session_id, $booking);
        $data          = $response['data'] ?? [];
        $paymentStatus = $data['payment_status'] ?? null;

        if ($paymentStatus === 'paid') {
            $this->applyPaidSession($booking, $data);
        }

        return $paymentStatus;
    }

    /**
     * Decide what a "paid" session means for the booking in its current state.
     */
    public function decidePaidAction(Booking $booking): string
    {
        return match ($booking->status) {
            'pending'   => self::ACTION_CONFIRM,
            'confirmed' => $booking->confirmed_at === null ? self::ACTION_COMPLETE_MANUAL : self::ACTION_NONE,
            'cancelled' => match (true) {
                // Already flagged (or settled) by an earlier pass — don't re-alert on every sweep.
                $booking->payment_status === 'paid' => self::ACTION_NONE,
                // Cancelled before cancellation_source existed: leave it exactly as it is.
                $booking->cancellation_source === null => self::ACTION_NONE,
                !$booking->isSystemCancelled()      => self::ACTION_ADMIN_CANCELLED,
                $this->hasCapacityFor($booking)     => self::ACTION_RECOVER,
                default                             => self::ACTION_NO_CAPACITY,
            },
            default => self::ACTION_NONE, // checked_in / refunded
        };
    }

    /**
     * Apply a paid session to the booking under a row lock, so the browser
     * callback, webhook and sweeps can't confirm (and email) it twice.
     *
     * @param  array<string, mixed>  $sessionData  the `data` node of a Thawani session response
     * @param  bool  $notify  false to confirm without emailing the customer
     * @return string one of the ACTION_* constants
     */
    public function applyPaidSession(Booking $booking, array $sessionData, bool $notify = true): string
    {
        // The session response carries no `payment_ref`; `invoice` is Thawani's
        // reference for the payment and is what shows up on their dashboard.
        $reference = $sessionData['payment_ref'] ?? $sessionData['invoice'] ?? null;

        $action = DB::transaction(function () use ($booking, $reference, $notify) {
            $locked = Booking::with(['timeSlot', 'ticketType', 'extraServices'])
                ->whereKey($booking->id)
                ->lockForUpdate()
                ->first();

            if (!$locked) {
                return self::ACTION_NONE;
            }

            $action = $this->decidePaidAction($locked);

            switch ($action) {
                case self::ACTION_CONFIRM:
                    $locked->update(['payment_status' => 'paid', 'payment_reference' => $reference]);
                    $this->recordPayment($locked, $reference);
                    $locked->confirm($notify);
                    break;

                case self::ACTION_RECOVER:
                    $locked->update([
                        'cancelled_at'        => null,
                        'cancellation_source' => null,
                        'payment_status'      => 'paid',
                        'payment_reference'   => $reference,
                    ]);
                    $this->recordPayment($locked, $reference);
                    $locked->confirm($notify);

                    PaymentGatewayLog::log($locked, 'thawani', 'late_payment_recovered', ['session_id' => $locked->payment_session_id], ['reference' => $reference]);
                    Log::info('Thawani late payment recovered a cancelled booking', [
                        'booking_id'        => $locked->id,
                        'booking_reference' => $locked->booking_reference,
                    ]);
                    break;

                case self::ACTION_NO_CAPACITY:
                case self::ACTION_ADMIN_CANCELLED:
                    // The money is in, but the booking can't be honoured. Mark it paid
                    // (still cancelled) so it shows up as "paid but cancelled" for a
                    // manual refund, and so later sweeps skip it.
                    $locked->update(['payment_status' => 'paid', 'payment_reference' => $reference]);

                    PaymentGatewayLog::log($locked, 'thawani', 'paid_needs_manual_action', ['session_id' => $locked->payment_session_id], ['reason' => $action, 'reference' => $reference]);
                    Log::critical('Thawani payment captured for a cancelled booking that could not be revived — needs refund or manual confirmation', [
                        'booking_id'        => $locked->id,
                        'booking_reference' => $locked->booking_reference,
                        'reason'            => $action,
                        'session_id'        => $locked->payment_session_id,
                        'payment_ref'       => $reference,
                    ]);
                    break;

                case self::ACTION_COMPLETE_MANUAL:
                    // An admin flipped the status to "confirmed" by hand: no confirmation
                    // timestamp, payment record, capacity counters or promo usage. Fill
                    // those in. The timestamp is the row's last touch — the closest
                    // record of when it was fixed. Notifications are left to the caller.
                    $locked->update([
                        'confirmed_at'        => $locked->updated_at ?? now(),
                        'cancelled_at'        => null,
                        'cancellation_source' => null,
                        'payment_status'      => 'paid',
                        'payment_reference'   => $locked->payment_reference ?? $reference,
                    ]);
                    $this->recordPayment($locked, $reference);
                    $locked->applyConfirmationCounters();
                    break;
            }

            return $action;
        });

        $booking->refresh();

        return $action;
    }

    private function recordPayment(Booking $booking, ?string $reference): void
    {
        $booking->payments()->firstOrCreate(
            ['payment_method' => 'thawani'],
            [
                'amount'    => $booking->total_price,
                'reference' => $reference,
                'notes'     => 'Online payment via Thawani.',
            ]
        );
    }

    // Whether the seats a cancelled booking used to hold are still free — a
    // cancelled booking holds nothing, so anything sold in the meantime counts.
    private function hasCapacityFor(Booking $booking): bool
    {
        if ($booking->timeSlot->getRemainingCapacity() < $booking->quantity) {
            return false;
        }

        if ($booking->ticketType->getRemainingQuantity() < $booking->quantity) {
            return false;
        }

        foreach ($booking->extraServices as $service) {
            if ($service->getRemainingQuantity() < $service->pivot->quantity) {
                return false;
            }
        }

        return true;
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
