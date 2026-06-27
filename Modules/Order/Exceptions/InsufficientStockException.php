<?php

namespace Modules\Order\Exceptions;

use App\Exceptions\ApiDomainException;

class InsufficientStockException extends ApiDomainException
{
    public function __construct(string $productName, int $available, int $requested)
    {
        parent::__construct(
            "موجودی محصول «{$productName}» کافی نیست. موجودی فعلی: {$available}، درخواستی: {$requested}",
            422,
            'insufficient_stock'
        );
    }
}
