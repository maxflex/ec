<?php

namespace App\Http\Resources;

use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Group */
class GroupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            '*',
        ], [
            'teacher_counts' => $this->teacher_counts,
            'lesson_counts' => $this->lesson_counts,
            'first_lesson_date' => $this->first_lesson_date,
            'client_groups_count' => $this->clientGroups()->count(),
            'acts_count' => $this->acts()->count(),
            'user' => new PersonResource($this->user),
            'teachers' => PersonResource::collection($this->teachers),
            'teeth' => $this->getSavedSchedule(),
        ]);
    }
}
