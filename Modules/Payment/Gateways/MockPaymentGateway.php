<?php

namespace Modules\Payment\Gateways;

use Illuminate\Support\Str;

class MockPaymentGateway implements PaymentGatewayInterface
{
    public function requestPayment(float $amount, array $metadata = []): PaymentResponse
    {
        $paymentId = (string) Str::random(12);

        return new PaymentResponse(
            success: true,
            transactionId: $paymentId,
            message: 'درخواست پرداخت با موفقیت ایجاد شد. برای تایید، POST /api/v1/payment/verify را با payment_id فراخوانی کنید.',
            redirectUrl: null,
            metadata: ['amount' => $amount]
        );
    }

    public function verifyPayment(string $paymentId): PaymentResponse
    {
        return new PaymentResponse(
            success: true,
            transactionId: $paymentId,
            message: 'پرداخت با موفقیت تایید شد.',
            metadata: ['reference_id' => Str::random(16)]
        );
    }

    public function name(): string
    {
        return 'mock';
    }
}
