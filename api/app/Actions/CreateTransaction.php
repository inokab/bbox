<?php

namespace App\Actions;

use App\DTOs\TransactionData;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Exceptions\CurrencyMismatchException;
use App\Exceptions\InsufficientBalanceException;
use App\Models\Merchant;
use App\Models\Transaction;
use App\Services\ValidatorService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

readonly class CreateTransaction
{
    public function __construct(private ValidatorService $validatorService) {}

    /**
     * @throws \Throwable
     */
    public function handle(Merchant $merchant, TransactionData $data): Transaction
    {
        if (isset($data->currency) && $data->currency !== $merchant->currency) {
            throw new CurrencyMismatchException($merchant->currency, $data->currency);
        }

        $existing = Transaction::where('idempotency_key', $data->idempotencyKey)->first();

        if ($existing) {
            return $existing;
        }

        $validationResult = $this->validatorService->validate($merchant, $data);

        $status = $validationResult->approved ? TransactionStatus::Approved : TransactionStatus::Rejected;

        try {
            return DB::transaction(function () use ($merchant, $data, $status) {
                if ($status === TransactionStatus::Approved) {
                    $this->applyBalanceChange($merchant, $data);
                }

                return Transaction::create([
                    'merchant_id'     => $merchant->id,
                    'idempotency_key' => $data->idempotencyKey,
                    'type'            => $data->type,
                    'amount'          => $data->amount,
                    'currency'        => $data->currency,
                    'status'          => $status,
                ]);
            });
        } catch (UniqueConstraintViolationException) {
            return Transaction::where('idempotency_key', $data->idempotencyKey)->firstOrFail();
        }
    }

    /**
     * @throws InsufficientBalanceException
     */
    private function applyBalanceChange(Merchant $merchant, TransactionData $data): void
    {
        $merchant = Merchant::lockForUpdate()->findOrFail($merchant->id);

        if ($data->type === TransactionType::Refund) {
            if ($merchant->balance < $data->amount) {
                throw new InsufficientBalanceException();
            }

            $merchant->decrement('balance', $data->amount);
        } else {
            $merchant->increment('balance', $data->amount);
        }
    }
}
