<?php

namespace App\Http\Resources;

use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Teacher */
class TeacherResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'photo_url', 'is_head_teacher', '*'
        ], [
            'phones' => PhoneListResource::collection($this->phones),
            'user' => new PersonResource($this->user),
        ]);
    }
}
