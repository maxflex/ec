<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupContractResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $client = $this->contract->client;
        return extract_fields($this, ['contract_id'], [
            'teeth' => $client->getTeeth(),
            'client' => new PersonWithPhotoResource($client)
        ]);
    }
}
