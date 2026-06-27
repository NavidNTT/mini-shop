<?php

namespace Modules\Order\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Order\Services\OrderService;
use Modules\Order\Http\Requests\CheckoutRequest;
use Modules\Order\Transformers\OrderResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function checkout(CheckoutRequest $request): JsonResponse
    {
        $order = $this->orderService->checkout(
            $request->user()->id,
            $request->validated('notes')
        );

        return response()->json([
            'message' => 'سفارش با موفقیت ثبت شد.',
            'data' => new OrderResource($order),
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $orders = $this->orderService->getUserOrders($request->user()->id);

        return response()->json([
            'data' => OrderResource::collection($orders),
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $order = $this->orderService->getOrderById($request->user()->id, $id);

        return response()->json(['data' => new OrderResource($order)]);
    }
}
