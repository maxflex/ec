<?php

namespace App\Http\Resources;

use App\Models\ExamScore;
use App\Models\WebReview;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin WebReview */
class WebReviewPubResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $examScores = $this->examScores
            ->sortByDesc('score')
            ->values()
            ->map(fn (ExamScore $es) => extract_fields($es, [
                'score', 'max_score',
            ], [
                'name' => $es->exam->getName(),
            ]));

        return extract_fields($this, [
            'text', 'signature', 'rating',
        ], [
            'exam_scores' => $examScores,
            'client' => new PersonWithPhotoResource($this->client),
        ]);
    }
}
