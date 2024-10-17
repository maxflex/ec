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
        $client = $this->client;
        $clients = $this->getClients(true);
        $allClients = $clients;
        if ($client) {
            $allClients[] = $client;
        }
        return extract_fields($this, [
            'status', 'created_at', 'comment', 'comments_count',
            'direction', 'user_id', 'is_verified'
        ], [
            'phones' => PhoneListResource::collection($this->phones),
            'responsible_user' => new PersonResource($this->responsibleUser),
            'client' => new PersonResource($client),
            'passes' => PassResource::collection($this->passes),
            'clients' => PersonResource::collection($clients),
            'associated_requests_count' => count($this->getAssociatedRequests($allClients))
        ]);
    }
}
