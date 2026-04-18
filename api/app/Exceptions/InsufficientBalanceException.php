<?php

namespace App\Exceptions;

class InsufficientBalanceException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Insufficient balance for this refund.');
    }
}
