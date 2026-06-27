<?php

namespace Modules\Category\Exceptions;

use App\Exceptions\ApiDomainException;

class CategoryDeleteException extends ApiDomainException
{
    public static function hasProducts(): self
    {
        return new self(
            'این دسته‌بندی دارای محصول است و قابل حذف نیست.',
            422,
            'category_has_products'
        );
    }

    public static function hasChildren(): self
    {
        return new self(
            'این دسته‌بندی دارای زیردسته است و قابل حذف نیست.',
            422,
            'category_has_children'
        );
    }
}
