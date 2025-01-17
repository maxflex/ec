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
        switch ($this->entity_type) {
            case ClientParent::class:
                $extra = [
                    'client' => new PersonResource($this->entity->client),
                    'entity' => new PersonResource($this->entity)
                ];
                break;

            case Client::class:
                $client = new PersonResource($this->entity);
                $extra = [
                    'client' => $client,
                    'entity' => $client,
                ];
                break;

            case Teacher::class:
                $extra = [
                    'entity' => new PersonResource($this->entity)
                ];
                break;

            // Request
            default:
                $extra = [
                    'request_id' => $this->entity_id,
                ];
        }

        return extract_fields($this, [
            'comment'
        ], $extra);
    }
}
