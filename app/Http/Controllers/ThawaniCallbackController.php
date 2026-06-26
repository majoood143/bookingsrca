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
    // Thawani redirects here after the user completes / cancels payment.
    // Query params: ?session_id=<id>   (always present)
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

        // Webhook hasn't fired yet: verify with Thawani using the stored session id.
        $sessionId = $booking->payment_session_id;

        if (!$sessionId) {
            Log::warning('Thawani callback: missing session id on booking', ['reference' => $reference]);
            return redirect()->route('event.booking', $booking->event->slug)
                ->with('error', __('Payment session not found. Please contact support.'));
        }

        try {
            $response      = $this->thawani->getSession($sessionId, $booking);
            $paymentStatus = $response['data']['payment_status'] ?? null;

            if ($paymentStatus === 'paid') {
                $booking->update([
                    'payment_status'    => 'paid',
                    'payment_reference' => $response['data']['payment_ref'] ?? null,
                ]);

                $booking->confirm();

                return redirect()->route('booking.success', $booking->booking_reference);
            }

            $booking->update(['payment_status' => 'failed']);

            return redirect()
                ->route('event.booking', $booking->event->slug)
                ->with('error', __('Payment was not completed. Please try again.'));

        } catch (Exception $e) {
            Log::error('Thawani callback error', [
                'reference'  => $reference,
                'session_id' => $sessionId,
                'error'      => $e->getMessage(),
            ]);

            return redirect()
                ->route('event.booking', $booking->event->slug)
                ->with('error', __('An error occurred while verifying your payment. Please contact support.'));
        }
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

        if ($event === 'payment_completed' && $sessionId) {
            if ($booking && $booking->status !== 'confirmed') {
                $booking->update([
                    'payment_status'    => 'paid',
                    'payment_reference' => $data['data']['payment_ref'] ?? null,
                ]);

                $booking->confirm();
            }
        }

        return response()->json(['message' => 'ok']);
    }
}
