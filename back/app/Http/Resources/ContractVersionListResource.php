<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractVersionListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'date', 'programs', 'sum', 'version'
        ], [
            'payments_count' => isset($this->payments_count)
                ? $this->payments_count
                : $this->payments()->count(),
            'contract' => extract_fields($this->contract, [
                'year', 'company'
            ], [
                'client' => new PersonResource($this->contract->client)
            ])
        ]);
    }
}
