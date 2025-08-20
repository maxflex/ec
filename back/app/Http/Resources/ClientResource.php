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
            'head_teacher_id', 'photo_url', 'created_at',
            'passport', 'is_remote', 'directions', 'email',
            'heard_about_us', 'mark_sheet', 'is_risk',
        ], [
            'entity_type' => Client::class,
            'head_teacher' => new PersonResource($this->headTeacher),
            'user' => new PersonResource($this->user),
            'parent' => new ParentResource($this->parent),
            'phones' => PhoneResource::collection($this->phones),
        ]);
    }
}
