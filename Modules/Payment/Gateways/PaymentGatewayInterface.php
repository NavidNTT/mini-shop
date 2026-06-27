<?php

namespace Modules\Payment\Gateways;

interface PaymentGatewayInterface
{
    public function requestPayment(float $amount, array $metadata = []): PaymentResponse;

    public function verifyPayment(string $paymentId): PaymentResponse;

    public function name(): string;
}
