<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'status', 'start_at', 'cabinet', 'teacher_id', 'price', 'created_at',
            'conducted_at', 'topic', 'is_topic_verified', 'is_unplanned'
        ], [
            'teacher' => new PersonResource($this->teacher),
            'user' => new PersonResource($this->user),
        ]);
    }
}
