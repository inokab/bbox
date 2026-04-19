<?php

namespace Database\Factories;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'merchant_id'     => MerchantFactory::new(),
            'idempotency_key' => fake()->uuid(),
            'type'            => fake()->randomElement(TransactionType::cases()),
            'amount'          => fake()->numberBetween(100, 100_000),
            'currency'        => 'HUF',
            'status'          => TransactionStatus::Approved,
            'reason'          => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(['status' => TransactionStatus::Approved]);
    }

    public function rejected(): static
    {
        return $this->state(['status' => TransactionStatus::Rejected]);
    }

    public function payment(): static
    {
        return $this->state(['type' => TransactionType::Payment]);
    }

    public function refund(): static
    {
        return $this->state(['type' => TransactionType::Refund]);
    }
}
