<?php

namespace App\Exceptions;

class CurrencyMismatchException extends \Exception
{
    public function __construct(string $expected, string $actual)
    {
        parent::__construct(
            "Currency mismatch: merchant uses {$expected}, transaction has {$actual}."
        );
    }
}
