<?php

namespace App\Actions;

use App\DTOs\MerchantData;
use App\Models\Merchant;

class CreateMerchant
{
    public function handle(MerchantData $data): Merchant
    {
        return Merchant::create([
            'name' => $data->name,
            'email' => $data->email,
            'currency' => $data->currency,
        ]);
    }
}
