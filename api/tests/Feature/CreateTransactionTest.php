<?php

use App\Actions\CreateTransaction;
use App\DTOs\TransactionData;
use App\DTOs\ValidationResult;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Exceptions\CurrencyMismatchException;
use App\Exceptions\IdempotencyConflictException;
use App\Exceptions\InsufficientBalanceException;
use App\Models\Merchant;
use App\Services\ValidatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function approvedAction(): CreateTransaction
{
    $validator = Mockery::mock(ValidatorService::class);
    $validator->shouldReceive('validate')->andReturn(new ValidationResult(true));

    return new CreateTransaction($validator);
}

function rejectedAction(string $reason = 'fraud'): CreateTransaction
{
    $validator = Mockery::mock(ValidatorService::class);
    $validator->shouldReceive('validate')->andReturn(new ValidationResult(false, $reason));

    return new CreateTransaction($validator);
}

it('increments merchant balance on approved payment', function () {
    $merchant = Merchant::factory()->create(['balance' => 5_000, 'currency' => 'HUF']);

    $transaction = approvedAction()->handle(
        $merchant,
        new TransactionData('key-1', TransactionType::Payment, 1_000, 'HUF'),
    );

    expect($transaction->status)->toBe(TransactionStatus::Approved)
        ->and($merchant->fresh()->balance)->toBe(6_000);
});

it('decrements merchant balance on approved refund', function () {
    $merchant = Merchant::factory()->create(['balance' => 5_000, 'currency' => 'HUF']);

    $transaction = approvedAction()->handle(
        $merchant,
        new TransactionData('key-1', TransactionType::Refund, 1_000, 'HUF'),
    );

    expect($transaction->status)->toBe(TransactionStatus::Approved)
        ->and($merchant->fresh()->balance)->toBe(4_000);
});

it('throws InsufficientBalanceException when refund exceeds balance', function () {
    $merchant = Merchant::factory()->create(['balance' => 500, 'currency' => 'HUF']);

    approvedAction()->handle(
        $merchant,
        new TransactionData('key-1', TransactionType::Refund, 1_000, 'HUF'),
    );
})->throws(InsufficientBalanceException::class);

it('does not change balance when refund would cause insufficient balance', function () {
    $merchant = Merchant::factory()->create(['balance' => 500, 'currency' => 'HUF']);

    try {
        approvedAction()->handle(
            $merchant,
            new TransactionData('key-1', TransactionType::Refund, 1_000, 'HUF'),
        );
    } catch (InsufficientBalanceException) {
    }

    expect($merchant->fresh()->balance)->toBe(500);
});

it('saves transaction as rejected and does not change balance when validator rejects', function () {
    $merchant = Merchant::factory()->create(['balance' => 5_000, 'currency' => 'HUF']);

    $transaction = rejectedAction('amount_exceeds_limit')->handle(
        $merchant,
        new TransactionData('key-1', TransactionType::Payment, 1_000, 'HUF'),
    );

    expect($transaction->status)->toBe(TransactionStatus::Rejected)
        ->and($transaction->reason)->toBe('amount_exceeds_limit')
        ->and($merchant->fresh()->balance)->toBe(5_000);
});

it('saves transaction as rejected when validator is unavailable', function () {
    $merchant = Merchant::factory()->create(['balance' => 5_000, 'currency' => 'HUF']);

    $transaction = rejectedAction('validator_unavailable')->handle(
        $merchant,
        new TransactionData('key-1', TransactionType::Payment, 1_000, 'HUF'),
    );

    expect($transaction->status)->toBe(TransactionStatus::Rejected)
        ->and($transaction->reason)->toBe('validator_unavailable')
        ->and($merchant->fresh()->balance)->toBe(5_000);
});

it('throws CurrencyMismatchException when transaction currency differs from merchant currency', function () {
    $merchant = Merchant::factory()->create(['currency' => 'HUF']);

    approvedAction()->handle(
        $merchant,
        new TransactionData('key-1', TransactionType::Payment, 1_000, 'EUR'),
    );
})->throws(CurrencyMismatchException::class);

it('returns the existing transaction on duplicate idempotency key', function () {
    $merchant = Merchant::factory()->create(['balance' => 5_000, 'currency' => 'HUF']);
    $data = new TransactionData('idempotent-key', TransactionType::Payment, 1_000, 'HUF');

    $first = approvedAction()->handle($merchant, $data);
    $second = approvedAction()->handle($merchant, $data);

    expect($second->id)->toBe($first->id)
        ->and($merchant->fresh()->balance)->toBe(6_000);
});

it('throws IdempotencyConflictException when another merchant already used the same key', function () {
    $merchantA = Merchant::factory()->create(['balance' => 5_000, 'currency' => 'HUF']);
    $merchantB = Merchant::factory()->create(['balance' => 5_000, 'currency' => 'HUF']);

    approvedAction()->handle(
        $merchantA,
        new TransactionData('shared-key', TransactionType::Payment, 1_000, 'HUF'),
    );

    approvedAction()->handle(
        $merchantB,
        new TransactionData('shared-key', TransactionType::Payment, 500, 'HUF'),
    );
})->throws(IdempotencyConflictException::class);
