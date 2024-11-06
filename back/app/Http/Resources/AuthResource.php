<?php

namespace App\Http\Resources;

use App\Models\Client;
use App\Models\Grade;
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

        switch ($this->entity_type) {
            case Teacher::class:
                $extra = [
                    'is_head_teacher' => $entity->is_head_teacher,
                ];
                break;

            case Client::class:
                $extra = [
                    'has_grades' => Grade::where('client_id', $entity->id)->exists()
                ];
                break;

            case User::class:
                $extra = [
                    'is_call_notifications' => $entity->is_call_notifications,
                ];
                break;
        }

        return extract_fields($this, [
            'telegram_id', 'entity_type', 'number',
        ], extract_fields($entity, [
                'first_name', 'last_name', 'middle_name', 'photo_url'
            ], [
                ...$extra,
                'id' => $this->entity_id,
            ])
        );
    }
}
