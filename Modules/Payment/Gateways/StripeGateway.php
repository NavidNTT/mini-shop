<?php

namespace Modules\Payment\Gateways;

use Illuminate\Support\Str;

class StripeGateway implements PaymentGatewayInterface
{
    public function __construct(
        private readonly string $secretKey,
        private readonly string $publicKey
    ) {}

    public function requestPayment(float $amount, array $metadata = []): PaymentResponse
    {
        // In a real implementation, this would call Stripe's API
        // For now, this serves as a reference implementation
        $paymentIntentId = 'pi_' . Str::random(24);

        return new PaymentResponse(
            success: true,
            transactionId: $paymentIntentId,
            message: 'Payment intent created successfully.',
            redirectUrl: null,
            metadata: [
                'amount' => $amount,
                'stripe_public_key' => $this->publicKey,
            ]
        );
    }

    public function verifyPayment(string $paymentId): PaymentResponse
    {
        // In production, retrieve the PaymentIntent from Stripe
        return new PaymentResponse(
            success: true,
            transactionId: $paymentId,
            message: 'Payment verified successfully.',
            metadata: ['stripe_status' => 'succeeded']
        );
    }

    public function name(): string
    {
        return 'stripe';
    }
}
