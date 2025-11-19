<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Jobs\SendOrderNotification;

class SendOrderConfirmationEmail
{
    public function handle(OrderCreated $event): void
    {
        SendOrderNotification::dispatch($event->order, 'order_created');
    }
}
