<?php

namespace App\Models;

use Exception;
use App\Mail\AllTickets;
use App\Mail\BookingConfirmation;
use App\Support\MpdfAlmarai;
use DeviceDetector\DeviceDetector;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
        'source',
        'created_by',
        'kiosk_id',
        'status',
        'confirmed_at',
        'cancelled_at',
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
        'ticket_price' => 'decimal:2',
        'services_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'status', 'payment_status', 'payment_method',
                'quantity', 'ticket_price', 'services_price', 'total_price',
                'event_date', 'source', 'locale', 'confirmed_at', 'cancelled_at',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->booking_reference)) {
                $booking->booking_reference = 'BK-' . strtoupper(Str::random(8));
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

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function kiosk()
    {
        return $this->belongsTo(Kiosk::class);
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

    // Updated confirm method
    public function confirm()
    {
        $this->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        $this->timeSlot->increment('current_bookings', $this->quantity);
        $this->ticketType->increment('quantity_sold', $this->quantity);

        foreach ($this->extraServices as $service) {
            $service->increment('quantity_used', $service->pivot->quantity);
        }

        $this->sendAllTickets();

        // Send booking confirmation to the primary attendee
        $primary = $this->attendees->first();
        if ($primary && !empty($primary->email)) {
            Mail::to($primary->email)->send(new BookingConfirmation($this));
        }
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

    public function cancel()
    {
        if ($this->status === 'confirmed') {
            $this->timeSlot->decrement('current_bookings', $this->quantity);
            $this->ticketType->decrement('quantity_sold', $this->quantity);

            foreach ($this->extraServices as $service) {
                $service->decrement('quantity_used', $service->pivot->quantity);
            }
        }

        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
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
            writer: new PngWriter(),
            data: route('booking.success', $this->booking_reference),
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: 220,
            margin: 8,
            roundBlockSizeMode: RoundBlockSizeMode::Margin
        ))->build();

        return 'data:image/png;base64,' . base64_encode($result->getString());
    }

    // Base64-encoded Code128 barcode of the booking reference, printed on each attendee ticket receipt.
    public function getBookingReferenceBarcodeBase64(): string
    {
        $barcode = (new BarcodeGeneratorPNG())->getBarcode(
            $this->booking_reference,
            BarcodeGenerator::TYPE_CODE_128,
            2,
            60
        );

        return 'data:image/png;base64,' . base64_encode($barcode);
    }

    // Base64-encoded ticket header artwork used as the summary PDF banner background.
    public function getHeaderBackgroundBase64(): ?string
    {
        $path = storage_path('app/public/avatars/MasarTicket.png');

        if (! is_file($path)) {
            return null;
        }

        return 'data:image/png;base64,' . base64_encode(file_get_contents($path));
    }

    // Renders the booking summary PDF with mPDF and returns the raw PDF binary.
    public function generateSummaryPdf(): ?string
    {
        try {
            $booking = $this->load(['event', 'timeSlot', 'ticketType', 'attendees', 'extraServices']);

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
            $mpdf->WriteHTML($html);

            return $mpdf->Output('booking-' . $this->booking_reference . '.pdf', Destination::STRING_RETURN);
        } catch (Exception $e) {
            Log::error('PDF Generation Failed', [
                'booking_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}