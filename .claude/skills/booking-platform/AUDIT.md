# Security & performance audit — 2026-10-28

## Status (updated 2026-10-28)

| # | Status |
|---|---|
| 1, 3, 4, 5, 6, 8, 10, 17 | **Fixed** — regression tests in `tests/Feature/PaymentSecurityTest.php` |
| 7 | **Fixed.** Correction: `storage/installed` *is* tracked in git; the risk was a test run or wiped `storage/` deleting it |
| 9 | **Fixed** (lock in `markPaid()`, random order numbers). Correction: `ShopCart::items()` already drops sold-out items, so the "no stock check" part was wrong |
| 11 | **Withdrawn**: `BookingConfirmation` and `PosOrderReceipt` already implement `ShouldQueue`, so `send()` queues them |
| 12 | **Fixed** for the web flow: 5 bookings / 10 min per session, 60 per IP (`EventBooking::submitBooking`). Kiosks rely on #13 instead |
| 13 | **Fixed, opt-in**: set a Device Token on the kiosk in the admin panel, then provision the device with `/kiosk/{code}?token=…` once. Kiosks without a token stay open as before |
| 14 | **Fixed**: random order numbers; guest checkout no longer updates an existing customer |
| 16 | **Partly fixed**: the API no longer returns secret settings (keys, passwords, tokens). Booking create/update through the API still bypasses booking rules — turn the API off if nothing uses it |
| 18, 19, 20 | **Fixed** (SQL sum in the widget; compiled views untracked + backup files removed; one shared `NboService::initiate()`, covered by a test) |
| 2 | **Open, on hold** until NBO's inquiry-API docs arrive. NBO goes through the locked settle path (#6), but a forged POST is still accepted |

Also fixed: `InstallWizardTest` leaked its DB env vars, so every later test ran against the real local MySQL database from `.env`; it also deleted `storage/installed` for good. Full suite went from 28 failures to 2 (both pre-existing: `ExampleTest` and one middleware test lack `RefreshDatabase`).

**What this app does:** Sells timed event tickets online and at self-service kiosks, runs a POS counter and an online shop, and hosts a penalty-shootout marketing game. Customers pay through Thawani, NBO or CCAvenue (Bank Muscat). Staff manage everything in a Filament admin panel.

**Load assumed:** one site, peaks of a few hundred people booking the same popular slot at once (event launches, venue crowds on one IP), several PHP workers plus a scheduler.

A "Livewire public property" below means a variable on a page component that the browser can change at will with `$wire.set('name', value)`, the same way it changes form fields.

---

## Must fix

### 1. Anyone can get a confirmed, "paid" booking without paying
`app/Livewire/EventBooking.php:93-110`
- **What this is:** After saving the booking, `submitBooking()` sends Thawani / NBO / CCAvenue customers to the gateway. Any other payment method falls into "Cash / Free — confirm immediately".
- **Problem:** `selectedPaymentMethod` and `enabledGateways` are public properties. A customer runs `$wire.set('selectedPaymentMethod','cash')` in the browser console, then pays. The booking is saved with `payment_status = paid`, confirmed, and tickets are emailed.
- **Fix:** Mark both properties `#[Locked]`, and in `submitBooking()` reject any method that is not in `resolveEnabledGateways()` (recomputed on the server). Only `'free'` may reach the confirm-now branch, and only when the total is 0.
- **If we skip it:** Free tickets for anyone who opens dev tools.

### 2. NBO payments can be forged with one POST
`app/Http/Controllers/NboCallbackController.php:112-138`, `app/Http/Controllers/PosOrderPaymentController.php:97-134`
- **What this is:** NBO posts the payment result to us through the customer's browser, unsigned and unencrypted (the code comments say so).
- **Problem:** The customer sees their `PaymentID` in the NBO URL and knows their own total. Posting `paymentid=<id>&result=CAPTURED&amt=<total>` to `/booking/payment/callback/nbo` confirms the booking. The shop version does not even check the amount.
- **Fix:** Treat the callback as a hint only. Confirm from NBO's server-to-server inquiry API (action 8 / "inquiry" on the tranportal), like Thawani's `reconcileBooking()`. Until that exists, keep NBO bookings pending and let staff confirm them.
- **If we skip it:** Free tickets and free shop orders for anyone who knows the trick.

### 3. Shop Thawani webhook trusts the event name, and webhooks accept anything when no secret is set
`app/Http/Controllers/PosOrderPaymentController.php:70-93`, `app/Services/ThawaniService.php:316-322`
- **What this is:** The shop webhook marks an order paid when the body says `event_type = payment_completed`.
- **Problem:** `verifyWebhookSignature()` returns `true` when `thawani.webhook_secret` is empty, which is the default. Anyone who knows an order's session id (it is in the Thawani checkout URL they were sent to) posts a fake `payment_completed` and the order is paid. The booking webhook avoids this by asking Thawani; the shop webhook does not.
- **Fix:** In the shop webhook, call `$this->thawani->getSession()` and act on its `payment_status`, exactly like `thawaniCallback()` above it. Make an empty secret reject in production.
- **If we skip it:** Free shop orders.

### 4. Ticket types and extra services are not scoped to the event
`app/Services/SeatAllocationService.php:83`, `:99`
- **What this is:** The single place that locks stock and computes the price.
- **Problem:** It loads `TicketType::where('id', $typeId)` and `ExtraService::whereIn('id', ...)` with no `event_id` check. The ids come from the public `ticketQuantities` / `ticketTypeServices` properties. A customer books event A with a cheap or free ticket type id from event B and pays B's price.
- **Fix:** Add `->where('event_id', $eventId)` to both queries (and `->where('is_active', true)` for services). Two lines.
- **If we skip it:** Underpriced bookings, and stock taken from the wrong event.

### 5. Stock is counted against the wrong ticket type for mixed bookings → overselling
`app/Models/TicketType.php:51-90`, `app/Models/Booking.php:232-243`
- **What this is:** A booking with 1 Adult + 3 Child saves `ticket_type_id = Adult` (the first one) and `quantity = 4`.
- **Problem:** `getActiveSoldQuantity()` sums `bookings.quantity` by `bookings.ticket_type_id`. Adult shows 4 sold, Child shows 0 sold. A Child type limited to 20 sells forever; Adult runs out early. `applyConfirmationCounters()` has the same error.
- **Fix:** Count per type from `booking_attendees` (each attendee row already has its own `ticket_type_id`), joined to bookings for the status filter.
- **If we skip it:** Limited ticket types oversell, others show sold out too soon.

### 6. Late NBO / CCAvenue callbacks confirm cancelled bookings with no lock and no capacity check
`app/Http/Controllers/NboCallbackController.php:120-135`, `app/Http/Controllers/CCAvenueCallbackController.php:74-91`
- **What this is:** On success these controllers call `$booking->confirm()` directly.
- **Problem:** The expiry sweep cancels pending bookings after 15 minutes. A customer who takes 20 minutes on the 3-D Secure page comes back to a cancelled booking; the callback confirms it anyway, even if the seats were resold. Two callbacks at once (double-submit, browser retry) both pass the `status === 'confirmed'` check and confirm twice: two emails, counters and promo usage applied twice.
- **Fix:** Move the "apply a paid result" logic out of `ThawaniService::applyPaidSession()` into one shared method (lock row, decide action, capacity re-check) and call it from all three gateways.
- **If we skip it:** Oversold slots and double-counted promo codes.

### 7. If `storage/installed` is ever missing, anyone can take over the site
`app/Http/Middleware/EnsureAppIsInstalled.php:19-25`, `app/Livewire/Installer/InstallWizard.php:195-216`, `:313-340`
- **What this is:** The installer is open to the internet whenever the marker file is absent.
- **Problem:** The file is not in git, and the test suite deletes it. A fresh deploy, a wiped `storage/`, or a test run on the server re-opens `/install`. A visitor then rewrites `.env` (DB host), or simply creates a new super admin — `createAdmin()` does not check that one already exists.
- **Fix:** Also treat the app as installed when the `users` table already has a `super_admin`, and refuse `saveDatabase()` / `createAdmin()` in that case. Optionally require an `INSTALL_TOKEN` env value.
- **If we skip it:** Full admin takeover after one bad deploy.

### 8. Other client-writable flags that bypass checks
`app/Livewire/Concerns/GuardsPrivateEvents.php:14`, `app/Livewire/Kiosk/KioskBooking.php:22`, `app/Livewire/Concerns/HandlesEventBookingFlow.php:56-84`
- **What this is:** Public booleans that gate access and validation.
- **Problem:** `$wire.set('passwordRequired', false)` skips a private event's password. `awaitingCardTap` and the `show*` / `require*` field flags can be flipped too, so required ID fields stop being required. Min-tickets and ticket-dependency rules run only in `nextStep()`, not in `submitBooking()`.
- **Fix:** `#[Locked]` on `passwordRequired`, `awaitingCardTap`, all `show*`/`require*` flags, `maxTickets`/`minTickets`; re-check `eventIsUnlocked()` and the min/dependency rules inside `persistBooking()`.
- **If we skip it:** Private events and required-data rules are decoration.

### 9. Shop: paid twice, stock taken twice, no stock check, order number collisions
`app/Models/PosOrder.php:59-64`, `:105-130`, `app/Livewire/Shop/Checkout.php:64-127`
- **What this is:** `markPaid()` checks `isPaid()` then writes; checkout creates orders without checking stock; numbers are `count() + 1`.
- **Problem:** Browser callback and webhook landing together both pass `isPaid()`: stock is decremented twice and two payment rows are written. Products with 0 stock can still be bought (stock goes negative). Two checkouts in the same second get the same `SO-000124`; the unique index throws a 500 for one of them.
- **Fix:** In `markPaid()`, re-select the order with `lockForUpdate()` inside a transaction and return if paid. Check stock in `placeOrder()`. Number orders from the auto-increment id after insert (`'SO-' . str_pad($order->id, 6, '0', STR_PAD_LEFT)`).
- **If we skip it:** Wrong stock and money totals, random checkout failures at busy times.

---

## Should fix

### 10. No index on the payment lookup columns
`database/migrations/2026_03_31_000001_add_payment_fields_to_bookings_table.php:14`, `database/migrations/2026_08_30_100002_create_pos_orders_table.php:40`
- **What this is:** Every callback, webhook and sweep finds the booking/order by `bookings.payment_session_id` or `pos_orders.payment_reference`.
- **Problem:** Neither column is indexed, so each lookup scans the whole table. With 100k bookings that is slow on exactly the requests a gateway times out on.
- **Fix:** One migration adding both indexes.
- **If we skip it:** Callbacks slow down as data grows; gateway retries pile up.

### 11. Email is sent inline while a row lock is held
`app/Models/Booking.php:246-260`, called from `ThawaniService::applyPaidSession()` (`:177-248`) and `PosOrder::markPaid()` (`:129`)
- **What this is:** `confirm()` calls `Mail::to()->send()` synchronously.
- **Problem:** In the Thawani path this runs inside the transaction that locks the booking. A slow SMTP server (2–10 s) holds the lock and the PHP worker; the webhook for the same booking waits behind it. On a busy launch, workers run out.
- **Fix:** `Mail::to(...)->queue(...)` (the queue already exists for ticket jobs), or dispatch after commit.
- **If we skip it:** Payment pages hang when the mail server is slow.

### 12. Nothing limits how many pending bookings one person can create
`app/Livewire/EventBooking.php:69`, `app/Livewire/Kiosk/KioskBooking.php:327`
- **What this is:** Each pending booking holds its seats for 15 minutes. The kiosk URL `/kiosk/{code}` is public.
- **Problem:** A short script (or a bored customer) creates pending bookings in a loop and holds every seat of a popular slot; real customers see "sold out".
- **Fix:** A `RateLimiter::attempt()` per session + IP in `persistBooking()` (e.g. 5 per 10 min), and require a device token (like the print agent's) for kiosk pages.
- **If we skip it:** Easy sell-out denial on launch day.

### 13. Kiosk wallet payment trusts a card UID sent by the browser
`app/Livewire/Kiosk/KioskBooking.php:263-325`
- **What this is:** The kiosk app passes the NFC card UID to `onCardTapped($uid)`.
- **Problem:** Because the kiosk page is public, anyone can call `onCardTapped` with any UID and spend that card's balance. UIDs are short and printed/readable by any phone.
- **Fix:** Same kiosk device token as #12 so only real kiosks reach this; log and alert on repeated "card not found".
- **If we skip it:** Wallet balances can be drained remotely.

### 14. Shop order pages and guest checkout leak / overwrite customer data
`routes/web.php:83-86`, `app/Models/PosCustomer.php:72-80`
- **What this is:** `/shop/order/SO-000123/success` shows the order and customer email; guest checkout updates an existing customer found by email.
- **Problem:** Order numbers are sequential, so anyone can walk them and collect emails. Anyone checking out with someone else's email overwrites that customer's name, phone and address.
- **Fix:** Show the success page only to the session that placed the order (store the order id in session). For guests, create a new customer row instead of updating the existing one.
- **If we skip it:** Personal data leak; corrupted customer records.

### 15. The riskiest code has no tests
`tests/Feature/*`
- **What this is:** Payment callbacks and the Livewire booking actions.
- **Problem:** There are no tests for NBO callbacks, shop NBO/Thawani callbacks, or for tampering with Livewire properties. Every finding above in those areas would have been caught by one test each.
- **Fix:** Add one feature test per gateway callback (forged result is rejected; late result on a cancelled booking does not oversell) and one Livewire test (`Livewire::test(EventBooking::class)->set('selectedPaymentMethod','cash')->call('submitBooking')` must not confirm).
- **If we skip it:** These holes come back after the next refactor.

### 16. Admin API writes bypass all booking rules
`app/Filament/Resources/Bookings/Api/Handlers/CreateHandler.php:30-36` (and the other `*/Api/Handlers`)
- **What this is:** `/api/admin/bookings` POST/PUT does `fill($request->all())->save()`.
- **Problem:** A token with `Create:Booking` can set any status, price or capacity-breaking quantity, skipping `SeatAllocationService`, confirmation, emails and counters. `GET /api/admin/booking-settings` returns gateway secret keys to any token with that view permission.
- **Fix:** If nothing uses the API, turn it off (remove the `Api` folders or the plugin). Otherwise use `$request->validated()` and hide secret settings from the transformer.
- **If we skip it:** One leaked API token can rewrite bookings and read payment keys.

---

## Nice to have

### 17. Expiry sweep can overlap itself
`routes/console.php:15`
- **What this is:** `bookings:cancel-expired` runs every 5 minutes and calls Thawani once per stale booking.
- **Problem:** With many stale bookings and a slow gateway, one run lasts more than 5 minutes and the next one starts on the same rows (safe thanks to locks, but double the API calls).
- **Fix:** Add `->withoutOverlapping()` like the recover-late-payments line below it.
- **If we skip it:** Wasted gateway calls and log noise.

### 18. Balance-due widget sums in PHP
`app/Filament/Widgets/TotalBalanceDueWidget.php:27-36`
- **What this is:** Loads every confirmed booking with a balance, then sums in PHP.
- **Problem:** Fine today; slow on the dashboard once there are tens of thousands of rows.
- **Fix:** One `SUM(total_price - paid)` query.
- **If we skip it:** Slower admin dashboard over time.

### 19. Files that should not be in the repo
`storage/framework/views/*.php` (60 compiled views tracked in git), `app/Livewire/EventBooking.php.backup`, `app/Models/TicketType.php.back`, `storage/installed_`
- **What this is:** Build output and old copies.
- **Problem:** Compiled views in git cause merge noise and can serve stale templates after deploy; `.backup` files confuse readers and searches.
- **Fix:** `git rm --cached storage/framework/views/*.php` (the folder's `.gitignore` already exists), delete the backup files.
- **If we skip it:** Noise, and a chance of shipping stale views.

### 20. NBO booking and order payment start are near-copies
`app/Services/NboService.php:89-167` vs `:173-247`
- **What this is:** Two 80-line methods that differ only in the amount, track id, billing info and where the payment id is saved.
- **Problem:** A fix to one (e.g. the inquiry call from #2) has to be repeated in the other.
- **Fix:** One private `initiate(array $plainData, Model $payable, string $responseUrl): array` that both call.
- **If we skip it:** The two copies drift apart.

---

## Duplicates & dead code (added 2026-11-13)

### 21. Eight empty controllers nothing routes to
`app/Http/Controllers/{Attendee,BookingAttendee,Booking,BookingExtraService,Event,ExtraService,TicketType,TimeSlot}Controller.php`
- **Problem:** 10-line stubs, not in `routes/` and not referenced anywhere. They suggest a second booking API that does not exist.
- **Fix:** Delete all eight.

### 22. Booking statuses are bare strings
228 literals of `'pending'` / `'confirmed'` / `'cancelled'` … across 52 files in `app/`.
- **Problem:** A typo (`'canceled'`) compiles and silently breaks capacity counts. Games models already use `STATUS_*` constants.
- **Fix:** Add `Booking::STATUS_*` constants; new and touched code uses them. Migrate the rest file by file when touched, not in one sweep.

### 23. `ThawaniService::applyPaidSession()` settles all three gateways
- **Problem:** The name hides that NBO and CCAvenue depend on it; a Thawani-only change can break them.
- **Fix:** When next touched, move it to a gateway-neutral owner (e.g. `BookingPaymentService::settle()`) and update the three callers + tests in the same change.

---

**Verdict:** Not safe to take real money yet. Fix 1, 2, 3 and 4 first (each is a few lines and stops free tickets/orders), then 5–7.

**Lean:** about −150 lines possible (#19, #20, sharing the confirm path in #6), 0 dependencies.

**Not checked:** Filament admin resources/pages beyond the API handlers, CMS rendering (XSS in rich content), WhatsApp/SMS gateways, `ThermalPrintService`, reports/exports, the games engine in depth, `print-agent/` script, Blade views beyond the two success pages, JS under `resources/js`. Nothing was run; findings come from reading code. `.env` has `APP_DEBUG=true` — make sure production does not.
