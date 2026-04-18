<?php

namespace App\Http\Controllers\Merchants;

use App\Actions\CreateMerchant;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMerchantRequest;
use App\Http\Resources\MerchantResource;
use Symfony\Component\HttpFoundation\Response;

class CreateMerchantController extends Controller
{
    public function __invoke(StoreMerchantRequest $request, CreateMerchant $action)
    {
        $merchant = $action->handle($request->validated());

        return response()->json(new MerchantResource($merchant), Response::HTTP_CREATED);
    }
}
