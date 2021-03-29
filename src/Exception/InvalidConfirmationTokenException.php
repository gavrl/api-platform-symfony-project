<?php

namespace App\Exception;

use Throwable;
use Exception;
use JetBrains\PhpStorm\Pure;

class InvalidConfirmationTokenException extends Exception
{
    #[Pure]
    public function __construct(
        string $message = "",
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct('Confirmation token is invalid.', $code, $previous);
    }
}