<?php

namespace App\Http\Controllers\Transactions;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\Merchant;

class ListTransactionController extends Controller
{
    public function __invoke(Merchant $merchant)
    {
        $transactions = $merchant->transactions()->latest()->paginate(15);

        return response()->json(TransactionResource::collection($transactions));
    }
}
