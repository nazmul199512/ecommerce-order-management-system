<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Jobs\GenerateInvoice;

class GenerateInvoicePdf
{
    public function handle(OrderCreated $event): void
    {
        // Generate invoice after a short delay
        GenerateInvoice::dispatch($event->order)->delay(now()->addMinutes(2));
    }
}
