<?php

namespace App\Http\Resources;

use App\Models\Phone;
use App\Models\Teacher;
use App\Models\User;
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

        $extra = ['first_name', 'last_name', 'middle_name', 'photo_url'];

        switch ($this->entity_type) {
            case Teacher::class:
                $extra = [
                    ...$extra,
                    'is_head_teacher'
                ];
                break;

            case User::class:
                $extra = [
                    ...$extra,
                    'is_call_notifications'
                ];
                break;
        }

        return extract_fields($this, [
            'telegram_id', 'entity_type', 'number',
        ], extract_fields($entity, $extra, [
                'id' => $this->entity_id
            ])
        );
    }
}
