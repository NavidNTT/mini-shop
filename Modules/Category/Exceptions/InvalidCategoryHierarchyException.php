<?php

namespace Modules\Category\Exceptions;

use App\Exceptions\ApiDomainException;

class InvalidCategoryHierarchyException extends ApiDomainException
{
    public function __construct(string $message = 'سلسله‌مراتب دسته‌بندی نامعتبر است.')
    {
        parent::__construct($message, 422, 'invalid_category_hierarchy');
    }
}
