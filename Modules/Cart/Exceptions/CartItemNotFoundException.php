<?php

namespace Modules\Cart\Exceptions;

use App\Exceptions\ApiDomainException;

class CartItemNotFoundException extends ApiDomainException
{
    public function __construct()
    {
        parent::__construct(
            'آیتم مورد نظر در سبد خرید شما یافت نشد.',
            404,
            'cart_item_not_found'
        );
    }
}
