<?php

namespace Modules\Order\Repositories;

use Modules\Order\Models\Order;

class OrderRepository
{
    public function createOrder($userId, $totalPrice, $status = 'pending')
    {
        return Order::create([
            'user_id' => $userId,
            'total_price' => $totalPrice,
            'status' => $status
        ]);
    }

    public function getUserOrders($userId)
    {
        return Order::with('items.product')->where('user_id', $userId)->latest()->get();
    }

    public function getUserOrderById($userId, $orderId)
    {
        return Order::with('items.product')
            ->where('user_id', $userId)
            ->findOrFail($orderId);
    }
}