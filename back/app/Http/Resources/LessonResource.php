<?php

namespace App\Http\Resources;

use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Lesson */
class LessonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'status', 'date', 'time', 'cabinet', 'teacher_id', 'price', 'created_at',
            'conducted_at', 'topic', 'is_topic_verified', 'is_unplanned', 'quarter',
            'files', 'homework', 'group_id'
        ], [
            'teacher' => new PersonResource($this->teacher),
            'user' => new PersonResource($this->user),
        ]);
    }
}
