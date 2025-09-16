<?php

namespace App\Http\Resources;

use App\Enums\LessonStatus;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Lesson */
class LessonListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isAdmin = get_class(auth()->user()) === User::class;

        return extract_fields($this, [
            'status', 'date', 'time', 'time_end', 'seq',
            'cabinet', 'is_unplanned', 'is_first', 'quarter',
            'topic', 'is_topic_verified', 'is_free', 'homework',
            'is_need_conduct',
        ], [
            'has_files' => count($this->files) > 0,
            'teacher' => new PersonResource($this->teacher),
            'client_lesson' => $this->when(
                $request->has('client_id'),
                fn () => $this->getClientLesson(intval($request->client_id)),
            ),
            'draft_students_count' => $this->when(
                $isAdmin,
                fn () => $this->group->draft_students_count
            ),
            // если занятие проведено: берем фактическое из client_lessons
            // иначе берем из client_groups
            'students_count' => $this->status === LessonStatus::conducted
                ? $this->clientLessons()->count()
                : $this->group->clientGroups()->count(),
            'group' => extract_fields($this->group, [
                'program', 'zoom', 'letter', 'draft_students_count',
            ]),
        ]);
    }
}
