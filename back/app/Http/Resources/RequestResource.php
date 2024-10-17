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
            'yandex_id', 'google_id', 'ip'
        ], [
            'user' => new PersonResource($this->user),
            'client' => new ClientListResource($this->client),
            'phones' => PhoneListResource::collection($this->phones),
        ]);
    }
}
