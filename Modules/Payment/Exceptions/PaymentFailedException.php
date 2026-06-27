<?php

namespace Modules\Payment\Exceptions;

use App\Exceptions\ApiDomainException;

class PaymentFailedException extends ApiDomainException
{
    public function __construct(string $message = 'پرداخت با خطا مواجه شد.')
    {
        parent::__construct($message, 422, 'payment_failed');
    }
}
