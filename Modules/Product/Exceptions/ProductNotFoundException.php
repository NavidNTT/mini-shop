<?php

namespace Modules\Product\Exceptions;

use App\Exceptions\ApiDomainException;

class ProductNotFoundException extends ApiDomainException
{
    public function __construct(int $id)
    {
        parent::__construct(
            "محصولی با شناسه {$id} یافت نشد.",
            404,
            'product_not_found'
        );
    }
}
