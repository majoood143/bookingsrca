<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\CCAvenueService;

class CCAvenueRedirectController extends Controller
{
    public function __construct(private readonly CCAvenueService $ccavenue) {}

    // ──────────────────────────────────────────────────────────────────────────
    // GET /booking/payment/ccavenue/{reference}
    //
    // CCAvenue's initiateTransaction endpoint requires a POST body
    // (encRequest, access_code), which Livewire can't produce via a normal
    // redirect. This route is a plain, full-page GET that renders an
    // auto-submitting hidden form targeting the gateway, mirroring the
    // WP plugin's document.redirect.submit() pattern.
    // ──────────────────────────────────────────────────────────────────────────
    public function show(string $reference)
    {
        $booking = Booking::where('booking_reference', $reference)->firstOrFail();

        $payload = $this->ccavenue->buildRedirectPayload($booking);

        return view('payments.ccavenue-redirect', $payload);
    }
}
