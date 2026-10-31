<?php

namespace App\Exceptions;

use Exception;

class PaymentNotAllowedException extends Exception
{
    public function __construct(
        string $message = 'Transaksi sudah dibayar atau telah melewati batas holding pembayaran.',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
