<?php

namespace App\Services;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class InvoiceService
{
    public function generate(Order $order): string
    {
        $pdf = Pdf::loadView('invoices.template', [
            'order' => $order->load(['user', 'items.product', 'items.variant']),
        ]);

        $filename = 'invoices/' . $order->order_number . '.pdf';
        Storage::put($filename, $pdf->output());

        $order->update(['invoice_path' => $filename]);

        return $filename;
    }
}
