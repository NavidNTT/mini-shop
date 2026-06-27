<?php

namespace Modules\Payment\Providers;

use Modules\Payment\Gateways\MockPaymentGateway;
use Modules\Payment\Gateways\PaymentGatewayInterface;
use Modules\Payment\Gateways\StripeGateway;
use Nwidart\Modules\Support\ModuleServiceProvider;

class PaymentServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Payment';

    protected string $nameLower = 'payment';

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function register(): void
    {
        parent::register();

        $this->app->singleton(PaymentGatewayInterface::class, function () {
            $gateway = config('payment.gateway', 'mock');

            return match ($gateway) {
                'stripe' => new StripeGateway(
                    secretKey: (string) config('payment.stripe.secret_key'),
                    publicKey: (string) config('payment.stripe.public_key'),
                ),
                default => new MockPaymentGateway(),
            };
        });
    }
}
