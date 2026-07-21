<?php

namespace App\Services;

use RuntimeException;
use App\Models\Booking;
use App\Models\BookingSetting;
use App\Models\PaymentGatewayLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NboService
{
    private const IV          = 'PGKEYENCDECIVSPC';
    private const SANDBOX_URL = 'https://unifiedpg.nbo.om/OLTPSTG/payment/hosted.htm';
    private const LIVE_URL    = 'https://unifiedpg.nbo.om/OLTP/payment/hosted.htm';
    private const CIPHER      = 'AES-256-CBC';
    private const CURRENCY    = '512'; // OMR

    private string $tranportalId;
    private string $tranportalPassword;
    private string $resourceKey;
    private string $endpointUrl;
    private bool   $testMode;

    public function __construct()
    {
        $this->tranportalId       = (string) BookingSetting::get('nbo.tranportal_id', '');
        $this->tranportalPassword = (string) BookingSetting::get('nbo.tranportal_password', '');
        $this->resourceKey        = (string) BookingSetting::get('nbo.resource_key', '');
        $this->testMode           = (bool)   BookingSetting::get('nbo.test_mode', true);

        $customUrl             = (string) BookingSetting::get('nbo.endpoint_url', '');
        $this->endpointUrl     = $customUrl ?: ($this->testMode ? self::SANDBOX_URL : self::LIVE_URL);
    }

    // ── Encryption ───────────────────────────────────────────────────────────

    public function encrypt(array $data): string
    {
        $json      = json_encode([$data], JSON_UNESCAPED_UNICODE);
        $urlEncoded = urlencode($json);

        $encrypted = openssl_encrypt(
            $urlEncoded,
            self::CIPHER,
            $this->resourceKey,
            OPENSSL_RAW_DATA,
            self::IV
        );

        if ($encrypted === false) {
            throw new RuntimeException('NBO encryption failed: ' . openssl_error_string());
        }

        return strtoupper(bin2hex($encrypted));
    }

    public function decrypt(string $hexData): array
    {
        $binary    = hex2bin(strtolower($hexData));
        $decrypted = openssl_decrypt(
            $binary,
            self::CIPHER,
            $this->resourceKey,
            OPENSSL_RAW_DATA,
            self::IV
        );

        if ($decrypted === false) {
            throw new RuntimeException('NBO decryption failed: ' . openssl_error_string());
        }

        $decoded = urldecode($decrypted);
        $parsed  = json_decode($decoded, true);

        // NBO wraps the payload in an array: [{ ... }]
        if (is_array($parsed) && isset($parsed[0])) {
            return $parsed[0];
        }

        return is_array($parsed) ? $parsed : [];
    }

    // ── Payment initiation ───────────────────────────────────────────────────

    public function initiatePayment(Booking $booking): string
    {
        $responseUrl = route('payment.callback.nbo');

        $attendee = $booking->attendees->first();

        $plainData = [
            'id'           => $this->tranportalId,
            'password'     => $this->tranportalPassword,
            'action'       => '1',
            'amt'          => number_format((float) $booking->total_price, 3, '.', ''),
            'currencycode' => self::CURRENCY,
            'langid'       => 'en',
            'trackId'      => $this->trackId($booking),
            'responseURL'  => $responseUrl,
            'errorURL'     => $responseUrl,
            'udf1'         => str_replace('-', '', (string) $booking->booking_reference),
            'udf2'         => preg_replace('/\D+/', '', (string) ($attendee?->phone ?? '')),
            //'udf3'         => (string) ($attendee?->email ?? ''),
            'billingInfo'  => $this->billingInfo($booking),
        ];

        $trandata = $this->encrypt($plainData);

        $requestPayload = [
            'id'          => $this->tranportalId,
            'trandata'    => $trandata,
            'responseURL' => $responseUrl,
            'errorURL'    => $responseUrl,
        ];

        $response = Http::asJson()->post($this->endpointUrl, [$requestPayload]);

        $body    = json_decode(trim($response->body()), true);
        $payload = is_array($body) ? ($body[0] ?? $body) : [];

        PaymentGatewayLog::log(
            $booking,
            'nbo',
            'initiate_payment',
            ['plain' => array_merge($plainData, ['password' => '••••••••']), 'encrypted' => $requestPayload],
            $payload ?: ['raw' => $response->body()],
            $response->status(),
        );

        if (!$response->successful()) {
            Log::error('NBO initiatePayment failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new RuntimeException('NBO payment gateway error: ' . $response->body());
        }

        if (($payload['status'] ?? '') !== '1') {
            $errorText = $payload['errorText'] ?? $payload['error'] ?? 'Payment initiation failed';
            Log::error('NBO initiatePayment: gateway error', ['payload' => $payload]);
            throw new RuntimeException('NBO: ' . $errorText);
        }

        // result field contains "PaymentID:PaymentPageURL"
        $resultStr = $payload['result'] ?? '';
        $colonPos  = strpos($resultStr, ':');

        if ($colonPos === false || $colonPos === 0) {
            Log::error('NBO initiatePayment: unexpected result field', ['payload' => $payload]);
            throw new RuntimeException('NBO: unexpected response format.');
        }

        $paymentId  = substr($resultStr, 0, $colonPos);
        $paymentUrl = substr($resultStr, $colonPos + 1);

        if (empty($paymentId) || empty($paymentUrl)) {
            throw new RuntimeException('NBO: empty PaymentID or URL in response.');
        }

        $booking->update(['payment_session_id' => $paymentId]);

        return $paymentUrl . '?PaymentID=' . $paymentId;
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * The integration guide specifies trackId as Numeric (pages 14, 22, 36),
     * but booking_reference is alphanumeric (e.g. "BK-7F3K9QX2"). Build a
     * numeric value instead, prefixed with the booking id for traceability
     * and suffixed with milliseconds so retries of the same booking don't
     * collide ("IPAY0100114 - Duplicate Record").
     */
    private function trackId(Booking $booking): string
    {
        return $booking->id . substr((string) round(microtime(true) * 1000), -6);
    }

    /**
     * billingInfo is marked mandatory for CyberSource in the integration
     * guide. Without it NBO/CyberSource rejects the transaction during
     * authorization with "IPAY0100221 - Invalid email id" - even though the
     * initial request validates fine - because no billing email was ever
     * sent at all. Name/email/phone come from the booking's primary
     * attendee; the address fields aren't collected anywhere in the
     * booking flow, so reasonable static defaults are used to satisfy
     * CyberSource's required-field validation.
     *
     * country must be an ISO 3166-1 alpha-2 code ("OM"), not the full name
     * ("Oman") shown in the integration guide's sample payload - CyberSource's
     * Payer Authentication enrollment check rejects the full name, which
     * passes NBO's own request validation (so initiatePayment succeeds and a
     * payment page is returned) but then fails once NBO forwards the billing
     * data to CyberSource during the post-redirect auth step, surfacing as
     * "IPAY0400015 - Problem Occurred in Perform Auth With Check Enroll".
     * phoneNumber must be digits only, no "+" prefix, for the same reason.
     */
    private function billingInfo(Booking $booking): array
    {
        $attendee = $booking->attendees->first();
        $phone    = $attendee?->phone ?: '00000000';

        return [
            'firstName'          => $attendee?->first_name ?: 'Guest',
            'lastName'           => $attendee?->last_name ?: 'Guest',
            'country'            => 'OM',
            'phoneNumber'        => preg_replace('/\D+/', '', $phone) ?: '00000000',
            'address'            => $booking->event?->location ?: 'Raza Farm',
            'postalCode'         => '100',
            'locality'           => 'Muscat',
            'administrativeArea' => 'Muscat',
            'email'              => $attendee?->email ?: 'noemail@razatfarm.gov.om',
        ];
    }

    public function isConfigured(): bool
    {
        return !empty($this->tranportalId)
            && !empty($this->tranportalPassword)
            && !empty($this->resourceKey);
    }

    public function isTestMode(): bool
    {
        return $this->testMode;
    }
}
