<?php

namespace Modules\Order\Exceptions;

use App\Exceptions\ApiDomainException;

class OrderNotPayableException extends ApiDomainException
{
    public function __construct(string $message = 'این سفارش قابل پرداخت نیست.')
    {
        parent::__construct($message, 422, 'order_not_payable');
    }
}
