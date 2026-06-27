<?php

namespace Modules\Product\Exceptions;

use App\Exceptions\ApiDomainException;

class InactiveProductException extends ApiDomainException
{
    public function __construct(string $productTitle)
    {
        parent::__construct(
            "محصول «{$productTitle}» در حال حاضر غیرفعال است و قابل افزودن به سبد خرید نیست.",
            422,
            'inactive_product'
        );
    }
}
