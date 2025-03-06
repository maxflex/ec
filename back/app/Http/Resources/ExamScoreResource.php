<?php

namespace App\Http\Resources;

use App\Models\ExamScore;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ExamScore */
class ExamScoreResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return extract_fields($this, ['*'], [
            'web_review_id' => count($this->webReviews) ? $this->webReviews[0]->id : null,
            'user' => new PersonResource($this->user),
            'client' => new PersonResource($this->client),
        ]);
    }
}
