<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Request
 */
class RequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'status', 'direction', 'responsible_user_id', 'created_at',
            'yandex_id', 'google_id', 'ip', 'client_id', 'source',
        ], [
            'user' => new PersonResource($this->user),
            'phones' => PhoneResource::collection($this->phones),
            'associated_clients' => ClientWithContractsResource::collection($this->getAssociatedClients()),
        ]);
    }
}
