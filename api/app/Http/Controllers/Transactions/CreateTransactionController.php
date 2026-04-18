<?php

namespace App\Http\Controllers\Transactions;

use App\Actions\CreateTransaction;
use App\DTOs\TransactionData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Merchant;
use Symfony\Component\HttpFoundation\Response;

class CreateTransactionController extends Controller
{
    /**
     * @throws \Throwable
     */
    public function __invoke(StoreTransactionRequest $request, Merchant $merchant, CreateTransaction $action)
    {
        $data = TransactionData::fromRequest($request->validated(), $merchant->currency, $request->header('Idempotency-Key'));

        $transaction = $action->handle($merchant, $data);

        $status = $transaction->wasRecentlyCreated
            ? Response::HTTP_CREATED
            : Response::HTTP_OK;

        return response()->json(new TransactionResource($transaction), $status);
    }
}
