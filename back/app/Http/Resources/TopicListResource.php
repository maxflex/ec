<?php

namespace App\Http\Resources;

use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Lesson */
class TopicListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'date', 'time', 'topic', 'is_topic_verified',
        ], [
            'teacher' => new PersonResource($this->teacher),
        ]);
    }
}
