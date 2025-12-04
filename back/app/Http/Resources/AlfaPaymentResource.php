<?php

namespace App\Http\Resources;

use App\Models\ContractPayment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ContractPayment */
class AlfaPaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return extract_fields($this, ['*'], [
            'client' => new PersonResource($this->contract->client),
            'contract' => extract_fields($this->contract, ['company']),
        ]);
    }
}
