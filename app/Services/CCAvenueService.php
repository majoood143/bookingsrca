<?php

namespace App\Services;

use RuntimeException;
use App\Models\Booking;
use App\Models\BookingSetting;
use App\Models\PaymentGatewayLog;
use App\Models\PosOrder;
use App\Models\PosOrderGatewayLog;

class CCAvenueService
{
    // Generic CCAvenue-documented hosts. Bank Muscat's MCPG instance may use a
    // different white-labeled host — override via ccavenue.endpoint_url once
    // the real merchant-provided sandbox/live URLs are known.
    private const SANDBOX_URL = 'https://mti.bankmuscat.com:6443/transaction.do?command=initiateTransaction';
    private const LIVE_URL    = 'https://smartpaytrns.bankmuscat.com/transaction.do?command=initiateTransaction';
    private const CIPHER      = 'aes-256-gcm';
    private const CURRENCY    = 'OMR';

    private string $merchantId;
    private string $accessCode;
    private string $workingKey;
    private string $endpointUrl;
    private bool   $testMode;

    public function __construct()
    {
        $this->merchantId = (string) BookingSetting::get('ccavenue.merchant_id', '');
        $this->accessCode = (string) BookingSetting::get('ccavenue.access_code', '');
        $this->workingKey = (string) BookingSetting::get('ccavenue.working_key', '');
        $this->testMode   = (bool)   BookingSetting::get('ccavenue.test_mode', true);

        $customUrl         = (string) BookingSetting::get('ccavenue.endpoint_url', '');
        $this->endpointUrl = $customUrl ?: ($this->testMode ? self::SANDBOX_URL : self::LIVE_URL);
    }

    // ── Encryption ───────────────────────────────────────────────────────────

    /**
     * AES-256-GCM encrypt, matching CCAvenue's documented scheme: random
     * 16-byte IV, output is hex(iv) . hex(ciphertext . tag).
     */
    public function encrypt(string $plainText): string
    {
        $iv = openssl_random_pseudo_bytes(16);

        $cipherText = openssl_encrypt(
            $plainText,
            self::CIPHER,
            $this->workingKey,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($cipherText === false) {
            throw new RuntimeException('CCAvenue encryption failed: ' . openssl_error_string());
        }

        return bin2hex($iv) . bin2hex($cipherText . $tag);
    }

    /**
     * Reverse of encrypt(): first 16 bytes are the IV, last 16 bytes are the
     * GCM auth tag, everything in between is the ciphertext.
     */
    public function decrypt(string $encryptedText): string
    {
        $binary = hex2bin($encryptedText);

        if ($binary === false) {
            throw new RuntimeException('CCAvenue decryption failed: invalid hex payload.');
        }

        $ivLength  = 16;
        $tagLength = 16;

        $iv         = substr($binary, 0, $ivLength);
        $tag        = substr($binary, -$tagLength);
        $cipherText = substr($binary, $ivLength, -$tagLength);

        $plainText = openssl_decrypt(
            $cipherText,
            self::CIPHER,
            $this->workingKey,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($plainText === false) {
            throw new RuntimeException('CCAvenue decryption failed: ' . openssl_error_string());
        }

        return $plainText;
    }

    // ── Payment initiation ───────────────────────────────────────────────────

    /**
     * Build the encrypted request CCAvenue expects and the redirect target,
     * for an auto-submitting POST form. order_id is derived from
     * booking_reference (not the sequential booking id) to match Thawani's
     * approach and avoid exposing sequential identifiers to the gateway/
     * back-office — but with the hyphen stripped, since CCAvenue/Bank Muscat
     * rejects it ("order_id: Invalid Character. Error Code: 21000") and only
     * accepts alphanumeric order_ids. The stripped value is stored on
     * payment_session_id so the callback can look the booking back up by
     * the exact same value CCAvenue echoes back as order_id.
     *
     * @return array{url: string, encRequest: string, access_code: string}
     */
    public function buildRedirectPayload(Booking $booking): array
    {
        $callbackUrl = route('payment.callback.ccavenue');
        $attendee    = $booking->attendees->first();
        $orderId     = preg_replace('/[^A-Za-z0-9]/', '', $booking->booking_reference);

        $plainData = [
            'merchant_id'      => $this->merchantId,
            'order_id'         => $orderId,
            'amount'           => number_format((float) $booking->total_price, 3, '.', ''),
            'redirect_url'     => $callbackUrl,
            'cancel_url'       => $callbackUrl,
            'billing_name'     => trim(($attendee?->first_name ?: 'Guest') . ' ' . ($attendee?->last_name ?: '')),
            'billing_address'  => $booking->event?->location ?: 'Raza Farm',
            'billing_city'     => 'Muscat',
            'billing_state'    => 'Muscat',
            'billing_zip'      => '100',
            'billing_country'  => 'Oman',
            'billing_tel'      => preg_replace('/\D+/', '', $attendee?->phone ?: '00000000') ?: '00000000',
            'billing_email'    => $attendee?->email ?: 'noemail@razatfarm.gov.om',
            'delivery_name'    => '',
            'delivery_address' => '',
            'delivery_city'    => '',
            'delivery_state'   => '',
            'delivery_zip'     => '',
            'delivery_country' => '',
            'delivery_tel'     => '',
            'language'         => 'EN',
            'currency'         => self::CURRENCY,
            'tid'              => (string) time(),
        ];

        $queryString = http_build_query($plainData);
        $encRequest  = $this->encrypt($queryString);

        PaymentGatewayLog::log(
            $booking,
            'ccavenue',
            'initiate_payment',
            ['plain' => array_merge($plainData, ['billing_email' => '••••••••']), 'endpoint' => $this->endpointUrl],
            ['encRequest_len' => strlen($encRequest)],
        );

        $booking->update(['payment_session_id' => $orderId]);

        return [
            'url'         => $this->endpointUrl,
            'encRequest'  => $encRequest,
            'access_code' => $this->accessCode,
        ];
    }

    // Parallel to buildRedirectPayload() above, for a POS shop order. order_id
    // is derived from order_number instead of booking_reference, and stored
    // on payment_reference (POS orders have no separate session-id column) so
    // the callback can look the order back up by the exact value CCAvenue
    // echoes back.
    public function buildOrderRedirectPayload(PosOrder $order): array
    {
        $callbackUrl = route('shop.payment.callback.ccavenue');
        $orderId     = preg_replace('/[^A-Za-z0-9]/', '', $order->order_number);

        $plainData = [
            'merchant_id'      => $this->merchantId,
            'order_id'         => $orderId,
            'amount'           => number_format((float) $order->grand_total, 3, '.', ''),
            'redirect_url'     => $callbackUrl,
            'cancel_url'       => $callbackUrl,
            'billing_name'     => $order->contact_name,
            'billing_address'  => $order->address_line_1 ?: 'Raza Farm',
            'billing_city'     => $order->city ?: 'Muscat',
            'billing_state'    => $order->state ?: 'Muscat',
            'billing_zip'      => $order->zip_code ?: '100',
            'billing_country'  => $order->country ?: 'Oman',
            'billing_tel'      => preg_replace('/\D+/', '', $order->contact_mobile) ?: '00000000',
            'billing_email'    => $order->contact_email,
            'delivery_name'    => '',
            'delivery_address' => '',
            'delivery_city'    => '',
            'delivery_state'   => '',
            'delivery_zip'     => '',
            'delivery_country' => '',
            'delivery_tel'     => '',
            'language'         => 'EN',
            'currency'         => self::CURRENCY,
            'tid'              => (string) time(),
        ];

        $queryString = http_build_query($plainData);
        $encRequest  = $this->encrypt($queryString);

        PosOrderGatewayLog::log(
            $order,
            'ccavenue',
            'initiate_payment',
            ['plain' => array_merge($plainData, ['billing_email' => '••••••••']), 'endpoint' => $this->endpointUrl],
            ['encRequest_len' => strlen($encRequest)],
        );

        $order->update(['payment_method' => 'ccavenue', 'payment_reference' => $orderId]);

        return [
            'url'         => $this->endpointUrl,
            'encRequest'  => $encRequest,
            'access_code' => $this->accessCode,
        ];
    }

    public function isConfigured(): bool
    {
        return !empty($this->merchantId) && !empty($this->accessCode) && !empty($this->workingKey);
    }

    public function isTestMode(): bool
    {
        return $this->testMode;
    }
}
