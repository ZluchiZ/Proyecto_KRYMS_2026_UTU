<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

class RegistrationMailDeliveryException extends RuntimeException
{
    public function __construct(Throwable $previous)
    {
        parent::__construct('The registration verification email could not be delivered.', 0, $previous);
    }
}
