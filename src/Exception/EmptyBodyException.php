<?php

namespace App\Exception;

use Throwable;
use Exception;
use JetBrains\PhpStorm\Pure;

class EmptyBodyException extends Exception
{
    #[Pure]
    public function __construct(
        string $message = "",
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct('The body of the POST/PUT method cannot be empty', $code, $previous);
    }
}