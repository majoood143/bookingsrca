<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Booking;
use App\Models\PaymentGatewayLog;
use App\Services\CCAvenueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CCAvenueCallbackController extends Controller
{
    public function __construct(private readonly CCAvenueService $ccavenue) {}

    // ──────────────────────────────────────────────────────────────────────────
    // POST /booking/payment/callback/ccavenue
    //
    // CCAvenue posts a single "encResp" field here after the user completes,
    // cancels, or fails payment (redirect_url and cancel_url both point at
    // this same route — there is no separate webhook). Decrypting encResp
    // yields a query string with order_id, order_status (success/failure/
    // aborted/invalid/initiated/unsuccessful), amount, tracking_id,
    // bank_ref_no, payment_type, failure_message, status_message.
    //
    // order_id is booking_reference with its hyphen stripped (CCAvenue/Bank
    // Muscat rejects non-alphanumeric order_ids), stored on payment_session_id
    // at initiate time — so the booking is looked up by that column rather
    // than booking_reference directly.
    //
    // CSRF is disabled for this route via bootstrap/app.php.
    // ──────────────────────────────────────────────────────────────────────────
    public function callback(Request $request)
    {
        $encResp = $request->input('encResp');

        if (!$encResp) {
            Log::warning('CCAvenue callback: missing encResp', $this->loggableRequest($request));
            return redirect('/')->with('error', __('Invalid payment callback.'));
        }

        try {
            $decrypted = $this->ccavenue->decrypt($encResp);
            parse_str($decrypted, $data);
        } catch (Exception $e) {
            Log::error('CCAvenue callback decryption error', ['error' => $e->getMessage()]);
            PaymentGatewayLog::log(null, 'ccavenue', 'callback', $this->loggableRequest($request), ['error' => $e->getMessage()]);
            return redirect('/')->with('error', __('An error occurred while verifying your payment. Please contact support.'));
        }

        $orderId = $data['order_id'] ?? null;
        $status  = strtolower((string) ($data['order_status'] ?? ''));
        $amount  = $data['amount'] ?? null;

        $booking = $orderId ? Booking::where('payment_session_id', $orderId)->first() : null;

        if (!$booking) {
            Log::warning('CCAvenue callback: no booking found for order_id', ['order_id' => $orderId]);
            PaymentGatewayLog::log(null, 'ccavenue', 'callback', $this->loggableRequest($request), $data);
            return redirect('/')->with('error', __('Booking not found.'));
        }

        // Already confirmed by a previous callback attempt
        if ($booking->status === 'confirmed') {
            return redirect()->route('booking.success', $booking->booking_reference);
        }

        PaymentGatewayLog::log($booking, 'ccavenue', 'callback', $this->loggableRequest($request), $data);

        // Security check: never trust a "success" status unless the amount matches.
        $amountMatches = $amount !== null
            && round((float) $amount, 2) === round((float) $booking->total_price, 2);

        if ($status === 'success' && $amountMatches) {
            $reference = $data['tracking_id'] ?? $data['bank_ref_no'] ?? null;

            $booking->update([
                'payment_status'    => 'paid',
                'payment_reference' => $reference,
            ]);

            $booking->payments()->firstOrCreate(
                ['payment_method' => 'ccavenue'],
                [
                    'amount'    => $booking->total_price,
                    'reference' => $reference,
                    'notes'     => 'Online payment via CCAvenue (Bank Muscat).',
                ]
            );

            $booking->confirm();

            return redirect()->route('booking.success', $booking->booking_reference);
        }

        if ($status === 'success' && !$amountMatches) {
            Log::warning('CCAvenue callback: amount mismatch, treating as failed', [
                'booking_id' => $booking->id,
                'expected'   => $booking->total_price,
                'received'   => $amount,
            ]);
        }

        $booking->update(['payment_status' => 'failed']);

        if ($status === 'aborted') {
            $booking->cancel();
        }

        $message = $data['failure_message'] ?? $data['status_message'] ?? __('Payment was not completed. Please try again.');

        return redirect()
            ->route('event.booking', $booking->event->slug)
            ->with('error', $message);
    }

    /**
     * Build a safe snapshot of the inbound request for logging.
     */
    private function loggableRequest(Request $request): array
    {
        return [
            'content_type' => $request->header('Content-Type'),
            'all'          => $request->all(),
        ];
    }
}
