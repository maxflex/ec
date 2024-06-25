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
            'status', 'start_at', 'time_end', 'cabinet',
            'is_unplanned', 'is_first'
        ], [
            'group' => extract_fields($this->group, [
                'program'
            ], [
                'contracts_count' =>
                $this->status === LessonStatus::conducted
                    ? $this->contractLessons->count()
                    : $this->group->contracts()->count()
            ]),
            'teacher' =>  new PersonResource($this->teacher),
        ]);
    }
}
