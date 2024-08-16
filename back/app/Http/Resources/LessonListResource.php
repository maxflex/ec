<?php

namespace App\Http\Resources;

use App\Enums\LessonStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'status', 'date', 'time', 'time_end',
            'cabinet', 'is_unplanned', 'is_first',
        ], [
            'clientLesson' => $this->when(
                $this->clientLesson,
                extract_fields($this->clientLesson, [
                    'status', 'scores', 'minutes_late', 'is_remote'
                ])
            ),
            'group' => extract_fields($this->group, [
                'program'
            ], [
                'students_count' =>
                $this->status === LessonStatus::conducted
                    ? $this->clientLessons()->count()
                    : $this->group->clientGroups()->count()
            ]),
            'teacher' =>  new PersonResource($this->teacher),
        ]);
    }
}
