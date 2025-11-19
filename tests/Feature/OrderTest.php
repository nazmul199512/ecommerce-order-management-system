<?php
namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticate($role = 'customer')
    {
        $user = User::factory()->create(['role' => $role]);
        $token = auth()->login($user);
        return ['user' => $user, 'token' => $token];
    }

    public function test_customer_can_create_order()
    {
        $auth = $this->authenticate('customer');
        $vendor = User::factory()->create(['role' => 'vendor']);
        $product = Product::factory()->create(['vendor_id' => $vendor->id]);
        
        Inventory::create([
            'product_id' => $product->id,
            'quantity' => 100,
            'reserved' => 0,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$auth['token']}")
            ->postJson('/api/v1/orders', [
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 2,
                    ],
                ],
                'shipping_address' => '123 Test St, Test City, 12345',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'order_number', 'status', 'total_amount'],
            ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $auth['user']->id,
            'status' => 'pending',
        ]);
    }

    public function test_cannot_create_order_with_insufficient_stock()
    {
        $auth = $this->authenticate('customer');
        $vendor = User::factory()->create(['role' => 'vendor']);
        $product = Product::factory()->create(['vendor_id' => $vendor->id]);
        
        Inventory::create([
            'product_id' => $product->id,
            'quantity' => 1,
            'reserved' => 0,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$auth['token']}")
            ->postJson('/api/v1/orders', [
                'items' => [
                    [
                        'product_id' => $product->id,
                'quantity' => 10,
                    ],
                ],
                'shipping_address' => '123 Test St',
            ]);

        $response->assertStatus(422);
    }

    public function test_customer_can_cancel_own_order()
    {
        $auth = $this->authenticate('customer');
        $vendor = User::factory()->create(['role' => 'vendor']);
        $product = Product::factory()->create(['vendor_id' => $vendor->id]);
        
        $inventory = Inventory::create([
            'product_id' => $product->id,
            'quantity' => 100,
        ]);

        $order = Order::factory()->create([
            'user_id' => $auth['user']->id,
            'status' => 'pending',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$auth['token']}")
            ->postJson("/api/v1/orders/{$order->id}/cancel");

        $response->assertStatus(200);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_customer_cannot_cancel_other_customer_order()
    {
        $auth = $this->authenticate('customer');
        $otherCustomer = User::factory()->create(['role' => 'customer']);
        
        $order = Order::factory()->create([
            'user_id' => $otherCustomer->id,
            'status' => 'pending',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$auth['token']}")
            ->postJson("/api/v1/orders/{$order->id}/cancel");

        $response->assertStatus(403);
    }

    public function test_admin_can_update_order_status()
    {
        $auth = $this->authenticate('admin');
        $customer = User::factory()->create(['role' => 'customer']);
        
        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'status' => 'pending',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$auth['token']}")
            ->patchJson("/api/v1/orders/{$order->id}/status", [
                'status' => 'processing',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'processing',
        ]);
    }

    public function test_customer_can_only_see_own_orders()
    {
        $auth = $this->authenticate('customer');
        
        Order::factory()->create(['user_id' => $auth['user']->id]);
        Order::factory()->create(['user_id' => User::factory()->create()->id]);

        $response = $this->withHeader('Authorization', "Bearer {$auth['token']}")
            ->getJson('/api/v1/orders');

        $response->assertStatus(200);
        $data = $response->json('data');
        
        $this->assertCount(1, $data);
        $this->assertEquals($auth['user']->id, $data[0]['user']['id']);
    }
}