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
            'yandex_id', 'google_id', 'ip', 'client_id'
        ], [
            'user' => new PersonResource($this->user),
            'phones' => PhoneListResource::collection($this->phones),
            'clients' => ClientWithContractsResource::collection($this->getClients())
        ]);
    }
}
