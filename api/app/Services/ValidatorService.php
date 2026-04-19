<?php

namespace App\Services;

use App\DTOs\TransactionData;
use App\DTOs\ValidationResult;
use App\Models\Merchant;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ValidatorService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.validator.url');
    }

    public function validate(Merchant $merchant, TransactionData $data): ValidationResult
    {
        try {
            $response = Http::timeout(5)->post("{$this->baseUrl}/validate", [
                'merchant_id' => $merchant->id,
                'type' => $data->type->value,
                'amount' => $data->amount,
                'currency' => $data->currency ?? $merchant->currency,
            ]);

            return $this->resolveValidationResult($merchant->id, $response);
        } catch (ConnectionException $e) {
            Log::warning('Validator service unavailable', [
                'merchant_id' => $merchant->id,
                'error'       => $e->getMessage(),
            ]);

            return new ValidationResult(false, 'validator_unavailable');
        }
    }

    private function resolveValidationResult(string $merchantId, Response $response): ValidationResult
    {
        $body = $response->json();

        if (!$response->successful() || !isset($body['approved'])) {
            Log::warning('Validator service returned unexpected response', [
                'merchant_id' => $merchantId,
                'status'      => $response->status(),
                'body'        => $body,
            ]);

            return new ValidationResult(false, 'validator_unexpected_response');
        }

        Log::info('Validator response', [
            'merchant_id' => $merchantId,
            'approved'    => $body['approved'],
            'reason'      => $body['reason'] ?? null,
        ]);

        return new ValidationResult($body['approved'], $body['reason'] ?? null);
    }
}
