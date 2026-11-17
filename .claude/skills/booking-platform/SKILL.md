---
name: booking-platform
description: Use when changing, adding to, planning or reviewing anything in this Laravel event-booking / POS / shop / games app — booking wizard, kiosk, payment gateways (Thawani, NBO, CCAvenue), shop orders, capacity, admin resources, CMS/SEO, translations — or when tempted to write a new service, helper, controller or status string here.
---

# Booking platform

Laravel 12 + Filament 4 (admin at `/admin`) + Livewire 3 (public pages). MySQL. Database cache, session and queue. Arabic + English. Open security/performance gaps: [AUDIT.md](AUDIT.md).

## How work is done here

Every change follows these five steps in order. No step is optional for a change that touches money, capacity, access or data. A one-line copy or style fix needs only steps 2 and 5.

1. **Spec.** Write down, before any code: what the user can do after the change, the acceptance criteria as testable sentences ("a cancelled booking paid late is refunded, not confirmed"), and what is out of scope. If a criterion is unknown, ask. Do not guess business rules (prices, capacity, refunds, who may see what).
2. **Find the owner.** Look up the concern in *One place per concern* below and `grep` for the behaviour. If an owner exists, change it or call it. A second implementation is a defect, even if it is "just a small variant".
3. **Touch list.** List every file the change must reach: callers (`grep` the method name), Filament resource + its `Api/` handler, Livewire component + Blade view, both `lang/en*` and `lang/ar*` files, migration, factory, tests, `routes/console.php` if scheduled. The change is not done until each item is handled.
4. **Test first** for any branch, money, capacity, permission or state transition: a failing feature test in `tests/Feature/`, then the code. Bug fix = a test that reproduces the bug first.
5. **Verify.** Run `php artisan test` and `vendor/bin/pint --dirty`. Compare against the baseline (2 known failures: `ExampleTest`, "install route itself is reachable"). Report the real numbers. A new failure means the work is not done.

**Done** = acceptance criteria each covered by a test or a stated manual check, the full suite at baseline, Pint clean, no TODOs left in the diff, both languages translated.

## One place per concern

| Concern | The one owner | Never |
|---|---|---|
| Price + lock stock for a sale | `SeatAllocationService::lockAndPrice()` inside `DB::transaction` | price math in a component or controller |
| Confirm a paid online booking (all 3 gateways) | `ThawaniService::applyPaidSession($booking, $data, gateway: '…')` | `$booking->confirm()` from a controller |
| Classify a logged gateway response (admin views, reports) | `Support\ClassifiesGatewayOutcome` (trait on both log models) | per-gateway status checks in resources |
| Start an NBO payment | `NboService::initiate()` | copying the booking variant for orders |
| Mark a shop order paid | `PosOrder::markPaid()` (locks the row) | writing `payment_status` directly |
| Stock movement | `PosProduct::applyStockMovement()` | `decrement('stock')` |
| Kiosk wallet balance | `KioskCard::applyTransaction()` | editing `balance` |
| Promo codes | `PromoCodeService::validate()` / `recordUsage()` | |
| Commission | `CommissionService::resolveAndCalculate()` | |
| Capacity counts | `TimeSlot::getActiveBookedQuantity`, `TicketType::getActiveSoldQuantity` (from `booking_attendees`), `ExtraService::getActiveUsedQuantity` | reading `current_bookings` / `quantity_sold` (denormalized counters only) |
| Booking wizard steps (web + kiosk) | `Livewire/Concerns/HandlesEventBookingFlow` | logic in only one of `EventBooking` / `KioskBooking` |
| Private-event access | `Livewire/Concerns/GuardsPrivateEvents` | |
| Settings, gateway keys, secrets | `BookingSetting::get()` (cached 1 h) | `env()` outside `config/`, new settings tables |
| Phone numbers | `Support\PhoneNumber` | ad-hoc regex |
| Money display | `partials/currency-amount` / `currency-label` | `number_format` in views |
| AR target index → item (MindAR) | `ArZone::target_order` via `storeCompiledTargets()` / `scannerCards()` | mapping indexes from current item order |
| Every outbound gateway call | logged to `PaymentGatewayLog` (bookings) / `PosOrderGatewayLog` (shop) | silent HTTP calls |

Adding a new concern: put it in a Service (`app/Services`) if it spans models or calls outside, a model method if it changes one model's own state. Register it in this table in the same change.

## Coding rules

- **Statuses and types are constants, not literals.** Games models do this (`GameAttempt::STATUS_STARTED`). Bookings still use bare `'pending'` / `'confirmed'` strings (228 places); new or changed booking code adds and uses `Booking::STATUS_*` constants. Do not mass-rewrite old call sites outside the task.
- **Validate at the edge, trust nothing from the browser.** Form Requests or `$this->validate()` with explicit rules; never `$request->all()` into `fill()`/`create()`. Livewire public properties are user input: anything that decides money, access or flow is `#[Locked]` or re-checked in the action that uses it.
- **State changes are transactions.** Lock the row (`lockForUpdate`), re-read status, then write. Idempotent: running it twice gives the same result as once.
- **No side effects under a lock.** Mail implements `ShouldQueue`; HTTP calls happen before or after the transaction, never inside.
- **Schema changes are new migrations.** Never edit a shipped migration. Index every column used in a `where` on callbacks, webhooks and sweeps. Add/extend the factory in the same change.
- **Authorization through policies** (`app/Policies`, Filament Shield). No `if ($user->email === …)`.
- **Translations:** every user-facing string goes through `__()` and is added to both `lang/en*` and `lang/ar*` in the same change.
- **Delete, don't park.** No `.backup` / `.old` files, no commented-out code, no empty stub classes. Git is the history.
- **No new dependency** for something Laravel, Filament or an installed package already does.

## Rules for money paths

1. Settle only from an authenticated source: a signed gateway message (CCAvenue GCM, Thawani signed webhook) or our own server-to-server lookup (`ThawaniService::getSession`). The browser redirect is a hint.
2. An empty webhook secret must reject in production.
3. Every id from the client is scoped to its event / owner before use (`->where('event_id', $eventId)`).
4. A late payment on a cancelled booking re-checks capacity under the lock before confirming.
5. Ticket stock is counted from `booking_attendees.ticket_type_id`; `bookings.ticket_type_id` is only the first type of a mixed booking.

## Red flags — stop and go back to step 2

| You are about to… | Instead |
|---|---|
| write a new helper/service "because it's faster than finding the old one" | `grep` the behaviour; extend the owner |
| copy a method and tweak it | extract the shared part into the owner, call it from both |
| fix only the web booking flow | the kiosk uses the same trait — fix it there |
| say "should work" / "looks right" | run the test and quote the result |
| skip the test "because it's a small change" to money/capacity/access | write the test; small money bugs are free tickets |
| invent a rule (refund window, VAT, limits) | ask the user |
| add a column or setting that already exists under another name | check the model's `$fillable` and `booking_settings` keys first |
| leave a TODO "for later" | finish it, or list it in AUDIT.md with file:line |

## Review checklist

Design
- [ ] Does an owner for this already exist (table above)? Is anything implemented twice?
- [ ] Is every item on the touch list handled (callers, Api handler, both languages, factory, tests)?
- [ ] New status/type string? Is it a constant?

Security
- [ ] New Livewire public property — can `$wire.set()` it bypass something?
- [ ] New route excluded from CSRF in `bootstrap/app.php` — what authenticates it instead?
- [ ] Payment status change — authenticated source, row lock, idempotent?
- [ ] Public URL with a guessable id? Personal files on the `local` disk, never `public`?
- [ ] Rate limit on anything that sends SMS/email or holds capacity?

Performance
- [ ] Hot-path `where` columns indexed; no query per row in loops (`withSum`, grouped sums — see `Event::getSoldOutDates`).
- [ ] Scheduled commands calling gateways use `withoutOverlapping()`.

## Map

| Area | Entry points | Core logic |
|---|---|---|
| Online booking | `/events/{slug}` → `Livewire\EventBooking` | `HandlesEventBookingFlow::persistBooking()`, `SeatAllocationService` |
| Kiosk | `/kiosk/{code}` → `Livewire/Kiosk/KioskBooking`; optional `kiosks.device_token` (`?token=` once, then session) | same trait; `KioskCard::applyTransaction()` |
| Thawani | `ThawaniCallbackController` (callback, cancel, webhook) | `ThawaniService::reconcileBooking()` / `applyPaidSession()` |
| NBO | `NboCallbackController`, `PosOrderPaymentController::nboCallback` | `NboService` |
| CCAvenue | `CCAvenueCallbackController`, `PosOrderPaymentController::ccavenueCallback` | `CCAvenueService` (AES-GCM) |
| Shop | `/shop*` → `Livewire/Shop/*` | `PosOrder::markPaid()`, `PosProduct::applyStockMovement()` |
| Games | `/play/{campaign}` → `Controllers/Games/*` | `Services/Games/*`, `Messaging/OtpService` |
| Print agent | `/print-agent/*` (bearer token) | `PrintAgentController` |
| Admin REST API | `/api/admin/*` (Sanctum + Shield) | `Filament/Resources/*/Api/Handlers` |
| Installer | `/install` → `Livewire/Installer/InstallWizard`, gated by `storage/installed` | `Middleware/EnsureAppIsInstalled` |
| AR explore | `/explore*` → `ArExploreController`; admin compiles `.mind` in browser → `ArZoneTargetsController` | `ArZone`, `resources/js/ar/*` (MindAR from CDN, no three.js) |
| Scheduler | `routes/console.php` | expire bookings (5 min), recover late Thawani payments, reports, CMS publish, games |

Booking lifecycle: `pending` (holds capacity) → `confirmed` → `checked_in`, or `cancelled` / `refunded`. Capacity = live SUM over confirmed + non-failed pending bookings.

Filament layout: `XResource.php` + `XResource/` (pages, relation managers) is the admin UI; the plural folder (`Bookings/`, `Events/`…) holds only its `Api/`. Add admin features to the resource, API endpoints to `Api/`; never a third place.

## SEO

Indexable: `/`, `/events/{slug}`, `/shop`, `/shop/{slug}`, `/blog*`, `/explore*` (not `/explore/*/scan/`), CMS `/{slug}`. Head comes from `components/layouts/app.blade.php`: `<title>` via `->title()` in `render()`, the rest via `@push('meta')` inside the component root.

- Each indexable page: `->title()`, meta description (`Str::squish()->limit(160)`), self canonical without query string, og/twitter tags, JSON-LD where a schema.org type fits. Copy the event or product-detail view.
- Language is per session, not per URL — no `hreflang`.
- `/robots.txt` and `/sitemap.xml` are routes in `CmsFrontendController`; never add the static files to `public/`.
- New public slug model → add to `sitemap()` (published only). New transactional/device/token URL → `Disallow:` in `robots()` or `noindex`.
- Tests: `tests/Feature/SeoTest.php`.

## Tests

`php artisan test` (baseline 2 failures, above). New feature tests use `RefreshDatabase` (`EnsureAppIsInstalledMiddlewareTest` is the one existing exception). Payments: `PaymentSecurityTest.php`, `CCAvenuePaymentTest.php`, `ThawaniLatePaymentTest.php`, `PosShopTest.php`. Kiosk: `KioskBookingTest.php`. Games: `tests/Feature/Games/`. NBO callbacks still have no tests — add one with any NBO change.
