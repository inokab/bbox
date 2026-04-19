<?php

use App\DTOs\TransactionData;
use App\Enums\TransactionType;
use App\Models\Merchant;
use App\Services\ValidatorService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['services.validator.url' => 'http://validator:3000']);
});

it('returns approved when validator approves', function () {
    Http::fake([
        'http://validator:3000/validate' => Http::response(['approved' => true, 'reason' => null]),
    ]);

    $result = (new ValidatorService())->validate(
        Merchant::factory()->create(),
        new TransactionData('key-1', TransactionType::Payment, 1000, 'HUF'),
    );

    expect($result->approved)->toBeTrue()
        ->and($result->reason)->toBeNull();
});

it('returns rejected with reason when validator rejects', function () {
    Http::fake([
        'http://validator:3000/validate' => Http::response(['approved' => false, 'reason' => 'amount_exceeds_limit']),
    ]);

    $result = (new ValidatorService())->validate(
        Merchant::factory()->create(),
        new TransactionData('key-1', TransactionType::Payment, 99_000_000, 'HUF'),
    );

    expect($result->approved)->toBeFalse()
        ->and($result->reason)->toBe('amount_exceeds_limit');
});

it('sends correct payload to the validator', function () {
    Http::fake([
        'http://validator:3000/validate' => Http::response(['approved' => true, 'reason' => null]),
    ]);

    $merchant = Merchant::factory()->create(['currency' => 'HUF']);

    (new ValidatorService())->validate(
        $merchant,
        new TransactionData('key-1', TransactionType::Refund, 5000, 'HUF'),
    );

    Http::assertSent(function ($request) use ($merchant) {
        return $request->url() === 'http://validator:3000/validate'
            && $request['merchant_id'] === $merchant->id
            && $request['type'] === 'refund'
            && $request['amount'] === 5000
            && $request['currency'] === 'HUF';
    });
});

it('returns rejected with validator_unavailable when connection fails', function () {
    Http::fake(fn () => throw new ConnectionException('Connection refused'));

    $result = (new ValidatorService())->validate(
        Merchant::factory()->make(),
        new TransactionData('key-1', TransactionType::Payment, 1000, 'HUF'),
    );

    expect($result->approved)->toBeFalse()
        ->and($result->reason)->toBe('validator_unavailable');
});

it('returns rejected with validator_unexpected_response on non-200', function () {
    Http::fake([
        'http://validator:3000/validate' => Http::response(['error' => 'internal'], 500),
    ]);

    $result = (new ValidatorService())->validate(
        Merchant::factory()->create(),
        new TransactionData('key-1', TransactionType::Payment, 1000, 'HUF'),
    );

    expect($result->approved)->toBeFalse()
        ->and($result->reason)->toBe('validator_unexpected_response');
});

it('returns rejected with validator_unexpected_response when approved field is missing', function () {
    Http::fake([
        'http://validator:3000/validate' => Http::response(['something' => 'unexpected']),
    ]);

    $result = (new ValidatorService())->validate(
        Merchant::factory()->create(),
        new TransactionData('key-1', TransactionType::Payment, 1000, 'HUF'),
    );

    expect($result->approved)->toBeFalse()
        ->and($result->reason)->toBe('validator_unexpected_response');
});
