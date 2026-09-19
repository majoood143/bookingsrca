<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Booking;
use App\Models\PaymentGatewayLog;
use App\Services\ThawaniService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ThawaniCallbackController extends Controller
{
    public function __construct(private readonly ThawaniService $thawani) {}

    // ──────────────────────────────────────────────────────────────────────────
    // GET /booking/payment/callback
    // Thawani redirects here after the user completes payment.
    // Query params: ?reference=<booking_reference>
    // ──────────────────────────────────────────────────────────────────────────
    public function callback(Request $request)
    {
        $reference = $request->query('reference');

        if (!$reference) {
            return redirect('/')->with('error', __('Invalid payment callback.'));
        }

        $booking = Booking::where('booking_reference', $reference)->first();

        if (!$booking) {
            Log::warning('Thawani callback: no booking found for reference', ['reference' => $reference]);
            return redirect('/')->with('error', __('Booking not found.'));
        }

        // Webhook already confirmed the booking — just send the user to success.
        if ($booking->status === 'confirmed') {
            return redirect()->route('booking.success', $booking->booking_reference);
        }

        if (!$booking->payment_session_id) {
            Log::warning('Thawani callback: missing session id on booking', ['reference' => $reference]);
            return redirect()->route('event.booking', $booking->event->slug)
                ->with('error', __('Payment session not found. Please contact support.'));
        }

        try {
            $paymentStatus = $this->thawani->reconcileBooking($booking);

            // Check the booking's own status rather than $paymentStatus alone.
            // reconcileBooking() confirms a pending booking, and also revives one
            // the expiry sweep cancelled while the customer was still on the
            // hosted page (the Thawani session outlives our hold), so a paid
            // session normally ends up confirmed here.
            if ($booking->status === 'confirmed') {
                return redirect()->route('booking.success', $booking->booking_reference);
            }

            // Paid, but the booking couldn't be revived (its seats were taken in
            // the meantime, or an admin had cancelled it). Say so plainly — a
            // vague "try again" would send the customer off to pay a second time.
            if ($paymentStatus === 'paid') {
                return redirect()
                    ->route('event.booking', $booking->event->slug)
                    ->with('error', __('Your payment was received, but we could not confirm this booking because it is no longer available. Please contact support — do not pay again.'));
            }

            // Thawani reports the session itself as cancelled (user clicked "Cancel"
            // on the hosted page but landed on the success URL, or the cancel_url
            // redirect was intercepted) — release the hold immediately rather than
            // waiting for the pending-booking expiry sweep.
            if ($paymentStatus === 'cancelled') {
                if ($booking->status === 'pending') {
                    $booking->update(['payment_status' => 'failed']);
                    $booking->cancel('gateway');
                }

                return redirect()
                    ->route('event.booking', $booking->event->slug)
                    ->with('error', __('Payment was cancelled. Please try again.'));
            }

            // Still unpaid: a failed card attempt, not a cancellation. Leave the
            // booking pending — the session stays payable, and the expiry sweep
            // decides when to give up on it.
            return redirect()
                ->route('event.booking', $booking->event->slug)
                ->with('error', __('Payment was not completed. Please try again.'));

        } catch (Exception $e) {
            Log::error('Thawani callback error', [
                'reference'  => $reference,
                'session_id' => $booking->payment_session_id,
                'error'      => $e->getMessage(),
            ]);

            return redirect()
                ->route('event.booking', $booking->event->slug)
                ->with('error', __('An error occurred while verifying your payment. Please contact support.'));
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GET /booking/payment/cancel
    // Thawani redirects here when the user cancels on the hosted checkout page.
    // Query params: ?reference=<booking_reference>
    // ──────────────────────────────────────────────────────────────────────────
    public function cancel(Request $request)
    {
        $reference = $request->query('reference');

        if (!$reference) {
            return redirect('/')->with('error', __('Invalid payment callback.'));
        }

        $booking = Booking::where('booking_reference', $reference)->first();

        if (!$booking) {
            Log::warning('Thawani cancel: no booking found for reference', ['reference' => $reference]);
            return redirect('/')->with('error', __('Booking not found.'));
        }

        // A webhook may have confirmed the booking in the moment between the
        // user cancelling and this redirect landing — don't clobber a paid booking.
        if ($booking->status === 'confirmed') {
            return redirect()->route('booking.success', $booking->booking_reference);
        }

        PaymentGatewayLog::log($booking, 'thawani', 'cancel', ['session_id' => $booking->payment_session_id], ['reason' => 'user_cancelled']);

        if ($booking->status === 'pending') {
            // A hit on cancel_url is only a redirect — the hosted session stays
            // payable, so ask Thawani before giving up on the booking. If it
            // turns out to be paid, reconcileBooking() confirms it.
            try {
                $this->thawani->reconcileBooking($booking);
            } catch (Exception $e) {
                Log::warning('Thawani cancel: session check failed, cancelling anyway', [
                    'reference' => $reference,
                    'error'     => $e->getMessage(),
                ]);
            }

            if ($booking->status === 'confirmed') {
                return redirect()->route('booking.success', $booking->booking_reference);
            }

            $booking->update(['payment_status' => 'failed']);
            $booking->cancel('customer');
        }

        return redirect()
            ->route('event.booking', $booking->event->slug)
            ->with('error', __('Payment was cancelled. Please try again.'));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // POST /booking/payment/webhook/thawani
    // Thawani server-to-server event notification (async backup).
    // CSRF is disabled for this route via bootstrap/app.php.
    // ──────────────────────────────────────────────────────────────────────────
    public function webhook(Request $request)
    {
        $payload   = $request->getContent();
        $signature = $request->header('thawani-signature', '');

        if (!$this->thawani->verifyWebhookSignature($payload, $signature)) {
            Log::warning('Thawani webhook: invalid signature');
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $data      = json_decode($payload, true);
        $event     = $data['event_type']        ?? null;
        $sessionId = $data['data']['session_id'] ?? null;

        Log::info('Thawani webhook received', ['event' => $event, 'session_id' => $sessionId]);

        $booking = $sessionId ? Booking::where('payment_session_id', $sessionId)->first() : null;

        PaymentGatewayLog::log($booking, 'thawani', 'webhook', $data, ['event_type' => $event, 'session_id' => $sessionId]);

        if (!$booking || $booking->status === 'confirmed') {
            return response()->json(['message' => 'ok']);
        }

        // Don't trust the event name or payload alone (Thawani's full set of
        // webhook event names isn't reliably documented, and signature
        // verification is skipped entirely when no webhook secret is
        // configured) — resync against the session itself via our own
        // authenticated API call and act on its actual payment_status.
        // reconcileBooking() also revives a booking the expiry sweep already
        // cancelled if its session turns out to be paid.
        try {
            $paymentStatus = $this->thawani->reconcileBooking($booking);

            if ($paymentStatus === 'cancelled' && $booking->status === 'pending') {
                $booking->update(['payment_status' => 'failed']);
                $booking->cancel('gateway');
            }
        } catch (Exception $e) {
            Log::error('Thawani webhook resync failed', [
                'session_id' => $sessionId,
                'error'      => $e->getMessage(),
            ]);
        }

        return response()->json(['message' => 'ok']);
    }
}
