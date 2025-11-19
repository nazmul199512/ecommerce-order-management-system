<?php

// app/Services/OrderService.php
namespace App\Services;

use App\Actions\Order\CreateOrderAction;
use App\Actions\Order\CancelOrderAction;
use App\Actions\Order\UpdateOrderStatusAction;
use App\Models\Order;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderService
{
    public function __construct(
        private CreateOrderAction $createOrderAction,
        private CancelOrderAction $cancelOrderAction,
        private UpdateOrderStatusAction $updateOrderStatusAction
    ) {}

    public function createOrder(User $user, array $data): Order
    {
        return $this->createOrderAction->execute($user, $data);
    }

    public function getOrders(array $filters = []): LengthAwarePaginator
    {
        $query = Order::with(['user', 'items.product', 'items.variant']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        return $query->latest()->paginate(config('app.pagination_per_page', 15));
    }

    public function cancelOrder(Order $order): Order
    {
        return $this->cancelOrderAction->execute($order);
    }

    public function updateStatus(Order $order, string $status): Order
    {
        return $this->updateOrderStatusAction->execute($order, $status);
    }
}
