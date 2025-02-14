<?php

namespace App\Http\Resources;

use App\Models\ExamScore;
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
            'has_available_exam_scores' => ExamScore::query()
                ->where('client_id', $this->client_id)
                ->whereRaw('NOT EXISTS (
                    select 1 from exam_score_web_review es_wr
                    where es_wr.exam_score_id = exam_scores.id
                )')
                ->exists(),
            'exam_scores' => $this->examScores->pluck('id'),
            'client' => new PersonResource($this->client),
            'user' => new PersonResource($this->user),
        ]);
    }
}
