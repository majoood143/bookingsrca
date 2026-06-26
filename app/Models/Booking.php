<?php

namespace App\Models;

use Exception;
use App\Mail\BookingConfirmation;
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

class Booking extends Model
{
    use HasFactory;

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
        'status',
        'confirmed_at',
        'cancelled_at',
        // Note: attendee_id was removed from bookings table by create_booking_attendees migration
        // Attendee info is stored in booking_attendees table instead
        'payment_method',
        'payment_status',
        'payment_session_id',
        'payment_reference',
    ];

    protected $casts = [
        'event_date' => 'date',
        'ticket_price' => 'decimal:2',
        'services_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

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

        // Send individual ticket email to every attendee
        foreach ($this->attendees as $attendee) {
            $attendee->sendTicketEmail();
        }

        // Send booking confirmation to the primary attendee
        $primary = $this->attendees->first();
        if ($primary && !empty($primary->email)) {
            Mail::to($primary->email)->send(new BookingConfirmation($this));
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

            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'margin_left' => 12,
                'margin_right' => 12,
                'margin_top' => 12,
                'margin_bottom' => 15,
                'default_font' => 'dejavusans',
            ]);
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