<?php

namespace App\Services;

use RuntimeException;
use App\Models\Booking;
use App\Models\BookingSetting;
use App\Models\PaymentGatewayLog;

class CCAvenueService
{
    // Generic CCAvenue-documented hosts. Bank Muscat's MCPG instance may use a
    // different white-labeled host — override via ccavenue.endpoint_url once
    // the real merchant-provided sandbox/live URLs are known.
    private const SANDBOX_URL = 'https://test.ccavenue.com/transaction/transaction.do?command=initiateTransaction';
    private const LIVE_URL    = 'https://secure.ccavenue.com/transaction/transaction.do?command=initiateTransaction';
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
     * for an auto-submitting POST form. order_id uses booking_reference
     * (not the sequential booking id) to match Thawani's approach and avoid
     * exposing sequential identifiers to the gateway/back-office. If the real
     * gateway rejects the hyphen in booking_reference, strip it here and in
     * the callback lookup consistently.
     *
     * @return array{url: string, encRequest: string, access_code: string}
     */
    public function buildRedirectPayload(Booking $booking): array
    {
        $callbackUrl = route('payment.callback.ccavenue');
        $attendee    = $booking->attendees->first();

        $plainData = [
            'merchant_id'      => $this->merchantId,
            'order_id'         => $booking->booking_reference,
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

        $booking->update(['payment_session_id' => $booking->booking_reference]);

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
