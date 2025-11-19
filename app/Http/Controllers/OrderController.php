<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'from_date', 'to_date']);

        // Customers can only see their orders
        if (auth()->user()->isCustomer()) {
            $filters['user_id'] = auth()->id();
        }

        // Vendors can see orders containing their products
        if (auth()->user()->isVendor()) {
            // This would need a more complex query in production
            $filters['vendor_id'] = auth()->id();
        }

        $orders = $this->orderService->getOrders($filters);

        return response()->json([
            'data' => OrderResource::collection($orders),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    public function store(CreateOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->createOrder(
            auth()->user(),
            $request->validated()
        );

        return response()->json([
            'message' => 'Order created successfully',
            'data' => new OrderResource($order),
        ], 201);
    }

    public function show(Order $order): JsonResponse
    {
        // Authorization check
        if (auth()->user()->isCustomer() && $order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        return response()->json([
            'data' => new OrderResource($order->load(['items.product', 'items.variant'])),
        ]);
    }

    public function cancel(Order $order): JsonResponse
    {
        // Authorization check
        if (auth()->user()->isCustomer() && $order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $order = $this->orderService->cancelOrder($order);

        return response()->json([
            'message' => 'Order cancelled successfully',
            'data' => new OrderResource($order),
        ]);
    }

    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        // Only admin and vendors can update status
        if (auth()->user()->isCustomer()) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $order = $this->orderService->updateStatus($order, $request->status);

        return response()->json([
            'message' => 'Order status updated successfully',
            'data' => new OrderResource($order),
        ]);
    }

    public function invoice(Order $order): JsonResponse
    {
        // Authorization check
        if (auth()->user()->isCustomer() && $order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        if (!$order->invoice_path) {
            return response()->json([
                'message' => 'Invoice not yet generated',
            ], 404);
        }

        return response()->download(
            storage_path('app/' . $order->invoice_path),
            'invoice-' . $order->order_number . '.pdf'
        );
    }
}
