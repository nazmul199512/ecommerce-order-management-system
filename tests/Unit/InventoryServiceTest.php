<?php

namespace Tests\Unit;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_restore_stock_increases_quantity()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $product = Product::factory()->create(['vendor_id' => $vendor->id]);

        $inventory = Inventory::create([
            'product_id' => $product->id,
            'quantity' => 50,
        ]);

        $inventoryService = app(InventoryService::class);
        $inventoryService->restoreStock($product->id, null, 10);

        $this->assertEquals(60, $inventory->fresh()->quantity);
    }

    public function test_deduct_stock_decreases_quantity()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $product = Product::factory()->create(['vendor_id' => $vendor->id]);

        $inventory = Inventory::create([
            'product_id' => $product->id,
            'quantity' => 50,
        ]);

        $inventoryService = app(InventoryService::class);
        $inventoryService->deductStock($product->id, null, 10);

        $this->assertEquals(40, $inventory->fresh()->quantity);
    }
}
