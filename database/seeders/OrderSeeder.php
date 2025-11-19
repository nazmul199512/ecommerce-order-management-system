<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::where('role', 'customer')->get();
        $products = Product::with('inventory')->get();

        foreach ($customers->take(5) as $customer) {
            // Create 2-3 orders per customer
            for ($i = 0; $i < rand(2, 3); $i++) {
                $status = ['pending', 'processing', 'shipped', 'delivered'][array_rand(['pending', 'processing', 'shipped', 'delivered'])];

                $subtotal = 0;
                $orderItems = [];

                // Add 1-3 random products to order
                $orderProducts = $products->random(rand(1, 3));

                foreach ($orderProducts as $product) {
                    $quantity = rand(1, 3);
                    $price = $product->base_price;
                    $itemSubtotal = $price * $quantity;
                    $subtotal += $itemSubtotal;

                    $orderItems[] = [
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'price' => $price,
                        'subtotal' => $itemSubtotal,
                    ];
                }

                $tax = $subtotal * 0.1;
                $total = $subtotal + $tax;

                $order = Order::create([
                    'order_number' => 'ORD-' . strtoupper(uniqid()),
                    'user_id' => $customer->id,
                    'status' => $status,
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'total_amount' => $total,
                    'shipping_address' => $customer->name . "\n123 Main St\nCity, State 12345",
                ]);

                foreach ($orderItems as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'subtotal' => $item['subtotal'],
                    ]);
                }
            }
        }
    }
}
