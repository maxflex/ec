<?php

namespace App\Http\Resources;

use App\Models\ContractVersion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ContractVersion */
class ContractVersionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'version' => $this->version,
            'programs' => ContractVersionProgramResource::collection($this->programs),
            'payments' => $this->payments,
            'user' => new PersonResource($this->user),
            'contract' => extract_fields($this->contract, [
                'year', 'company'
            ])
        ]);
    }
}
