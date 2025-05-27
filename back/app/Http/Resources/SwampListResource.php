<?php

namespace App\Http\Resources;

use App\Models\Client;
use App\Models\ContractVersionProgram;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ContractVersionProgram
 */
class SwampListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'contract_id', 'year', 'status', 'program',
            'total_price', 'total_price_passed',
        ], [
            'total_lessons' => $this->prices->sum('lessons'),
            'group_id' => $this->clientGroup?->group_id,
            'client' => new PersonResource(Client::find($this->client_id)),
        ]);
    }
}
