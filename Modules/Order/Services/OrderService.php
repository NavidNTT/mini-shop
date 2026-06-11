<?php

namespace Modules\Order\Services;

use Modules\Order\Repositories\OrderRepository;
use Modules\Cart\Services\CartService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class OrderService
{
    protected $orderRepository;
    protected $cartService;

    public function __construct(OrderRepository $orderRepository, CartService $cartService)
    {
        $this->orderRepository = $orderRepository;
        $this->cartService = $cartService;
    }

    public function checkout($userId)
    {
        $cart = $this->cartService->getCart($userId);

        if (!$cart || $cart->items->isEmpty()) {
            Log::warning("تلاش برای تسویه حساب با سبد خرید خالی. User ID: {$userId}");
            throw new Exception('سبد خرید شما خالی است و امکان ثبت سفارش وجود ندارد.');
        }

        $totalPrice = $cart->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        return DB::transaction(function () use ($userId, $totalPrice, $cart) {
            try {
                $order = $this->orderRepository->createOrder($userId, $totalPrice);

                foreach ($cart->items as $item) {
                    $order->items()->create([
                        'product_id' => $item->product_id,
                        'price'      => $item->price,
                        'quantity'   => $item->quantity
                    ]);
                }

                // خالی کردن سبد خرید بعد از ثبت سفارش
                $cart->items()->delete();

                Log::info("سفارش جدید ثبت شد. Order ID: {$order->id}, User ID: {$userId}");

                return $order->load('items.product');
            } catch (Exception $e) {
                Log::error("خطا در فرآیند ثبت سفارش: " . $e->getMessage());
                throw new Exception('متأسفانه در ثبت سفارش مشکلی پیش آمد.');
            }
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