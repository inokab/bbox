<?php

namespace App\DTOs;

use App\Enums\TransactionType;

readonly class TransactionData
{
    public function __construct(
        public string          $idempotencyKey,
        public TransactionType $type,
        public int             $amount,
        public string          $currency,
    ) {}

    public static function fromRequest(array $data, string $defaultCurrency, string $idempotencyKey): self
    {
        return new self(
            $idempotencyKey,
            TransactionType::from($data['type']),
            $data['amount'],
            $data['currency'] ?? $defaultCurrency,
        );
    }
}
