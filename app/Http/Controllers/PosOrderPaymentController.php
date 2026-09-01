<?php

namespace App\Http\Controllers;

use App\Models\PosOrder;
use App\Models\PosOrderGatewayLog;
use App\Services\CCAvenueService;
use App\Services\ThawaniService;
use App\Support\ShopCart;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Gateway callbacks for the POS online shop's checkout, mirroring the three
 * event-booking payment controllers (ThawaniCallbackController,
 * NboCallbackController, CCAvenueCallbackController) but settling a PosOrder
 * instead of a Booking. Consolidated into one controller since all three
 * gateways serve the same single checkout flow here.
 */
class PosOrderPaymentController extends Controller
{
    public function __construct(
        private readonly ThawaniService $thawani,
        private readonly CCAvenueService $ccavenue,
    ) {
    }

    // GET /shop/payment/callback — Thawani redirects here after checkout.
    public function thawaniCallback(Request $request)
    {
        $orderNumber = $request->query('reference');
        $order = $orderNumber ? PosOrder::where('order_number', $orderNumber)->first() : null;

        if (!$order) {
            return redirect()->route('shop.checkout')->with('error', __('pos.order.payment.not_found'));
        }

        if ($order->isPaid()) {
            return redirect()->route('shop.order.success', $order->order_number);
        }

        if (!$order->payment_reference) {
            return redirect()->route('shop.checkout')->with('error', __('pos.order.payment.session_missing'));
        }

        try {
            $response = $this->thawani->getSession($order->payment_reference, $order);
            $paymentStatus = $response['data']['payment_status'] ?? null;

            if ($paymentStatus === 'paid') {
                $reference = $response['data']['payment_ref'] ?? null;
                $order->markPaid('thawani', $reference);
                ShopCart::clear();

                return redirect()->route('shop.order.success', $order->order_number);
            }

            $order->update(['status' => 'failed']);

            return redirect()->route('shop.checkout')->with('error', __('pos.order.payment.not_completed'));
        } catch (Exception $e) {
            Log::error('POS shop Thawani callback error', ['order' => $order->order_number, 'error' => $e->getMessage()]);

            return redirect()->route('shop.checkout')->with('error', __('pos.order.payment.verification_error'));
        }
    }

    // POST /shop/payment/webhook/thawani — server-to-server backup (CSRF excluded).
    public function thawaniWebhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('thawani-signature', '');

        if (!$this->thawani->verifyWebhookSignature($payload, $signature)) {
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $data = json_decode($payload, true);
        $event = $data['event_type'] ?? null;
        $sessionId = $data['data']['session_id'] ?? null;

        $order = $sessionId ? PosOrder::where('payment_reference', $sessionId)->first() : null;

        PosOrderGatewayLog::log($order, 'thawani', 'webhook', $data, ['event_type' => $event, 'session_id' => $sessionId]);

        if ($event === 'payment_completed' && $order && !$order->isPaid()) {
            $reference = $data['data']['payment_ref'] ?? null;
            $order->markPaid('thawani', $reference);
            ShopCart::clear();
        }

        return response()->json(['message' => 'ok']);
    }

    // POST /shop/payment/callback/nbo (CSRF excluded).
    public function nboCallback(Request $request)
    {
        $input = collect($request->all())->mapWithKeys(fn ($value, $key) => [strtolower((string) $key) => $value])->all();

        $paymentId = $input['paymentid'] ?? null;
        $error = $input['error'] ?? null;

        $order = $paymentId ? PosOrder::where('payment_reference', $paymentId)->first() : null;

        if ($order && $order->isPaid()) {
            return redirect()->route('shop.order.success', $order->order_number);
        }

        if (!$order) {
            return redirect()->route('shop.checkout')->with('error', __('pos.order.payment.not_found'));
        }

        if ($error && $error !== '0') {
            $order->update(['status' => 'failed']);
            PosOrderGatewayLog::log($order, 'nbo', 'callback', $input, ['error' => $error]);

            return redirect()->route('shop.checkout')->with('error', __('pos.order.payment.not_completed'));
        }

        $result = $input['result'] ?? null;
        PosOrderGatewayLog::log($order, 'nbo', 'callback', $input, ['result' => $result]);

        if ($result === 'CAPTURED' || $result === 'APPROVED') {
            $order->markPaid('nbo', $paymentId);
            ShopCart::clear();

            return redirect()->route('shop.order.success', $order->order_number);
        }

        $order->update(['status' => 'failed']);

        return redirect()->route('shop.checkout')->with('error', __('pos.order.payment.not_completed'));
    }

    // GET /shop/payment/ccavenue/{order} — full-page auto-submit form (mirrors CCAvenueRedirectController).
    public function ccavenueRedirect(string $orderNumber)
    {
        $order = PosOrder::where('order_number', $orderNumber)->firstOrFail();

        $payload = $this->ccavenue->buildOrderRedirectPayload($order);

        return view('payments.pos-order-ccavenue-redirect', $payload);
    }

    // POST /shop/payment/callback/ccavenue (CSRF excluded).
    public function ccavenueCallback(Request $request)
    {
        $encResp = $request->input('encResp');

        if (!$encResp) {
            return redirect()->route('shop.checkout')->with('error', __('pos.order.payment.not_found'));
        }

        try {
            $decrypted = $this->ccavenue->decrypt($encResp);
            parse_str($decrypted, $data);
        } catch (Exception $e) {
            Log::error('POS shop CCAvenue callback decryption error', ['error' => $e->getMessage()]);

            return redirect()->route('shop.checkout')->with('error', __('pos.order.payment.verification_error'));
        }

        $orderId = $data['order_id'] ?? null;
        $status = strtolower((string) ($data['order_status'] ?? ''));
        $amount = $data['amount'] ?? null;

        $order = $orderId ? PosOrder::where('payment_reference', $orderId)->first() : null;

        if (!$order) {
            return redirect()->route('shop.checkout')->with('error', __('pos.order.payment.not_found'));
        }

        if ($order->isPaid()) {
            return redirect()->route('shop.order.success', $order->order_number);
        }

        PosOrderGatewayLog::log($order, 'ccavenue', 'callback', $data);

        $amountMatches = $amount !== null && round((float) $amount, 2) === round((float) $order->grand_total, 2);

        if ($status === 'success' && $amountMatches) {
            $reference = $data['tracking_id'] ?? $data['bank_ref_no'] ?? null;
            $order->markPaid('ccavenue', $reference);
            ShopCart::clear();

            return redirect()->route('shop.order.success', $order->order_number);
        }

        $order->update(['status' => 'failed']);

        $message = $data['failure_message'] ?? $data['status_message'] ?? __('pos.order.payment.not_completed');

        return redirect()->route('shop.checkout')->with('error', $message);
    }
}
