<?php

namespace App\Http\Resources;

use App\Models\WebReview;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin WebReview */
class WebReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'text', 'signature', 'rating', 'client_id', 'created_at',
            'programs', 'is_published',
        ], [
            'exam_scores' => $this->getExamScores(false),
            'has_photo' => $this->client->photo()->exists(),
            'client' => new PersonResource($this->client),
            'user' => new PersonResource($this->user),
        ]);
    }
}
