<?php

namespace Tests\Unit;

use App\Actions\Order\CreateOrderAction;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_order_deducts_inventory()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $vendor = User::factory()->create(['role' => 'vendor']);
        $product = Product::factory()->create(['vendor_id' => $vendor->id]);
        
        $inventory = Inventory::create([
            'product_id' => $product->id,
            'quantity' => 100,
            'reserved' => 0,
        ]);

        $orderData = [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                ],
            ],
            'shipping_address' => '123 Test St',
        ];

        $orderService = app(OrderService::class);
        $order = $orderService->createOrder($customer, $orderData);

        $this->assertEquals(95, $inventory->fresh()->quantity);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'user_id' => $customer->id,
        ]);
    }
}