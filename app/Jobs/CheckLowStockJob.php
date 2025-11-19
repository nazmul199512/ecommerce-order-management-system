<?php

namespace App\Jobs;

use App\Events\LowStockDetected;
use App\Models\Inventory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckLowStockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Inventory::where('quantity', '<=', \DB::raw('low_stock_threshold'))
            ->whereHas('product', function ($query) {
                $query->where('is_active', true);
            })
            ->chunk(100, function ($inventories) {
                foreach ($inventories as $inventory) {
                    event(new LowStockDetected($inventory));
                }
            });
    }
}
