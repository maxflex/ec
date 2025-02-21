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
        $examScore = $this->examScores?->first();

        return extract_fields($this, [
            'text', 'signature', 'rating',
        ], [
            'exam_score' => $this->when((bool) $examScore, fn () => extract_fields($examScore, [
                'score', 'max_score',
            ], [
                'name' => $examScore->exam->getName(),
            ])),
            'client' => new PersonWithPhotoResource($this->client),
        ]);
    }
}
