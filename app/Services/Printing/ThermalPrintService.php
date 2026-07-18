<?php

namespace App\Services\Printing;

use RuntimeException;
use Throwable;
use App\Models\Booking;
use App\Models\BookingAttendee;
use App\Models\BookingSetting;
use App\Models\PrintJob;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\MemoryPrintConnector;
use Spatie\Browsershot\Browsershot;

// The app is cloud-hosted while the thermal printer sits on a private, on-site
// LAN, so this service never talks to the printer directly — it renders the
// ESC/POS byte stream and queues it as a PrintJob. A separate on-site agent
// (see print-agent/) polls for pending jobs and delivers them locally.
class ThermalPrintService
{
    private bool $enabled;
    private int  $paperWidthDots;
    private bool $graphicsMode;

    public function __construct()
    {
        $this->enabled        = (bool) BookingSetting::get('printer.enabled', false);
        $this->paperWidthDots = (int)  BookingSetting::get('printer.paper_width_dots', 576);
        $this->graphicsMode   = (bool) BookingSetting::get('printer.graphics_mode', false);
    }

    /**
     * Render one ticket per attendee and queue the raw ESC/POS bytes for the
     * on-site print agent to deliver, cutting between each ticket and fully
     * cutting after the last one.
     */
    public function enqueueAttendeeTickets(Booking $booking): PrintJob
    {
        if (!$this->enabled) {
            throw new RuntimeException('Thermal printing is currently disabled in Printer Settings.');
        }

        $booking->loadMissing(['event', 'timeSlot', 'attendees.ticketType']);

        $attendees = $booking->attendees;

        if ($attendees->isEmpty()) {
            throw new RuntimeException('This booking has no attendees to print tickets for.');
        }

        $tmpDir = 'printing/tmp';
        Storage::disk('local')->makeDirectory($tmpDir);

        $connector   = new MemoryPrintConnector();
        $printer     = new Printer($connector);
        $rendered    = 0;
        $currentPath = null;
        $bytes       = '';
        $logo        = $this->logoDataUri();

        try {
            foreach ($attendees as $attendee) {
                $currentPath = $this->renderAttendeeTicketToPng($booking, $attendee, $tmpDir, $logo);

                $image = EscposImage::load(Storage::disk('local')->path($currentPath));

                $printer->setJustification(Printer::JUSTIFY_CENTER);

                if ($this->graphicsMode) {
                    $printer->graphics($image);
                } else {
                    $printer->bitImage($image);
                }

                Storage::disk('local')->delete($currentPath);
                $currentPath = null;

                $rendered++;

                $isLast = $rendered === $attendees->count();
                $printer->feed(2);
                $printer->cut($isLast ? Printer::CUT_FULL : Printer::CUT_PARTIAL);
            }

            $bytes = $connector->getData();
        } catch (Throwable $e) {
            throw new RuntimeException(
                "Rendered {$rendered} of {$attendees->count()} tickets — {$e->getMessage()}",
                previous: $e
            );
        } finally {
            if ($currentPath !== null) {
                Storage::disk('local')->delete($currentPath);
            }
            $printer->close();
        }

        return $this->queue($booking, 'attendee_tickets', $bytes);
    }

    /**
     * Render the whole-booking summary/payment receipt as a single document
     * and queue the raw ESC/POS bytes for the on-site print agent to deliver.
     */
    public function enqueueReceipt(Booking $booking): PrintJob
    {
        if (!$this->enabled) {
            throw new RuntimeException('Thermal printing is currently disabled in Printer Settings.');
        }

        $booking->loadMissing(['event', 'timeSlot', 'ticketType', 'attendees', 'extraServices', 'payments']);

        $tmpDir = 'printing/tmp';
        Storage::disk('local')->makeDirectory($tmpDir);

        $connector   = new MemoryPrintConnector();
        $printer     = new Printer($connector);
        $currentPath = null;
        $bytes       = '';

        try {
            $currentPath = $this->renderReceiptToPng($booking, $tmpDir);

            $image = EscposImage::load(Storage::disk('local')->path($currentPath));

            $printer->setJustification(Printer::JUSTIFY_CENTER);

            if ($this->graphicsMode) {
                $printer->graphics($image);
            } else {
                $printer->bitImage($image);
            }

            Storage::disk('local')->delete($currentPath);
            $currentPath = null;

            $printer->feed(2);
            $printer->cut(Printer::CUT_FULL);

            $bytes = $connector->getData();
        } catch (Throwable $e) {
            throw new RuntimeException("Could not render receipt — {$e->getMessage()}", previous: $e);
        } finally {
            if ($currentPath !== null) {
                Storage::disk('local')->delete($currentPath);
            }
            $printer->close();
        }

        return $this->queue($booking, 'receipt', $bytes);
    }

    /**
     * Queue a short connectivity test line, independent of Browsershot/imaging
     * — lets "Send Test Print" validate the queue/agent path cheaply, without
     * needing a real booking.
     */
    public function sendTestPrint(): PrintJob
    {
        $connector = new MemoryPrintConnector();
        $printer   = new Printer($connector);
        $bytes     = '';

        try {
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text(config('app.name') . "\n");
            $printer->text("Test print - " . now()->format('Y-m-d H:i:s') . "\n");
            $printer->feed(2);
            $printer->cut(Printer::CUT_FULL);
            $bytes = $connector->getData();
        } finally {
            $printer->close();
        }

        return $this->queue(null, 'test_print', $bytes);
    }

    private function queue(?Booking $booking, string $type, string $bytes): PrintJob
    {
        Storage::disk('local')->makeDirectory('print-jobs');

        $relativePath = 'print-jobs/' . Str::uuid() . '.bin';
        Storage::disk('local')->put($relativePath, $bytes);

        return PrintJob::create([
            'booking_id'   => $booking?->id,
            'type'         => $type,
            'status'       => 'pending',
            'payload_path' => $relativePath,
        ]);
    }

    /**
     * Browsershot renders this HTML from a local temp file with no web
     * server behind it, so any <img> pointing at an asset()/HTTP URL would
     * force Chrome to make a real HTTP request back into this same app to
     * fetch it — which deadlocks on a single-worker dev server (the request
     * rendering the ticket is the only one that could ever answer it) and
     * fails after Puppeteer's navigation timeout. Every image must be
     * self-contained as a data URI instead, same as the barcode already is.
     */
    private function logoDataUri(): string
    {
        $path = storage_path('app/public/images/horizontalLogo-02.svg');

        if (!is_file($path)) {
            return '';
        }

        return 'data:image/svg+xml;base64,' . base64_encode(file_get_contents($path));
    }

    private function renderAttendeeTicketToPng(Booking $booking, BookingAttendee $attendee, string $tmpDir, string $logo): string
    {
        $locale         = app()->getLocale();
        $isRtl          = $locale === 'ar';
        $t              = fn (string $key, array $replace = []) => trans("event_booking.ticket.$key", $replace, $locale);
        $dateFormatted  = $isRtl
            ? $booking->event_date->locale('ar')->translatedFormat('l، j F Y')
            : $booking->event_date->format('l, F j, Y');
        $barcode        = $booking->getBookingReferenceBarcodeBase64();

        $html = view('bookings.attendee-ticket-thermal', [
            'booking'       => $booking,
            'attendee'      => $attendee,
            'locale'        => $locale,
            'isRtl'         => $isRtl,
            't'             => $t,
            'dateFormatted' => $dateFormatted,
            'barcode'       => $barcode,
            'logo'          => $logo,
            'paperWidth'    => $this->paperWidthDots,
        ])->render();

        $relativePath = $tmpDir . '/booking-' . $booking->id . '-attendee-' . $attendee->id . '-' . Str::random(8) . '.png';
        $absolutePath = Storage::disk('local')->path($relativePath);

        Browsershot::html($html)
            ->windowSize($this->paperWidthDots, 100)
            ->fullPage()
            ->save($absolutePath);

        return $relativePath;
    }

    private function renderReceiptToPng(Booking $booking, string $tmpDir): string
    {
        $locale = app()->getLocale();
        $isRtl  = $locale === 'ar';

        $html = view('bookings.receipt-thermal', [
            'booking'    => $booking,
            'locale'     => $locale,
            'isRtl'      => $isRtl,
            'paperWidth' => $this->paperWidthDots,
        ])->render();

        $relativePath = $tmpDir . '/receipt-' . $booking->id . '-' . Str::random(8) . '.png';
        $absolutePath = Storage::disk('local')->path($relativePath);

        Browsershot::html($html)
            ->windowSize($this->paperWidthDots, 100)
            ->fullPage()
            ->save($absolutePath);

        return $relativePath;
    }
}
