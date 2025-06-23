<?php

namespace App\Http\Resources;

use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Group */
class GroupListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'program', 'client_groups_count', 'zoom', 'lessons_planned',
            'teacher_counts', 'lesson_counts', 'first_lesson_date',
        ], [
            'teachers' => PersonResource::collection($this->teachers),
            'teeth' => $this->getTeeth($this->year),
        ]);
    }
}
