<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\EventBooking;
use App\Livewire\EventSignageDashboard;
use App\Livewire\Kiosk\KioskBooking;
use App\Models\Event;
use App\Http\Controllers\ThawaniCallbackController;
use App\Http\Controllers\NboCallbackController;
use App\Http\Controllers\KioskHeartbeatController;

Route::get('/', function () {
    $events = Event::published()
        ->upcoming()
        ->with('ticketTypes')
        ->withSum(['bookings as booked_quantity' => fn($query) => $query->where('status', '!=', 'cancelled')], 'quantity')
        ->orderBy('start_date')
        ->get();

    return view('welcome', compact('events'));
});

Route::get('/events/{event:slug}', EventBooking::class)->name('event.booking');

Route::get('/events/{event:slug}/signage', EventSignageDashboard::class)->name('event.signage');

// ── Kiosk routes ─────────────────────────────────────────────────────────────
Route::get('/kiosk/{kiosk:code}', KioskBooking::class)->name('kiosk.booking');

// Called by the kiosk's native app — no browser session, so CSRF is excluded (see bootstrap/app.php).
Route::post('/kiosk/{kiosk:code}/heartbeat', [KioskHeartbeatController::class, 'store'])->name('kiosk.heartbeat');

Route::get('/booking/success/{reference}', function ($reference) {
    $booking = \App\Models\Booking::where('booking_reference', $reference)->firstOrFail();
    return view('booking.success', compact('booking'));
})->name('booking.success');

// ── Payment routes ────────────────────────────────────────────────────────────
// Thawani redirects the user back here after checkout (success or cancel).
Route::get('/booking/payment/callback', [ThawaniCallbackController::class, 'callback'])
    ->name('payment.callback');

// Thawani server-to-server webhook (CSRF excluded via bootstrap/app.php).
Route::post('/booking/payment/webhook/thawani', [ThawaniCallbackController::class, 'webhook'])
    ->name('payment.webhook.thawani');

// NBO browser-redirect callback (CSRF excluded via bootstrap/app.php).
Route::post('/booking/payment/callback/nbo', [NboCallbackController::class, 'callback'])
    ->name('payment.callback.nbo');

// Language switching
Route::get('/lang/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'ar'])) {
        session(['locale' => $locale]);
    }
    return redirect()->back();
})->name('lang.switch');

// Printable POS receipt for the booking wizard (admin only).
Route::get('/admin/bookings/{booking}/receipt', function (\App\Models\Booking $booking) {
    return view('bookings.pos-receipt', [
        'booking' => $booking->load(['event', 'timeSlot', 'ticketType', 'attendees', 'extraServices', 'payments']),
    ]);
})->middleware(['auth'])->name('bookings.receipt');

// Printable attendee ticket receipts, one separated ticket per attendee with a booking-reference barcode (admin only).
Route::get('/admin/bookings/{booking}/attendee-tickets', function (\App\Models\Booking $booking) {
    return view('bookings.attendee-tickets-receipt', [
        'booking' => $booking->load(['event', 'timeSlot', 'attendees.ticketType']),
    ]);
})->middleware(['auth'])->name('bookings.attendee-tickets');
