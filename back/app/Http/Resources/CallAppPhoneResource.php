<?php

namespace App\Http\Resources;

use App\Models\ClientParent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CallAppPhoneResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $entity = $this->entity;

        // У родителя ссылка должна вести на клиента
        if ($this->entity_type === ClientParent::class) {
            $entity->id = $this->entity->client->id;
        }

        return extract_fields($this, [
            'entity_type', 'comment'
        ], [
            'person' => new PersonResource($entity)
        ]);
    }
}
