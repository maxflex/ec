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
            'heard_about_us', 'mark_sheet', 'is_risk', 'bio',
        ], [
            'current_lesson' => new CurrentLessonResource($this->current_lesson),
            'schedule' => $this->schedule[current_academic_year()] ?? null,
            'entity_type' => Client::class,
            'head_teacher' => new PersonResource($this->headTeacher),
            'user' => new PersonResource($this->user),
            'representative' => new RepresentativeResource($this->representative),
            'phones' => PhoneResource::collection($this->phones),
        ]);
    }
}
