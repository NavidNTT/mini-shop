<?php

namespace Modules\Payment\Repositories;

use Modules\Order\Models\Order;
use Modules\Payment\Models\Payment;

class PaymentRepository
{
    public function create(array $data): Payment
    {
        return Payment::create($data);
    }

    public function findById($id): Payment
    {
        return Payment::findOrFail($id);
    }

    public function findPendingOrderForUser(int $userId, int $orderId): Order
    {
        return Order::query()
            ->where('user_id', $userId)
            ->findOrFail($orderId);
    }

    public function findForUserWithLock(int $userId, int $paymentId): Payment
    {
        return Payment::query()
            ->where('id', $paymentId)
            ->whereHas('order', fn ($query) => $query->where('user_id', $userId))
            ->with('order')
            ->lockForUpdate()
            ->firstOrFail();
    }

    public function update(Payment $payment, array $data): Payment
    {
        $payment->update($data);

        return $payment;
    }
}
