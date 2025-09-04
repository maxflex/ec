<?php

namespace App\Http\Resources;

use App\Models\Client;
use App\Models\Representative;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User|Client|Representative|Teacher
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
        return extract_fields($this, [
            'first_name', 'last_name', 'middle_name', 'photo_url',
            'is_call_notifications', 'is_head_teacher', 'has_grades',
            'client_id', // for representatives
        ], [
            'entity_type' => get_class($this->resource),
        ]);
    }
}
