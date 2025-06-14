<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Request
 */
class RequestListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'status', 'created_at', 'comment', 'comments_count',
            'direction', 'user_id', 'is_verified'
        ], [
            'phones' => PhoneResource::collection($this->phones),
            'responsible_user' => new PersonResource($this->responsibleUser),
            'client' => new PersonResource($this->client),
            'passes' => PassResource::collection($this->passes),
            'associated_clients' => PersonResource::collection($this->getAssociatedClients()),
            'associated_requests_count' => count($this->getAssociatedRequests())
        ]);
    }
}
