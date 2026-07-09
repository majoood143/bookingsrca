<?php

namespace App\Models;

use Exception;
use App\Mail\IndividualTicket;
use App\Support\MpdfAlmarai;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Endroid\QrCode\Builder\Builder;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class BookingAttendee extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'booking_id',
        'ticket_type_id',  // Add this
        'ticket_price',    // Add this
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'gender',
        'nationality',
        'identity_number',
        'ticket_number',
        'qr_code',
        'pdf_path',
        'email_sent',
        'email_sent_at',
        'checked_in',
        'checked_in_at',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'ticket_price' => 'decimal:2',  // Add this
        'email_sent' => 'boolean',
        'email_sent_at' => 'datetime',
        'checked_in' => 'boolean',
        'checked_in_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['booking_id', 'ticket_type_id', 'first_name', 'last_name', 'email', 'phone', 'checked_in'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($attendee) {
            if (empty($attendee->ticket_number)) {
                $attendee->ticket_number = 'TKT-' . strtoupper(Str::random(10));
            }
        });

        static::created(function ($attendee) {
            $attendee->generateQrCode();
            $attendee->generatePdfTicket();
        });
    }

    // Relationships
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    // Add this relationship
    public function ticketType()
    {
        return $this->belongsTo(TicketType::class);
    }

    // Helper methods
    public function getFullName()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    // Generate individual QR code
    public function generateQrCode()
    {
        try {
            // $qrData = json_encode([
            //     'ticket_number' => $this->ticket_number,
            //     'booking_ref' => $this->booking->booking_reference,
            //     'attendee' => $this->getFullName(),
            //     'event' => $this->booking->event->getTranslation('title', 'en'),
            //     'date' => $this->booking->event_date->format('Y-m-d'),
            //     'time' => $this->booking->timeSlot->getTimeRange(),
            // ]);

            $qrData = $this->booking->booking_reference;

            // $result = Builder::create()
            //     ->writer(new PngWriter())
            //     ->data($qrData)
            //     ->encoding(new Encoding('UTF-8'))
            //     ->errorCorrectionLevel(ErrorCorrectionLevel::Low)
            //     ->size(300)
            //     ->margin(10)
            //     ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
            //     ->build();

            $result = (new Builder(
                writer: new PngWriter(),
                data: $qrData,
                encoding: new Encoding('UTF-8'),
                errorCorrectionLevel: ErrorCorrectionLevel::Low,
                size: 300,
                margin: 10,
                roundBlockSizeMode: RoundBlockSizeMode::Margin
            ))->build();

            $qrCodePath = 'qr-codes/' . $this->ticket_number . '.png';
            Storage::disk('public')->put($qrCodePath, $result->getString());

            $this->update(['qr_code' => $qrCodePath]);

            return true;
        } catch (Exception $e) {
            Log::error('QR Code Generation Failed', [
                'attendee_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function getQrCodeUrl(): ?string
    {
        return $this->qr_code ? asset('storage/' . $this->qr_code) : null;
    }

    // Base64-encoded QR code image, used so mPDF can embed it without filesystem path lookups.
    public function getQrCodeBase64(): ?string
    {
        if (! $this->qr_code || ! Storage::disk('public')->exists($this->qr_code)) {
            return null;
        }

        return 'data:image/png;base64,' . base64_encode(Storage::disk('public')->get($this->qr_code));
    }

    // Generate individual PDF ticket
    public function generatePdfTicket()
    {
        try {
            $locale = app()->getLocale();

            $html = view('tickets.individual', [
                'attendee' => $this,
                'booking'  => $this->booking,
                'qrCode'   => $this->getQrCodeBase64(),
                'headerBg' => $this->booking->getHeaderBackgroundBase64(),
                'locale'   => $locale,
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

            if ($locale === 'ar') {
                MpdfAlmarai::applyArabicSettings($mpdf);
            }

            $mpdf->WriteHTML($html);

            $pdfPath = 'tickets/' . $this->ticket_number . '.pdf';
            Storage::disk('public')->put($pdfPath, $mpdf->Output($pdfPath, Destination::STRING_RETURN));

            $this->update(['pdf_path' => $pdfPath]);

            return true;
        } catch (Exception $e) {
            Log::error('PDF Generation Failed', [
                'attendee_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function getPdfUrl()
    {
        return $this->pdf_path ? asset('storage/' . $this->pdf_path) : null;
    }

    // Send individual ticket email
    // public function sendTicketEmail()
    // {
    //     try {
    //         Mail::to($this->email)->send(new \App\Mail\IndividualTicket($this));

    //         $this->update([
    //             'email_sent' => true,
    //             'email_sent_at' => now(),
    //         ]);

    //         return true;
    //     } catch (\Exception $e) {
    //         Log::error('Email Send Failed', [
    //             'attendee_id' => $this->id,
    //             'error' => $e->getMessage()
    //         ]);
    //         return false;
    //     }
    // }

    public function sendTicketEmail()
    {
        if (empty($this->email)) {
            return false;
        }

        try {
            Mail::to($this->email)->send(new IndividualTicket($this));

            $this->update([
                'email_sent' => true,
                'email_sent_at' => now(),
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Email Send Failed', [
                'attendee_id' => $this->id,
                'email' => $this->email,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function resendTicketEmail(): bool
    {
        return $this->sendTicketEmail();
    }

    // Check in attendee
    public function checkIn()
    {
        $this->update([
            'checked_in' => true,
            'checked_in_at' => now(),
        ]);
    }

    // Bidirectional check-in state, used by the scan-based check-in page's toggle
    // switches. checkIn() above stays one-way for the existing attendee-list modal.
    public function setCheckedIn(bool $state): void
    {
        $this->update([
            'checked_in' => $state,
            'checked_in_at' => $state ? now() : null,
        ]);
    }
}