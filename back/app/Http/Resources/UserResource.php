<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->entity_id,
            'telegram_id' => $this->telegram_id,
            'entity_type' => $this->entity_type,
            'entity' => extract_fields($this->entity, [
                'first_name', 'last_name', 'middle_name'
            ])
        ];
    }
}
