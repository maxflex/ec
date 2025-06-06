<?php

namespace App\Http\Resources;

use App\Enums\LessonStatus;
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
        $nonCancelledLessons = $this->lessons->filter(fn ($lesson) => $lesson->status !== LessonStatus::cancelled);
        $conductedLessons = $this->lessons->filter(fn ($lesson) => $lesson->status === LessonStatus::conducted);

        return extract_fields($this, ['*'], [
            'lessons' => [
                'conducted' => $conductedLessons->where('is_free', false)->count(),
                'conducted_free' => $conductedLessons->where('is_free', true)->count(),
                'planned' => $nonCancelledLessons->where('is_free', false)->count(),
                'planned_free' => $nonCancelledLessons->where('is_free', true)->count(),
            ],
            'client_groups_count' => $this->clientGroups()->count(),
            'first_lesson_date' => $this->lessons->min('date'),
            'user' => new PersonResource($this->user),
            'teachers' => PersonResource::collection($this->teachers),
            'teeth' => $this->getTeeth($this->year),
        ]);
    }
}
