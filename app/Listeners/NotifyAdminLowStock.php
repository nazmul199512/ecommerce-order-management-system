<?php

namespace App\Listeners;

use App\Events\LowStockDetected;
use App\Mail\LowStockAlert;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class NotifyAdminLowStock
{
    public function handle(LowStockDetected $event): void
    {
        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            Mail::to($admin->email)->queue(new LowStockAlert($event->inventory));
        }
    }
}
