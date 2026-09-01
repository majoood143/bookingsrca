<?php

namespace App\Mail;

use App\Models\PosOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Spatie\LaravelPdf\Facades\Pdf;

class PosOrderReceipt extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public PosOrder $order;

    public function __construct(PosOrder $order)
    {
        $this->order = $order->loadMissing('items.product');
    }

    public function build()
    {
        $locale = app()->getLocale();
        $filename = __('pos.order.receipt.filename', ['number' => $this->order->order_number], $locale) . '.pdf';

        $pdf = Pdf::view('reports.pos-order-receipt-pdf', [
            'order' => $this->order,
            'locale' => $locale,
        ])->format('a4');

        return $this->locale($locale)
            ->subject(__('pos.order.receipt.subject', ['number' => $this->order->order_number], $locale))
            ->view('emails.pos-order-receipt')
            ->with(['order' => $this->order, 'locale' => $locale])
            ->attachData($pdf->generatePdfContent(), $filename, ['mime' => 'application/pdf']);
    }
}
