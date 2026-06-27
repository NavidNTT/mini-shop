<?php

namespace Modules\Order\Repositories;

use Modules\Order\Models\Order;

class OrderRepository
{
    public function createOrder($userId, $totalPrice, $status = 'pending', ?string $notes = null): Order
    {
        return Order::create([
            'user_id' => $userId,
            'total_price' => $totalPrice,
            'status' => $status,
            'notes' => $notes,
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
