<?php

namespace App\Services\Printing;

use RuntimeException;
use Throwable;
use App\Models\Booking;
use App\Models\BookingAttendee;
use App\Models\BookingSetting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\Printer;
use Spatie\Browsershot\Browsershot;

class AttendeeTicketPrintService
{
    private PrintConnectorFactory $connectorFactory;
    private bool $enabled;
    private int  $paperWidthDots;
    private bool $graphicsMode;

    public function __construct()
    {
        $this->connectorFactory = new PrintConnectorFactory();
        $this->enabled          = (bool) BookingSetting::get('printer.enabled', false);
        $this->paperWidthDots   = (int)  BookingSetting::get('printer.paper_width_dots', 576);
        $this->graphicsMode     = (bool) BookingSetting::get('printer.graphics_mode', false);
    }

    /**
     * Render and print one ticket per attendee on the booking, cutting
     * between each ticket and fully cutting after the last one.
     */
    public function printAttendeeTickets(Booking $booking): void
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

        $printer      = new Printer($this->connectorFactory->make());
        $printed      = 0;
        $currentPath  = null;

        try {
            foreach ($attendees as $attendee) {
                $currentPath = $this->renderAttendeeTicketToPng($booking, $attendee, $tmpDir);

                $image = EscposImage::load(Storage::disk('local')->path($currentPath));

                $printer->setJustification(Printer::JUSTIFY_CENTER);

                if ($this->graphicsMode) {
                    $printer->graphics($image);
                } else {
                    $printer->bitImage($image);
                }

                Storage::disk('local')->delete($currentPath);
                $currentPath = null;

                $printed++;

                $isLast = $printed === $attendees->count();
                $printer->feed(2);
                $printer->cut($isLast ? Printer::CUT_FULL : Printer::CUT_PARTIAL);
            }
        } catch (Throwable $e) {
            throw new RuntimeException(
                "Printed {$printed} of {$attendees->count()} tickets — {$e->getMessage()}",
                previous: $e
            );
        } finally {
            if ($currentPath !== null) {
                Storage::disk('local')->delete($currentPath);
            }
            $printer->close();
        }
    }

    /**
     * Print a short connectivity test line, independent of Browsershot/imaging.
     */
    public function sendTestPrint(): void
    {
        $printer = new Printer($this->connectorFactory->make());

        try {
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text(config('app.name') . "\n");
            $printer->text("Test print — " . now()->format('Y-m-d H:i:s') . "\n");
            $printer->feed(2);
            $printer->cut(Printer::CUT_FULL);
        } finally {
            $printer->close();
        }
    }

    private function renderAttendeeTicketToPng(Booking $booking, BookingAttendee $attendee, string $tmpDir): string
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
}
