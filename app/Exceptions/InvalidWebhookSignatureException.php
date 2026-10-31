<?php

namespace App\Exceptions;

use Exception;

class InvalidWebhookSignatureException extends Exception
{
    public function __construct(
        string $message = 'Invalid signature key hash.',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
