<?php

namespace App\Http\Resources;

use App\Models\ContractVersion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ContractVersion */
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
            'date', 'sum', 'seq', 'is_active', 'direction_counts',
            'sum_change', 'created_at',
        ], [
            'programs_count' => $this->programs_count ?? $this->programs()->count(),
            'payments_count' => $this->payments_count ?? $this->payments()->count(),
            'contract' => extract_fields($this->contract, [
                'year', 'company', 'source',
            ], [
                'client' => new PersonResource($this->contract->client),
            ]),
        ]);
    }
}
