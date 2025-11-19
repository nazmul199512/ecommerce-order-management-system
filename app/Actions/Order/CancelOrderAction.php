<?php

namespace App\Actions\Order;

use App\Events\OrderCancelled;
use App\Models\Order;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CancelOrderAction
{
    public function __construct(
        private InventoryService $inventoryService
    ) {}

    public function execute(Order $order): Order
    {
        if (!$order->canBeCancelled()) {
            throw ValidationException::withMessages([
                'order' => ['Order cannot be cancelled in current status'],
            ]);
        }

        return DB::transaction(function () use ($order) {
            // Restore inventory
            foreach ($order->items as $item) {
                $this->inventoryService->restoreStock(
                    $item->product_id,
                    $item->variant_id,
                    $item->quantity,
                    $order
                );
            }

            // Update order status
            $order->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            event(new OrderCancelled($order));

            return $order->fresh();
        });
    }
}
