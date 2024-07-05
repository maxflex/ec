<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientReviewListResource extends JsonResource
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
                'program',
            ], [
                'teacher' => new PersonResource($this->teacher),
                'client' => new PersonResource($this->client),
            ]);
        }
        // fake
        return extract_fields($this, [
            'program'
        ], [
            'id' => uniqid(),
            'teacher' => new PersonResource($this->teacher),
            'client' => new PersonResource($this->client),
        ]);
    }
}
