<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'created_at', 'homework_comment', 'price'
        ], [
            'teacher' => new PersonResource($this->teacher),
            'client' => new PersonResource($this->client),
        ]);
    }
}
