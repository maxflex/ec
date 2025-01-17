<?php

namespace App\Http\Resources;

use App\Models\Client;
use App\Models\ClientParent;
use App\Models\Phone;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Phone */
class CallAppAonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $entity = $this->entity;

        $extra = match ($this->entity_type) {
            ClientParent::class => [
                'client_id' => $entity->client_id,
                'entity' => new PersonResource($entity)
            ],
            Client::class => [
                'client_id' => $this->entity_id,
                'entity' => new PersonResource($entity)
            ],
            Teacher::class => [
                'entity' => new PersonResource($entity)
            ],
            default => [
                'request_id' => $this->entity_id,
            ],
        };

        return extract_fields($this, ['comment'], $extra);
    }
}
