<?php

namespace App\Http\Resources;

use App\Models\ContractPayment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ContractPayment */
class ContractPaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return extract_fields($this, ['*'], [
            'user' => new PersonResource($this->user),
            'contract' => extract_fields($this->contract, [
                'company',
            ]),
        ]);
    }
}
