<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
                'created_at'
            ], [
                'teacher' => new PersonResource($this->teacher),
                'client' => new PersonResource($this->client),
            ]);
        }
        // fake report
        return extract_fields($this, [
            'year', 'program', 'lessons_since_last_report'
        ], [
            'id' => uniqid(),
            'teacher' => new PersonResource($this->teacher),
            'client' => new PersonResource($this->client),
        ]);
    }
}
