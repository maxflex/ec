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
        return extract_fields($this, [
            'first_name', 'last_name', 'middle_name', 'created_at',
            'photo_url', 'is_active', 'is_call_notifications'
        ], [
            'phones' => PhoneListResource::collection($this->phones)
        ]);
    }
}
