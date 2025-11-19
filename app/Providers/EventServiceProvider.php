<?php

namespace App\Providers;

use App\Events\LowStockDetected;
use App\Events\OrderCancelled;
use App\Events\OrderCreated;
use App\Events\OrderStatusUpdated;
use App\Listeners\GenerateInvoicePdf;
use App\Listeners\NotifyAdminLowStock;
use App\Listeners\NotifyOrderStatusChange;
use App\Listeners\SendOrderConfirmationEmail;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        OrderCreated::class => [
            SendOrderConfirmationEmail::class,
            GenerateInvoicePdf::class,
        ],
        OrderStatusUpdated::class => [
            NotifyOrderStatusChange::class,
        ],
        LowStockDetected::class => [
            NotifyAdminLowStock::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}