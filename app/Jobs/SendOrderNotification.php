<?php

namespace App\Jobs;

use App\Mail\OrderConfirmation;
use App\Mail\OrderStatusUpdated;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendOrderNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $type
    ) {}

    public function handle(): void
    {
        $mailable = match ($this->type) {
            'order_created' => new OrderConfirmation($this->order),
            'status_updated' => new OrderStatusUpdated($this->order),
            default => null,
        };

        if ($mailable) {
            Mail::to($this->order->user->email)->send($mailable);
        }
    }
}
