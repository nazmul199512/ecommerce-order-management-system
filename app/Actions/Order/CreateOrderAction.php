<?php

namespace App\Actions\Order;

use App\Events\OrderCreated;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateOrderAction
{
    public function __construct(
        private InventoryService $inventoryService
    ) {}

    public function execute(User $user, array $data): Order
    {
        return DB::transaction(function () use ($user, $data) {
            // Validate and reserve inventory
            $items = $this->validateAndReserveInventory($data['items']);

            // Calculate totals
            $subtotal = 0;
            foreach ($items as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }

            $tax = $subtotal * 0.1; // 10% tax
            $total = $subtotal + $tax;

            // Create order
            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'user_id' => $user->id,
                'status' => 'pending',
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total_amount' => $total,
                'shipping_address' => $data['shipping_address'],
                'notes' => $data['notes'] ?? null,
            ]);

            // Create order items
            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity'],
                ]);

                // Deduct from inventory
                $this->inventoryService->deductStock(
                    $item['product_id'],
                    $item['variant_id'],
                    $item['quantity'],
                    $order
                );
            }

            event(new OrderCreated($order));

            return $order->load(['items.product', 'items.variant']);
        });
    }

    private function validateAndReserveInventory(array $items): array
    {
        $validated = [];

        foreach ($items as $item) {
            $product = Product::with('inventory')->findOrFail($item['product_id']);

            if (!$product->is_active) {
                throw ValidationException::withMessages([
                    'items' => ["Product {$product->name} is not available"],
                ]);
            }

            $variantId = $item['variant_id'] ?? null;
            $price = $variantId
                ? $product->variants()->findOrFail($variantId)->price
                : $product->base_price;

            // Check inventory
            $inventory = $product->inventory;
            if (!$inventory || $inventory->available_quantity < $item['quantity']) {
                throw ValidationException::withMessages([
                    'items' => ["Insufficient stock for {$product->name}"],
                ]);
            }

            $validated[] = [
                'product_id' => $product->id,
                'variant_id' => $variantId,
                'quantity' => $item['quantity'],
                'price' => $price,
            ];
        }

        return $validated;
    }

    private function generateOrderNumber(): string
    {
        return 'ORD-' . strtoupper(uniqid());
    }
}
