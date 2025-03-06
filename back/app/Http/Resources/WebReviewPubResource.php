<?php

namespace App\Http\Resources;

use App\Models\WebReview;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin WebReview */
class WebReviewPubResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $examScores = $this->relationLoaded('examScores') ? $this->examScores : collect();

        return extract_fields($this, [
            'text', 'signature', 'rating',
        ], [
            'exam_scores' => $examScores->map(fn ($es) => extract_fields($es, [
                'score', 'max_score',
            ], [
                'name' => $es->exam->getName(),
            ])),
            'client' => new PersonWithPhotoResource($this->client),
        ]);
    }
}
