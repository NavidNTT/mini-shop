<?php

namespace Modules\Cart\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Cart\Services\CartService;
use Modules\Cart\Http\Requests\AddToCartRequest;
use Modules\Cart\Http\Requests\UpdateCartRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index(Request $request): JsonResponse
    {
        $cart = $this->cartService->getCart($request->user()->id);
        return response()->json($cart);
    }

    public function add(AddToCartRequest $request): JsonResponse
    {
        $cart = $this->cartService->addToCart($request->user()->id, $request->validated());
        return response()->json([
            'message' => 'محصول با موفقیت به سبد خرید اضافه شد.',
            'cart' => $cart
        ]);
    }

    public function update(UpdateCartRequest $request, $itemId): JsonResponse
    {
        $cart = $this->cartService->updateCartItem($request->user()->id, $itemId, $request->validated());
        return response()->json(['message' => 'سبد خرید بروزرسانی شد.', 'cart' => $cart]);
    }

    public function remove(Request $request, $itemId): JsonResponse
    {
        $cart = $this->cartService->removeCartItem($request->user()->id, $itemId);
        return response()->json(['message' => 'محصول از سبد خرید حذف شد.', 'cart' => $cart]);
    }
}