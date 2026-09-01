<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\EventBooking;
use App\Livewire\EventSignageDashboard;
use App\Livewire\Installer\InstallWizard;
use App\Livewire\Kiosk\KioskBooking;
use App\Livewire\Cms\CmsBlogIndex;
use App\Livewire\Cms\CmsPageShow;
use App\Livewire\Cms\CmsPostShow;
use App\Models\CmsRedirect;
use App\Models\Event;
use App\Http\Controllers\ThawaniCallbackController;
use App\Http\Controllers\NboCallbackController;
use App\Http\Controllers\CCAvenueRedirectController;
use App\Http\Controllers\CCAvenueCallbackController;
use App\Http\Controllers\CmsFrontendController;
use App\Http\Controllers\KioskHeartbeatController;
use App\Http\Controllers\PosOrderPaymentController;
use App\Http\Controllers\PrintAgentController;
use App\Livewire\Shop\Checkout as ShopCheckout;
use App\Livewire\Shop\ProductDetail as ShopProductDetail;
use App\Livewire\Shop\ShopCatalog;
use App\Models\PosOrder;

Route::get('/install', InstallWizard::class)->name('install');

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

// CCAvenue: full-page GET that renders the auto-submit POST form to the gateway.
Route::get('/booking/payment/ccavenue/{reference}', [CCAvenueRedirectController::class, 'show'])
    ->name('payment.redirect.ccavenue');

// CCAvenue browser-redirect callback (CSRF excluded via bootstrap/app.php).
Route::post('/booking/payment/callback/ccavenue', [CCAvenueCallbackController::class, 'callback'])
    ->name('payment.callback.ccavenue');

// ── POS online shop ───────────────────────────────────────────────────────────
Route::get('/shop', ShopCatalog::class)->name('shop.index');
Route::get('/shop/checkout', ShopCheckout::class)->name('shop.checkout');
Route::get('/shop/order/{order:order_number}/success', function (PosOrder $order) {
    abort_unless($order->isPaid(), 404);
    return view('shop.order-success', compact('order'));
})->name('shop.order.success');
Route::get('/shop/{product:slug}', ShopProductDetail::class)->name('shop.product.show');

// Thawani redirects here after shop checkout (success or cancel).
Route::get('/shop/payment/callback', [PosOrderPaymentController::class, 'thawaniCallback'])
    ->name('shop.payment.callback');

// Thawani server-to-server webhook (CSRF excluded via bootstrap/app.php).
Route::post('/shop/payment/webhook/thawani', [PosOrderPaymentController::class, 'thawaniWebhook'])
    ->name('shop.payment.webhook.thawani');

// NBO browser-redirect callback (CSRF excluded via bootstrap/app.php).
Route::post('/shop/payment/callback/nbo', [PosOrderPaymentController::class, 'nboCallback'])
    ->name('shop.payment.callback.nbo');

// CCAvenue: full-page GET that renders the auto-submit POST form to the gateway.
Route::get('/shop/payment/ccavenue/{orderNumber}', [PosOrderPaymentController::class, 'ccavenueRedirect'])
    ->name('shop.payment.redirect.ccavenue');

// CCAvenue browser-redirect callback (CSRF excluded via bootstrap/app.php).
Route::post('/shop/payment/callback/ccavenue', [PosOrderPaymentController::class, 'ccavenueCallback'])
    ->name('shop.payment.callback.ccavenue');

// Language switching
Route::get('/lang/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'ar'])) {
        session(['locale' => $locale]);
    }
    return redirect()->back();
})->name('lang.switch');

// ── On-site print agent ──────────────────────────────────────────────────────
// Polled by the on-site print-agent script (see print-agent/README.md) over
// HTTPS — no browser session, so CSRF is excluded (see bootstrap/app.php) and
// requests are instead authenticated with a bearer token (PrintAgentController).
Route::middleware(['throttle:120,1'])->group(function () {
    Route::get('/print-agent/jobs/next', [PrintAgentController::class, 'next'])->name('print-agent.next');
    Route::post('/print-agent/jobs/{job}/ack', [PrintAgentController::class, 'ack'])->name('print-agent.ack');
});

// ── CMS ───────────────────────────────────────────────────────────────────────
Route::get('/sitemap.xml', [CmsFrontendController::class, 'sitemap'])->name('cms.sitemap');
Route::get('/robots.txt', [CmsFrontendController::class, 'robots'])->name('cms.robots');
Route::get('/blog/feed', [CmsFrontendController::class, 'rssFeed'])->name('cms.feed');

Route::get('/blog', CmsBlogIndex::class)->name('cms.blog.index');
Route::get('/blog/category/{categorySlug}', CmsBlogIndex::class)->name('cms.blog.category');
Route::get('/blog/{slug}', CmsPostShow::class)->name('cms.post.show');

// Catch-all for CMS page slugs — must stay last so it never shadows a route
// above. Resolves its own 301/302 via cms_redirects before falling to 404.
Route::get('/{slug}', CmsPageShow::class)
    ->where('slug', '[^/]+')
    ->name('cms.page.show');

// True last resort for anything not matched by any route above (deep/legacy
// paths) — checks cms_redirects before letting the default 404 render.
Route::fallback(function () {
    $redirect = CmsRedirect::findForPath(request()->path());

    if ($redirect) {
        $redirect->recordHit();

        return redirect($redirect->to_path, $redirect->status_code);
    }

    abort(404);
});
