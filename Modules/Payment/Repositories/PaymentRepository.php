<?php

namespace Modules\Payment\Repositories;

use Modules\Payment\Models\Payment;

class PaymentRepository
{
    public function create(array $data)
    {
        return Payment::create($data);
    }

    public function findById($id)
    {
        return Payment::findOrFail($id);
    }

    public function update(Payment $payment, array $data)
    {
        $payment->update($data);
        return $payment;
    }
}