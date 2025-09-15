<?php

namespace App\Http\Resources;

use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Lesson
 */
class CurrentLessonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'time', 'time_end', 'cabinet',
        ], [
            'teacher' => new PersonResource($this->teacher),
            'group' => extract_fields($this->group, [
                'program',
            ]),
        ]);
    }
}
