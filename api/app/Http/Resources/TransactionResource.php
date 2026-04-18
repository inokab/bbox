<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'merchant_id'     => $this->merchant_id,
            'idempotency_key' => $this->idempotency_key,
            'type'            => $this->type,
            'amount'          => $this->amount,
            'currency'        => $this->currency,
            'status'          => $this->status,
            'created_at'      => $this->created_at,
        ];
    }
}
