<?php

namespace App\Actions\Order;

use App\Events\OrderStatusUpdated;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class UpdateOrderStatusAction
{
    private array $allowedTransitions = [
        'pending' => ['processing', 'cancelled'],
        'processing' => ['shipped', 'cancelled'],
        'shipped' => ['delivered'],
        'delivered' => [],
        'cancelled' => [],
    ];

    public function execute(Order $order, string $newStatus): Order
    {
        if (!$this->canTransitionTo($order->status, $newStatus)) {
            throw new \InvalidArgumentException(
                "Cannot transition from {$order->status} to {$newStatus}"
            );
        }

        return DB::transaction(function () use ($order, $newStatus) {
            $oldStatus = $order->status;
            $order->update(['status' => $newStatus]);

            event(new OrderStatusUpdated($order, $oldStatus, $newStatus));

            return $order->fresh();
        });
    }

    private function canTransitionTo(string $currentStatus, string $newStatus): bool
    {
        return in_array($newStatus, $this->allowedTransitions[$currentStatus] ?? []);
    }
}
