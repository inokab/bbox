<?php

namespace App\Services;

use App\DTOs\TransactionData;
use App\DTOs\ValidationResult;
use App\Models\Merchant;

class ValidatorService
{
    public function validate(Merchant $merchant, TransactionData $data): ValidationResult
    {
        return new ValidationResult(true);
    }
}
