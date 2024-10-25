<?php

namespace App\Http\Resources;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Client */
class ClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'first_name', 'last_name', 'middle_name', 'branches',
            'birthdate', 'head_teacher_id', 'photo_url', 'created_at',
            'passport', 'is_remote'
        ], [
            'head_teacher' => new PersonResource($this->headTeacher),
            'user' => new PersonResource($this->user),
            'parent' => new ParentResource($this->parent),
            'phones' => PhoneListResource::collection($this->phones),
        ]);
    }
}
