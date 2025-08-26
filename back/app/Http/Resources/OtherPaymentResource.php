<?php

namespace App\Http\Resources;

use App\Models\OtherPayment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OtherPayment
 */
class OtherPaymentResource extends JsonResource
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
        ]);
    }
}
