<?php

namespace App\Actions\Product;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ImportProductsAction
{
    public function execute(string $filePath, int $userId): array
    {
        $file = fopen(storage_path('app/' . $filePath), 'r');
        $header = fgetcsv($file);

        $imported = 0;
        $failed = 0;
        $errors = [];

        DB::transaction(function () use ($file, $userId, &$imported, &$failed, &$errors) {
            while (($row = fgetcsv($file)) !== false) {
                $data = array_combine($header, $row);

                $validator = Validator::make($data, [
                    'name' => 'required|string|max:255',
                    'sku' => 'required|unique:products,sku',
                    'base_price' => 'required|numeric|min:0',
                    'description' => 'nullable|string',
                    'initial_quantity' => 'required|integer|min:0',
                ]);

                if ($validator->fails()) {
                    $failed++;
                    $errors[] = [
                        'row' => $data,
                        'errors' => $validator->errors()->toArray(),
                    ];
                    continue;
                }

                $product = Product::create([
                    'vendor_id' => $userId,
                    'name' => $data['name'],
                    'sku' => $data['sku'],
                    'base_price' => $data['base_price'],
                    'description' => $data['description'] ?? null,
                    'is_active' => true,
                ]);

                $product->inventory()->create([
                    'quantity' => $data['initial_quantity'],
                    'low_stock_threshold' => $data['low_stock_threshold'] ?? 10,
                ]);

                $imported++;
            }
        });

        fclose($file);

        return [
            'imported' => $imported,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }
}
