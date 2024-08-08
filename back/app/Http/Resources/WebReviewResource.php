<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WebReviewResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'text', 'signature', 'rating', 'is_published',
            'client_id', 'created_at',
        ], [
            'exam_score_id' => $this->examScore?->id,
            'client' => new PersonResource($this->client),
            'user' => new PersonResource($this->user)
        ]);
    }
}
