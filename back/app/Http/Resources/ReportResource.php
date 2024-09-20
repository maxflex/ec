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
            'year', 'program', 'is_published', 'is_moderated',
            'created_at', 'homework_comment', 'price', 'recommendation_comment',
            'knowledge_level_comment', 'cognitive_ability_comment', 'grade'
        ], [
            'client_lessons' => $this->clientLessons->map(fn($cl) => extract_fields($cl, [
                'status', 'minutes_late', 'is_remote'
            ], [
                'lesson' => extract_fields($cl->lesson, [
                    'date', 'topic'
                ])
            ])),
            'teacher' => new PersonResource($this->teacher),
            'client' => new PersonResource($this->client),
        ]);
    }
}
