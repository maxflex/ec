<?php

namespace App\Http\Resources;

use App\Models\ClientGroup;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ClientGroup */
class ClientGroupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $client = $this->contractVersionProgram->contractVersion->contract->client;
        return extract_fields($this, ['contract_id'], [
            'teeth' => $client->getTeeth($this->group->year),
            'client' => new PersonWithPhotoResource($client)
        ]);
    }
}
