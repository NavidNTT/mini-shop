<?php

namespace Modules\Order\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Order\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function checkout(Request $request): JsonResponse
    {
        try {
            $order = $this->orderService->checkout($request->user()->id);
            return response()->json([
                'message' => 'سفارش با موفقیت ثبت شد.',
                'order' => $order
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->orderService->getUserOrders($request->user()->id));
    }

    public function show(Request $request, $id): JsonResponse
    {
        return response()->json($this->orderService->getOrderById($request->user()->id, $id));
    }
}