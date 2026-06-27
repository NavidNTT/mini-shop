<?php

namespace App\Exceptions;

use Exception;

class ApiDomainException extends Exception
{
    public function __construct(
        string $message,
        public readonly int $statusCode = 400,
        public readonly ?string $errorCode = null,
    ) {
        parent::__construct($message);
    }
}
