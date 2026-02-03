<?php

namespace App\Http\Resources;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Report */
class ReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'year', 'program', 'status', 'is_read', 'created_at', 'homework_comment',
            'price', 'recommendation_comment', 'comment', 'knowledge_level_comment',
            'cognitive_ability_comment', 'grade',
        ], [
            'client_lessons' => JournalResource::collection($this->client_lessons),
            'teacher' => new TeacherListResource($this->teacher),
            'client' => new PersonResource($this->client),
        ]);
    }
}
