<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = User::where('role', 'vendor')->get();

        foreach ($vendors as $vendor) {
            $products = [
                [
                    'name' => 'iPhone 15 Pro',
                    'description' => 'The latest flagship iPhone with A17 Pro chip',
                    'sku' => 'APPLE-IP15PRO',
                    'base_price' => 999.99,
                ],
                [
                    'name' => 'iPhone 15',
                    'description' => 'The standard iPhone 15 model',
                    'sku' => 'APPLE-IP15',
                    'base_price' => 799.99,
                ],
                [
                    'name' => 'Samsung Galaxy S24',
                    'description' => 'Premium Android flagship with AI features',
                    'sku' => 'SAMSUNG-S24',
                    'base_price' => 899.99,
                ],
                [
                    'name' => 'MacBook Pro 14"',
                    'description' => 'Professional laptop with M3 chip',
                    'sku' => 'APPLE-MBP14',
                    'base_price' => 1999.99,
                ],
                [
                    'name' => 'AirPods Pro',
                    'description' => 'Wireless earbuds with active noise cancellation',
                    'sku' => 'APPLE-APP',
                    'base_price' => 249.99,
                ],
            ];

            foreach ($products as $productData) {
                if (strpos($productData['sku'], 'APPLE') !== false && $vendor->email !== 'vendor@apple.com') {
                    continue;
                }
                if (strpos($productData['sku'], 'SAMSUNG') !== false && $vendor->email !== 'vendor@samsung.com') {
                    continue;
                }

                $product = Product::create([
                    'vendor_id' => $vendor->id,
                    'name' => $productData['name'],
                    'description' => $productData['description'],
                    'sku' => $productData['sku'],
                    'base_price' => $productData['base_price'],
                    'is_active' => true,
                ]);

                // Create inventory
                Inventory::create([
                    'product_id' => $product->id,
                    'quantity' => rand(50, 200),
                    'reserved' => 0,
                    'low_stock_threshold' => 10,
                ]);

                // Create variants for some products
                if (strpos($product->name, 'iPhone') !== false || strpos($product->name, 'Galaxy') !== false) {
                    $colors = ['Black', 'White', 'Blue', 'Red'];
                    $storages = ['128GB', '256GB', '512GB'];

                    foreach ($colors as $color) {
                        foreach ($storages as $storage) {
                            $variant = ProductVariant::create([
                                'product_id' => $product->id,
                                'name' => "$color - $storage",
                                'sku' => $product->sku . '-' . strtoupper(substr($color, 0, 3)) . '-' . $storage,
                                'price' => $product->base_price + (array_search($storage, $storages) * 100),
                                'attributes' => [
                                    'color' => $color,
                                    'storage' => $storage,
                                ],
                            ]);

                            // Create inventory for variant
                            Inventory::create([
                                'product_id' => $product->id,
                                'variant_id' => $variant->id,
                                'quantity' => rand(20, 100),
                                'reserved' => 0,
                                'low_stock_threshold' => 5,
                            ]);
                        }
                    }
                }
            }
        }
    }
}
