<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupCandidateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $contract = $this->contractVersion->contract;
        return extract_fields($this, [
            'program', 'is_closed'
        ], [
            'contract_id' => $contract->id,
            'client' => new PersonResource($contract->client)
        ]);
    }
}
