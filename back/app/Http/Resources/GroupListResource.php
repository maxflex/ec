<?php

namespace App\Http\Resources;

use App\Enums\LessonStatus;
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
        $nonCancelledLessons = $this->lessons->filter(fn ($lesson) => $lesson->status !== LessonStatus::cancelled);
        $conductedLessons = $this->lessons->filter(fn ($lesson) => $lesson->status === LessonStatus::conducted);
        $teachers = $this->teachers;
        $countsByTeacher = [];
        foreach ($teachers as $teacher) {
            $countsByTeacher[$teacher->id] = $this->lessons
                ->where('teacher_id', $teacher->id)
                ->where('status', '<>', LessonStatus::cancelled)
                ->count();
        }

        return extract_fields($this, [
            'program', 'client_groups_count', 'zoom', 'lessons_planned',
        ], [
            'lessons' => [
                'conducted' => $conductedLessons->where('is_free', false)->count(),
                'conducted_free' => $conductedLessons->where('is_free', true)->count(),
                'planned' => $nonCancelledLessons->where('is_free', false)->count(),
                'planned_free' => $nonCancelledLessons->where('is_free', true)->count(),
            ],
            'counts_by_teacher' => (object) $countsByTeacher,
            'first_lesson_date' => $this->lessons->min('date'),
            'teachers' => PersonResource::collection($teachers),
            'teeth' => $this->getTeeth($this->year),
        ]);
    }
}
