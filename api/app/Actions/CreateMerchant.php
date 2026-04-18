<?php

namespace App\Actions;

use App\Models\Merchant;

class CreateMerchant
{
    public function handle(array $data): Merchant
    {
        return Merchant::create($data);
    }
}
