<?php

namespace App\Listeners;

use App\Events\OrderStatusUpdated;
use App\Jobs\SendOrderNotification;

class NotifyOrderStatusChange
{
    public function handle(OrderStatusUpdated $event): void
    {
        SendOrderNotification::dispatch($event->order, 'status_updated');
    }
}
