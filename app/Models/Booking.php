<?php

namespace App\Models;

use App\Jobs\GenerateAttendeeTicketAssets;
use App\Jobs\SendBookingTickets;
use App\Mail\AllTickets;
use App\Mail\BookingConfirmation;
use App\Services\PromoCodeService;
use App\Support\MpdfAlmarai;
use DeviceDetector\DeviceDetector;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Picqer\Barcode\BarcodeGenerator;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Booking extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'booking_reference',
        'event_id',
        'time_slot_id',
        'ticket_type_id',
        'event_date',
        'quantity',
        'ticket_price',
        'services_price',
        'total_price',
        'promo_code_id',
        'discount_amount',
        'source',
        'created_by',
        'kiosk_id',
        'status',
        'confirmed_at',
        'cancelled_at',
        'cancellation_source',
        'refunded_at',
        'refunded_by',
        'refund_reason',
        // Note: attendee_id was removed from bookings table by create_booking_attendees migration
        // Attendee info is stored in booking_attendees table instead
        'payment_method',
        'payment_status',
        'payment_session_id',
        'payment_reference',
        'locale',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'event_date' => 'date',
        'ticket_price' => 'decimal:3',
        'services_price' => 'decimal:3',
        'total_price' => 'decimal:3',
        'discount_amount' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'status', 'payment_status', 'payment_method',
                'quantity', 'ticket_price', 'services_price', 'total_price',
                'event_date', 'source', 'locale', 'confirmed_at', 'cancelled_at', 'cancellation_source',
                'refunded_at', 'refund_reason',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->booking_reference)) {
                $booking->booking_reference = 'BK-'.strtoupper(Str::random(8));
            }
        });
    }

    // Relationships
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function timeSlot()
    {
        return $this->belongsTo(TimeSlot::class);
    }

    public function ticketType()
    {
        return $this->belongsTo(TicketType::class);
    }

    public function attendees()
    {
        return $this->hasMany(BookingAttendee::class);
    }

    public function firstAttendee()
    {
        return $this->hasOne(BookingAttendee::class)->oldestOfMany();
    }

    public function extraServices()
    {
        return $this->belongsToMany(ExtraService::class, 'booking_extra_services')
            ->withPivot('quantity', 'price')
            ->withTimestamps();
    }

    public function payments()
    {
        return $this->hasMany(BookingPayment::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function refundedBy()
    {
        return $this->belongsTo(User::class, 'refunded_by');
    }

    public function kiosk()
    {
        return $this->belongsTo(Kiosk::class);
    }

    public function promoCode()
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function gatewayLogs()
    {
        return $this->hasMany(PaymentGatewayLog::class)->latest();
    }

    public function getTotalPaidAttribute(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function getBalanceDueAttribute(): float
    {
        return max(0, (float) $this->total_price - $this->total_paid);
    }

    public function refreshPaymentStatus(): void
    {
        $totalPaid = $this->total_paid;

        $status = match (true) {
            $totalPaid <= 0 => 'pending',
            $totalPaid >= (float) $this->total_price => 'paid',
            default => 'partial',
        };

        $this->update(['payment_status' => $status]);
    }

    // Records a payment (when an amount is given) before confirming the booking,
    // so admins can settle the balance due as part of the confirmation step.
    public function recordPaymentAndConfirm(array $paymentData): void
    {
        if ((float) ($paymentData['amount'] ?? 0) > 0) {
            $this->payments()->create([
                'payment_method' => $paymentData['payment_method'],
                'amount' => $paymentData['amount'],
                'reference' => $paymentData['reference'] ?? null,
                'notes' => $paymentData['notes'] ?? null,
                'recorded_by' => auth()->id(),
            ]);
        }

        $this->confirm();
    }

    // $notify = false confirms without emailing the customer (used when repairing
    // old bookings whose event may already be over).
    public function confirm(bool $notify = true)
    {
        $this->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        $this->applyConfirmationCounters();

        if ($notify) {
            $this->sendConfirmationNotifications();
        }
    }

    // Capacity counters and promo usage that a confirmation is responsible for.
    // Split out of confirm() so a booking that was flipped to "confirmed" by
    // editing its status directly (which skips all of this) can be brought back
    // in line without re-sending anything.
    public function applyConfirmationCounters(): void
    {
        $this->timeSlot->increment('current_bookings', $this->quantity);
        $this->adjustTicketTypeSold(1);

        foreach ($this->extraServices as $service) {
            $service->increment('quantity_used', $service->pivot->quantity);
        }

        if ($this->promo_code_id) {
            app(PromoCodeService::class)->recordUsage($this);
        }
    }

    // Moves each ticket type's quantity_sold by its own attendee count, so a
    // mixed booking doesn't charge every ticket to its first (primary) type.
    // Bookings without attendee rows fall back to the primary type.
    protected function adjustTicketTypeSold(int $direction): void
    {
        $counts = $this->attendees()->whereNotNull('ticket_type_id')
            ->selectRaw('ticket_type_id, COUNT(*) as aggregate')
            ->groupBy('ticket_type_id')
            ->pluck('aggregate', 'ticket_type_id');

        if ($counts->isEmpty()) {
            $counts = collect([$this->ticket_type_id => $this->quantity]);
        }

        foreach ($counts as $typeId => $count) {
            TicketType::whereKey($typeId)->increment('quantity_sold', $direction * $count);
        }
    }

    public function sendConfirmationNotifications(): void
    {
        // Send booking confirmation to the primary attendee. A mail failure must
        // not undo the confirmation (the payment has already been taken).
        $primary = $this->attendees->first();
        if ($primary && ! empty($primary->email)) {
            try {
                Mail::to($primary->email)->send(new BookingConfirmation($this));
            } catch (Exception $e) {
                Log::error('Booking confirmation email failed', [
                    'booking_id' => $this->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Ticket assets are generated asynchronously (see BookingAttendee::boot())
        // and may not be ready yet, so chain the generation jobs ahead of the send —
        // each generation step is a no-op if its asset already exists.
        $jobs = $this->attendees
            ->map(fn (BookingAttendee $attendee) => new GenerateAttendeeTicketAssets($attendee->id))
            ->push(new SendBookingTickets($this->id))
            ->all();

        DB::afterCommit(fn () => Bus::chain($jobs)->dispatch());
    }

    // Send every attendee's PDF ticket and QR code in a single email to the
    // first attendee, instead of one email per attendee, to stay well under
    // the mail provider's message submission rate limit.
    public function sendAllTickets(): bool
    {
        $primary = $this->attendees->first();

        if (! $primary || empty($primary->email)) {
            return false;
        }

        try {
            Mail::to($primary->email)->send(new AllTickets($this));

            foreach ($this->attendees as $attendee) {
                $attendee->update([
                    'email_sent' => true,
                    'email_sent_at' => now(),
                ]);
            }

            return true;
        } catch (Exception $e) {
            Log::error('Ticket Email Send Failed', [
                'booking_id' => $this->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    // $source records why the booking was cancelled (customer | expired |
    // gateway | admin | error) — see isSystemCancelled().
    public function cancel(?string $source = null)
    {
        if ($this->status === 'confirmed') {
            $this->timeSlot->decrement('current_bookings', $this->quantity);
            $this->adjustTicketTypeSold(-1);

            foreach ($this->extraServices as $service) {
                $service->decrement('quantity_used', $service->pivot->quantity);
            }
        }

        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_source' => $source,
        ]);
    }

    // True when the system (not an admin) cancelled this online booking, so a
    // later "paid" report from the gateway means the customer genuinely completed
    // checkout and the booking should be revived. Only bookings cancelled since
    // cancellation_source was introduced carry a source; older cancellations have
    // none and are deliberately left alone.
    public function isSystemCancelled(): bool
    {
        return $this->status === 'cancelled'
            && in_array($this->cancellation_source, ['customer', 'expired', 'gateway'], true);
    }

    public function refund(?string $reason = null): void
    {
        if ($this->status === 'confirmed') {
            $this->timeSlot->decrement('current_bookings', $this->quantity);
            $this->adjustTicketTypeSold(-1);

            foreach ($this->extraServices as $service) {
                $service->decrement('quantity_used', $service->pivot->quantity);
            }
        }

        $this->update([
            'status' => 'refunded',
            'refunded_at' => now(),
            'refunded_by' => auth()->id(),
            'refund_reason' => $reason,
        ]);
    }

    // Parses the stored user agent with matomo/device-detector into a display-ready
    // breakdown of device, OS, and client info, or a bot flag if the UA is a known bot.
    public function getDeviceInfo(): array
    {
        if (empty($this->user_agent)) {
            return ['available' => false];
        }

        $dd = new DeviceDetector($this->user_agent);
        $dd->parse();

        if ($dd->isBot()) {
            $bot = $dd->getBot() ?: [];

            return [
                'available' => true,
                'is_bot' => true,
                'bot_name' => $bot['name'] ?? null,
                'bot_category' => $bot['category'] ?? null,
                'bot_producer' => $bot['producer']['name'] ?? null,
            ];
        }

        $client = $dd->getClient() ?: [];
        $os = $dd->getOs() ?: [];

        return [
            'available' => true,
            'is_bot' => false,
            'device_type' => $dd->getDeviceName() ?: null,
            'brand' => $dd->getBrandName() ?: null,
            'model' => $dd->getModel() ?: null,
            'is_mobile' => $dd->isMobile(),
            'is_desktop' => $dd->isDesktop(),
            'client_type' => $client['type'] ?? null,
            'client_name' => $client['name'] ?? null,
            'client_version' => $client['version'] ?? null,
            'client_engine' => $client['engine'] ?? null,
            'client_engine_version' => $client['engine_version'] ?? null,
            'os_name' => $os['name'] ?? null,
            'os_version' => $os['version'] ?? null,
            'os_platform' => $os['platform'] ?? null,
        ];
    }

    // Base64-encoded PNG QR code that links to the public booking verification page.
    public function getSummaryQrCodeBase64(): string
    {
        $result = (new Builder(
            writer: new PngWriter,
            data: route('booking.success', $this->booking_reference),
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: 220,
            margin: 8,
            roundBlockSizeMode: RoundBlockSizeMode::Margin
        ))->build();

        return 'data:image/png;base64,'.base64_encode($result->getString());
    }

    // Base64-encoded Code128 barcode of the booking reference, printed on each attendee ticket receipt.
    public function getBookingReferenceBarcodeBase64(): string
    {
        $barcode = (new BarcodeGeneratorPNG)->getBarcode(
            $this->booking_reference,
            BarcodeGenerator::TYPE_CODE_128,
            2,
            60
        );

        return 'data:image/png;base64,'.base64_encode($barcode);
    }

    // Base64-encoded ticket header artwork used as the summary PDF banner background.
    // Cached since this static file is read and re-encoded once per attendee
    // ticket (queued one job per attendee) plus once per summary PDF.
    public function getHeaderBackgroundBase64(): ?string
    {
        return Cache::remember('ticket_header_background_base64', now()->addDay(), function () {
            $path = storage_path('app/public/avatars/MasarTicket.png');

            if (! is_file($path)) {
                return null;
            }

            return 'data:image/png;base64,'.base64_encode(file_get_contents($path));
        });
    }

    // Renders the booking summary PDF with mPDF and returns the raw PDF binary.
    public function generateSummaryPdf(): ?string
    {
        try {
            $booking = $this->load(['event', 'timeSlot', 'ticketType', 'attendees.ticketType', 'extraServices']);

            $html = view('bookings.summary-pdf', [
                'booking' => $booking,
                'qrCode' => $booking->getSummaryQrCodeBase64(),
                'headerBg' => $this->getHeaderBackgroundBase64(),
            ])->render();

            $mpdf = new Mpdf(array_merge([
                'mode' => 'utf-8',
                'format' => 'A4',
                'margin_left' => 12,
                'margin_right' => 12,
                'margin_top' => 12,
                'margin_bottom' => 15,
                'default_font' => 'almarai',
            ], MpdfAlmarai::config()));

            // mPDF's line-box layout code emits benign "Undefined array key"
            // PHP warnings for certain content combinations (e.g. list/dottab
            // markers) even though rendering completes successfully. Laravel
            // promotes uncaught warnings to ErrorException, which would abort
            // generation here, so they're suppressed around the mPDF calls.
            @$mpdf->WriteHTML($html);

            return @$mpdf->Output('booking-'.$this->booking_reference.'.pdf', Destination::STRING_RETURN);
        } catch (Exception $e) {
            Log::error('PDF Generation Failed', [
                'booking_id' => $this->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
