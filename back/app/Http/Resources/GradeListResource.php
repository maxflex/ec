<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GradeListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // real
        if ($this->id) {
            return extract_fields($this, [
                'year', 'program', 'grade', 'quarter'
            ], [
                'client' => new PersonResource($this->client),
            ]);
        }
        // fake
        return extract_fields($this, [
            'year', 'program', 'quarter'
        ], [
            'id' => uniqid(),
            'client' => new PersonResource($this->client),
        ]);
    }
}
