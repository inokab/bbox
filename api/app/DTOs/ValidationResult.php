<?php

namespace App\DTOs;

readonly class ValidationResult
{
    public function __construct(public bool $approved, public ?string $reason = null) {}
}
