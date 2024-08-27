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
        return extract_fields($this->entity, [
            'telegram_id', 'is_call_notifications', 'first_name',
            'last_name', 'middle_name', 'photo_url', 'number',
            'entity_type'
        ], [
            'id' => $this->entity_id,
        ]);
    }
}
