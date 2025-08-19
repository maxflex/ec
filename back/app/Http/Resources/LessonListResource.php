<?php

namespace App\Http\Resources;

use App\Enums\LessonStatus;
use App\Models\Lesson;
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
            'group' => extract_fields($this->group, [
                'program', 'zoom',
            ], [
                'students_count' => $this->status === LessonStatus::conducted
                    ? $this->clientLessons()->count()
                    : $this->group->clientGroups()->count(),
            ]),
        ]);
    }
}
