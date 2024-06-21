<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * PHONE as user
 */
class AuthResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $entity = $this->entity;
        return [
            'id' => $this->entity_id,
            'telegram_id' => $this->telegram_id,
            'entity_type' => $this->entity_type,
            'first_name' => $entity->first_name,
            'last_name' => $entity->last_name,
            'middle_name' => $entity->middle_name,
            'number' => $this->number,
            'photo_url' => $entity->photo_url,
        ];
    }
}
