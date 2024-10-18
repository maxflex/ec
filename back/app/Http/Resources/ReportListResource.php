<?php

namespace App\Http\Resources;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Report
 */
class ReportListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // real report
        if ($this->id) {
            return extract_fields($this, [
                'year', 'program', 'is_published', 'is_moderated',
                'price', 'created_at', 'grade'
            ], [
                'lessons_count' => $this->lessons->count(),
                'teacher' => new PersonResource($this->teacher),
                'client' => new PersonResource($this->client),
            ]);
        }
        // fake report
        return extract_fields($this, [
            'year', 'program', 'lessons_count'
        ], [
            'id' => uniqid(),
            'teacher' => new PersonResource($this->teacher),
            'client' => new PersonResource($this->client),
        ]);
    }
}
