<?php

namespace App\DTOs;

readonly class MerchantData
{
    public function __construct(
        public string $name,
        public string $email,
        public string $currency,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            $data['name'],
            $data['email'],
            $data['currency'] ?? 'HUF',
        );
    }
}
