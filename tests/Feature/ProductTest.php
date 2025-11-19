<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticate($role = 'admin')
    {
        $user = User::factory()->create(['role' => $role]);
        $token = auth()->login($user);
        return ['user' => $user, 'token' => $token];
    }

    public function test_admin_can_create_product()
    {
        $auth = $this->authenticate('admin');
        $vendor = User::factory()->create(['role' => 'vendor']);

        $response = $this->withHeader('Authorization', "Bearer {$auth['token']}")
            ->postJson('/api/v1/products', [
                'name' => 'Test Product',
                'description' => 'Test Description',
                'sku' => 'TEST-001',
                'base_price' => 99.99,
                'vendor_id' => $vendor->id,
                'initial_quantity' => 100,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'name', 'sku', 'base_price'],
            ]);

        $this->assertDatabaseHas('products', [
            'sku' => 'TEST-001',
        ]);
    }

    public function test_vendor_can_create_own_product()
    {
        $auth = $this->authenticate('vendor');

        $response = $this->withHeader('Authorization', "Bearer {$auth['token']}")
            ->postJson('/api/v1/products', [
                'name' => 'Test Product',
                'sku' => 'TEST-002',
                'base_price' => 49.99,
                'initial_quantity' => 50,
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('products', [
            'sku' => 'TEST-002',
            'vendor_id' => $auth['user']->id,
        ]);
    }

    public function test_customer_cannot_create_product()
    {
        $auth = $this->authenticate('customer');

        $response = $this->withHeader('Authorization', "Bearer {$auth['token']}")
            ->postJson('/api/v1/products', [
                'name' => 'Test Product',
                'sku' => 'TEST-003',
                'base_price' => 29.99,
                'initial_quantity' => 25,
            ]);

        $response->assertStatus(403);
    }

    public function test_can_list_products()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        Product::factory()->count(5)->create(['vendor_id' => $vendor->id]);

        $auth = $this->authenticate('customer');

        $response = $this->withHeader('Authorization', "Bearer {$auth['token']}")
            ->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'sku', 'base_price'],
                ],
                'meta' => ['current_page', 'total', 'per_page'],
            ]);
    }

    public function test_can_search_products()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        Product::factory()->create([
            'vendor_id' => $vendor->id,
            'name' => 'iPhone 15',
            'sku' => 'APPLE-001',
        ]);
        Product::factory()->create([
            'vendor_id' => $vendor->id,
            'name' => 'Samsung Galaxy',
            'sku' => 'SAMSUNG-001',
        ]);

        $auth = $this->authenticate('customer');

        $response = $this->withHeader('Authorization', "Bearer {$auth['token']}")
            ->getJson('/api/v1/products/search?query=iPhone');

        $response->assertStatus(200);
    }

    public function test_vendor_can_update_own_product()
    {
        $auth = $this->authenticate('vendor');
        $product = Product::factory()->create(['vendor_id' => $auth['user']->id]);

        $response = $this->withHeader('Authorization', "Bearer {$auth['token']}")
            ->putJson("/api/v1/products/{$product->id}", [
                'name' => 'Updated Product Name',
                'base_price' => 199.99,
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product Name',
        ]);
    }

    public function test_vendor_cannot_update_other_vendor_product()
    {
        $vendor1 = User::factory()->create(['role' => 'vendor']);
        $product = Product::factory()->create(['vendor_id' => $vendor1->id]);

        $auth = $this->authenticate('vendor');

        $response = $this->withHeader('Authorization', "Bearer {$auth['token']}")
            ->putJson("/api/v1/products/{$product->id}", [
                'name' => 'Hacked Product',
            ]);

        $response->assertStatus(403);
    }
}
