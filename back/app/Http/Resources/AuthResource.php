<?php

namespace App\Http\Resources;

use App\Models\Phone;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * PHONE as user
 * @mixin Phone
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

        return extract_fields($this, [
            'telegram_id', 'entity_type', 'number',
        ],
            extract_fields($entity, [
                'first_name', 'last_name', 'middle_name', 'photo_url'
            ], [
                'id' => $this->entity_id,
                'is_call_notifications' => $entity->is_call_notifications ?? false,
            ])
        );
    }
}
