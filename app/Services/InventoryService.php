<?php

namespace App\Services;

use App\Events\LowStockDetected;
use App\Models\Inventory;
use App\Models\InventoryLog;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function reserveStock(int $productId, ?int $variantId, int $quantity): bool
    {
        return DB::transaction(function () use ($productId, $variantId, $quantity) {
            $inventory = Inventory::where('product_id', $productId)
                ->where('variant_id', $variantId)
                ->lockForUpdate()
                ->first();

            if (!$inventory || $inventory->available_quantity < $quantity) {
                return false;
            }

            $inventory->reserved += $quantity;
            $inventory->save();

            $this->logInventoryChange($inventory, 'adjustment', -$quantity);

            if ($inventory->isLowStock()) {
                event(new LowStockDetected($inventory));
            }

            return true;
        });
    }

    public function deductStock(int $productId, ?int $variantId, int $quantity, $reference = null): bool
    {
        return DB::transaction(function () use ($productId, $variantId, $quantity, $reference) {
            $inventory = Inventory::where('product_id', $productId)
                ->where('variant_id', $variantId)
                ->lockForUpdate()
                ->first();

            if (!$inventory) {
                return false;
            }

            $quantityBefore = $inventory->quantity;
            $inventory->quantity -= $quantity;
            $inventory->reserved = max(0, $inventory->reserved - $quantity);
            $inventory->save();

            $this->logInventoryChange(
                $inventory,
                'sale',
                -$quantity,
                $reference
            );

            return true;
        });
    }

    public function restoreStock(int $productId, ?int $variantId, int $quantity, $reference = null): bool
    {
        return DB::transaction(function () use ($productId, $variantId, $quantity, $reference) {
            $inventory = Inventory::where('product_id', $productId)
                ->where('variant_id', $variantId)
                ->lockForUpdate()
                ->first();

            if (!$inventory) {
                return false;
            }

            $inventory->quantity += $quantity;
            $inventory->save();

            $this->logInventoryChange(
                $inventory,
                'return',
                $quantity,
                $reference
            );

            return true;
        });
    }

    private function logInventoryChange(
        Inventory $inventory,
        string $type,
        int $quantityChanged,
        $reference = null
    ): void {
        InventoryLog::create([
            'inventory_id' => $inventory->id,
            'type' => $type,
            'quantity_before' => $inventory->quantity - $quantityChanged,
            'quantity_after' => $inventory->quantity,
            'quantity_changed' => $quantityChanged,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference?->id,
        ]);
    }

    public function updateInventory(int $productId, ?int $variantId, int $quantity): Inventory
    {
        return DB::transaction(function () use ($productId, $variantId, $quantity) {
            $inventory = Inventory::updateOrCreate(
                [
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                ],
                [
                    'quantity' => $quantity,
                ]
            );

            if ($inventory->isLowStock()) {
                event(new LowStockDetected($inventory));
            }

            return $inventory;
        });
    }
}
