<?php

namespace Modules\Cart\Exceptions;

use App\Exceptions\ApiDomainException;

class EmptyCartException extends ApiDomainException
{
    public function __construct()
    {
        parent::__construct(
            'سبد خرید شما خالی است و امکان ثبت سفارش وجود ندارد.',
            422,
            'empty_cart'
        );
    }
}
