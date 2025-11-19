<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'vendor_id' => $this->vendor_id,
            'vendor' => $this->whenLoaded('vendor', fn() => [
                'id' => $this->vendor->id,
                'name' => $this->vendor->name,
                'email' => $this->vendor->email,
            ]),
            'name' => $this->name,
            'description' => $this->description,
            'sku' => $this->sku,
            'base_price' => $this->base_price,
            'image_path' => $this->image_path,
            'is_active' => $this->is_active,
            'inventory' => $this->whenLoaded('inventory', fn() => [
                'quantity' => $this->inventory->quantity,
                'available_quantity' => $this->inventory->available_quantity,
                'reserved' => $this->inventory->reserved,
                'low_stock_threshold' => $this->inventory->low_stock_threshold,
                'is_low_stock' => $this->inventory->isLowStock(),
            ]),
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
