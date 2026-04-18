<?php

namespace App\Http\Controllers\Merchants;

use App\Http\Controllers\Controller;
use App\Http\Resources\MerchantResource;
use App\Models\Merchant;
use Illuminate\Http\Request;

class ShowMerchantController extends Controller
{
    public function __invoke(Merchant $merchant)
    {
        return response()->json(new MerchantResource($merchant));
    }
}
