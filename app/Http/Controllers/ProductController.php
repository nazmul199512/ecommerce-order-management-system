<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Requests\ImportProductsRequest;
use App\Http\Resources\ProductResource;
use App\Jobs\ImportProductsCsv;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['vendor', 'variants', 'inventory']);

        // Search
        if ($request->has('search')) {
            $query->search($request->search);
        }

        // Filter by vendor
        if ($request->has('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Scope to vendor's products if not admin
        if (auth()->user()->isVendor()) {
            $query->where('vendor_id', auth()->id());
        }

        $products = $query->latest()->paginate(15);

        return response()->json([
            'data' => ProductResource::collection($products),
            'meta' => [
                'current_page' => $products->currentPage(),
                'total' => $products->total(),
                'per_page' => $products->perPage(),
            ],
        ]);
    }

    public function store(CreateProductRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['vendor_id'] = auth()->user()->isVendor()
            ? auth()->id()
            : $data['vendor_id'];

        $product = Product::create($data);

        // Create inventory record
        $product->inventory()->create([
            'quantity' => $data['initial_quantity'] ?? 0,
            'low_stock_threshold' => $data['low_stock_threshold'] ?? 10,
        ]);

        return response()->json([
            'message' => 'Product created successfully',
            'data' => new ProductResource($product->load(['inventory', 'variants'])),
        ], 201);
    }

    public function show(Product $product): JsonResponse
    {
        // Authorization check for vendors
        if (auth()->user()->isVendor() && $product->vendor_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        return response()->json([
            'data' => new ProductResource($product->load(['vendor', 'variants', 'inventory'])),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        // Authorization check for vendors
        if (auth()->user()->isVendor() && $product->vendor_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $product->update($request->validated());

        return response()->json([
            'message' => 'Product updated successfully',
            'data' => new ProductResource($product->fresh()),
        ]);
    }

    public function destroy(Product $product): JsonResponse
    {
        // Authorization check for vendors
        if (auth()->user()->isVendor() && $product->vendor_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully',
        ]);
    }

    public function import(ImportProductsRequest $request): JsonResponse
    {
        $file = $request->file('file');
        $path = $file->store('imports');

        ImportProductsCsv::dispatch($path, auth()->id());

        return response()->json([
            'message' => 'Import job queued successfully. You will be notified when complete.',
        ], 202);
    }

    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2',
        ]);

        $products = Product::search($request->query)
            ->with(['vendor', 'inventory'])
            ->paginate(15);

        return response()->json([
            'data' => ProductResource::collection($products),
            'meta' => [
                'current_page' => $products->currentPage(),
                'total' => $products->total(),
            ],
        ]);
    }
}
