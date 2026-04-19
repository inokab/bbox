<?php

namespace App\Exceptions;

class IdempotencyConflictException extends \Exception
{
    public function __construct()
    {
        parent::__construct('The Idempotency-Key is already used by a different merchant.');
    }
}
