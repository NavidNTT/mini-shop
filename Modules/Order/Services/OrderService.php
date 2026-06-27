<?php

namespace Modules\Order\Services;

use Modules\Cart\Exceptions\EmptyCartException;
use Modules\Order\Exceptions\InsufficientStockException;
use Modules\Order\Repositories\OrderRepository;
use Modules\Cart\Services\CartService;
use Modules\Product\Exceptions\InactiveProductException;
use Modules\Product\Exceptions\ProductNotFoundException;
use Modules\Product\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    public function __construct(
        protected OrderRepository $orderRepository,
        protected CartService $cartService
    ) {}

    public function checkout($userId, ?string $notes = null)
    {
        $cart = $this->cartService->getCart($userId);

        if (!$cart || $cart->items->isEmpty()) {
            Log::warning("تلاش برای تسویه حساب با سبد خرید خالی. User ID: {$userId}");
            throw new EmptyCartException();
        }

        return DB::transaction(function () use ($userId, $cart, $notes) {
            $totalPrice = 0;

            foreach ($cart->items as $item) {
                $product = Product::query()
                    ->where('id', $item->product_id)
                    ->lockForUpdate()
                    ->first();

                if (!$product) {
                    throw new ProductNotFoundException($item->product_id);
                }

                if (!$product->is_active) {
                    throw new InactiveProductException($product->title);
                }

                if ($product->stock < $item->quantity) {
                    Log::warning("موجودی ناکافی برای محصول {$product->title}. درخواستی: {$item->quantity}, موجودی: {$product->stock}");
                    throw new InsufficientStockException($product->title, $product->stock, $item->quantity);
                }

                $totalPrice += $item->price * $item->quantity;
            }

            $order = $this->orderRepository->createOrder($userId, $totalPrice, 'pending', $notes);

            foreach ($cart->items as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                ]);

                Product::query()
                    ->where('id', $item->product_id)
                    ->decrement('stock', $item->quantity);
            }

            $cart->items()->delete();

            Log::info("سفارش جدید ثبت شد. Order ID: {$order->id}, User ID: {$userId}");

            return $order->load('items.product');
        });
    }

    public function getUserOrders($userId)
    {
        return $this->orderRepository->getUserOrders($userId);
    }

    public function getOrderById($userId, $orderId)
    {
        return $this->orderRepository->getUserOrderById($userId, $orderId);
    }
}
