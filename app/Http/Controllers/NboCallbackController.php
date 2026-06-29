<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Booking;
use App\Models\PaymentGatewayLog;
use App\Services\NboService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NboCallbackController extends Controller
{
    public function __construct(private readonly NboService $nbo) {}

    // ──────────────────────────────────────────────────────────────────────────
    // POST /booking/payment/callback/nbo
    // NBO POSTs here after the user completes / cancels payment.
    //
    // The integration guide describes this as { paymentId, trandata, error,
    // errorText } with an encrypted "trandata" blob to decrypt. In practice
    // (confirmed from production payloads) NBO actually posts a flat,
    // *unencrypted* form body with inconsistent casing - e.g.
    // "paymentid" (lowercase), "Error"/"ErrorText" (capitalized), and no
    // "trandata" at all - carrying the transaction fields directly:
    // result, tranid, ref, amt, authRespCode, udf1-15, plus 3-D Secure
    // fields (threeDSServerTranID, dsTranID, acsTranID).
    //
    // normalizedInput() below case-folds every key so both the documented
    // shape and the real one are read correctly, and resolveTranData()
    // builds the same associative shape NboService::decrypt() would have
    // produced, whether or not an encrypted "trandata" was actually sent.
    //
    // CSRF is disabled for this route via bootstrap/app.php.
    // ──────────────────────────────────────────────────────────────────────────
    public function callback(Request $request)
    {
        $input = $this->normalizedInput($request);

        $paymentId = $this->pick($input, 'paymentid', 'paymentId');
        $error     = $this->pick($input, 'error');
        $errorText = $this->pick($input, 'errortext', 'errorText');

        $booking = $paymentId
            ? Booking::where('payment_session_id', $paymentId)->first()
            : null;

        // Already confirmed by a previous callback attempt
        if ($booking && $booking->status === 'confirmed') {
            return redirect()->route('booking.success', $booking->booking_reference);
        }

        // Explicit error from NBO (user cancelled, declined card, gateway validation error, etc.)
        if ($error && $error !== '0') {
            $errorText = $errorText ?: __('Payment was not completed.');
            Log::warning('NBO callback error response', [
                'error'      => $error,
                'error_text' => $errorText,
                'payment_id' => $paymentId,
            ]);

            PaymentGatewayLog::log($booking, 'nbo', 'callback', $this->loggableRequest($request), ['error' => $error, 'errorText' => $errorText]);

            if ($booking) {
                $booking->update(['payment_status' => 'failed']);
                return redirect()
                    ->route('event.booking', $booking->event->slug)
                    ->with('error', $errorText);
            }

            return redirect('/')->with('error', $errorText);
        }

        if (!$paymentId) {
            Log::warning('NBO callback: missing paymentId', $this->loggableRequest($request));
            return redirect('/')->with('error', __('Invalid payment callback.'));
        }

        if (!$booking) {
            Log::warning('NBO callback: no booking found for paymentId', ['payment_id' => $paymentId]);
            return redirect('/')->with('error', __('Booking not found.'));
        }

        try {
            $data = $this->resolveTranData($input);

            if ($data === null) {
                Log::warning('NBO callback: no trandata or transaction fields present', [
                    'payment_id' => $paymentId,
                    ...$this->loggableRequest($request),
                ]);

                $booking->update(['payment_status' => 'failed']);

                PaymentGatewayLog::log($booking, 'nbo', 'callback', $this->loggableRequest($request), ['error' => 'no_transaction_data']);

                return redirect()
                    ->route('event.booking', $booking->event->slug)
                    ->with('error', __('An error occurred while verifying your payment. Please contact support.'));
            }

            $result = $data['result'] ?? null;

            Log::info('NBO callback resolved', [
                'payment_id' => $paymentId,
                'result'     => $result,
                'track_id'   => $data['trackId'] ?? null,
            ]);

            PaymentGatewayLog::log($booking, 'nbo', 'callback', $this->loggableRequest($request), $data);

            if ($result === 'CAPTURED' || $result === 'APPROVED') {
                $booking->update([
                    'payment_status'    => 'paid',
                    'payment_reference' => $paymentId,
                ]);

                $booking->confirm();

                return redirect()->route('booking.success', $booking->booking_reference);
            }

            $booking->update(['payment_status' => 'failed']);

            if ($result === 'CANCELED' || $result === 'CANCELLED') {
                $booking->cancel();
            }

            return redirect()
                ->route('event.booking', $booking->event->slug)
                ->with('error', __('Payment was not completed. Please try again.'));

        } catch (Exception $e) {
            Log::error('NBO callback decryption error', [
                'payment_id' => $paymentId,
                'error'      => $e->getMessage(),
            ]);

            PaymentGatewayLog::log($booking, 'nbo', 'callback', $this->loggableRequest($request), ['error' => $e->getMessage()]);

            return redirect()
                ->route('event.booking', $booking->event->slug)
                ->with('error', __('An error occurred while verifying your payment. Please contact support.'));
        }
    }

    /**
     * Build the same associative shape NboService::decrypt() would produce,
     * either by decrypting an encrypted "trandata" blob (documented shape)
     * or by reading the flat plaintext fields NBO actually sends. Returns
     * null when neither is present.
     */
    private function resolveTranData(array $input): ?array
    {
        $trandata = $this->pick($input, 'trandata');

        if ($trandata) {
            return $this->nbo->decrypt($trandata);
        }

        $result = $this->pick($input, 'result');

        if (!$result) {
            return null;
        }

        $data = [
            'result'        => $result,
            'tranId'        => $this->pick($input, 'tranid', 'tranId'),
            'ref'           => $this->pick($input, 'ref'),
            'amt'           => $this->pick($input, 'amt'),
            'authRespCode'  => $this->pick($input, 'authrespcode', 'authRespCode'),
            'authCode'      => $this->pick($input, 'authcode', 'authCode', 'auth'),
            'cardNo'        => $this->pick($input, 'cardno', 'cardNo'),
            'cardType'      => $this->pick($input, 'cardtype', 'cardType'),
            'trackId'       => $this->pick($input, 'trackid', 'trackId'),
            'respDateTime'  => $this->pick($input, 'respdatetime', 'respDateTime', 'postdate'),
        ];

        foreach (range(1, 5) as $n) {
            $data["udf{$n}"] = $this->pick($input, "udf{$n}");
        }

        return $data;
    }

    /**
     * Case-fold every key in the request (form fields, query string, and -
     * as a last resort - a raw JSON body, possibly array-wrapped like every
     * other payload in this integration) so lookups are immune to NBO's
     * inconsistent casing ("paymentid" vs "paymentId", "Error" vs "error", etc).
     */
    private function normalizedInput(Request $request): array
    {
        $all = $request->all();

        if (empty($all)) {
            $raw     = trim((string) $request->getContent());
            $decoded = $raw !== '' ? json_decode($raw, true) : null;

            if (is_array($decoded)) {
                $all = (isset($decoded[0]) && is_array($decoded[0])) ? $decoded[0] : $decoded;
            }
        }

        return collect($all)
            ->mapWithKeys(fn ($value, $key) => [strtolower((string) $key) => $value])
            ->all();
    }

    /**
     * Fetch the first non-empty value among the given (case-insensitive) keys.
     */
    private function pick(array $normalizedInput, string ...$keys): ?string
    {
        foreach ($keys as $key) {
            $value = $normalizedInput[strtolower($key)] ?? null;

            if ($value !== null && $value !== '') {
                return (string) $value;
            }
        }

        return null;
    }

    /**
     * Build a safe snapshot of the inbound request for PaymentGatewayLog /
     * Log::warning, since $request->all() can be empty whenever the payload
     * had to be recovered from the raw body in normalizedInput().
     */
    private function loggableRequest(Request $request): array
    {
        return [
            'content_type' => $request->header('Content-Type'),
            'all'          => $request->all(),
            'raw'          => $request->getContent(),
        ];
    }
}
