<?php

namespace App\Http\Controllers\Pub;

use App\Enums\Program;
use App\Http\Controllers\Controller;
use App\Http\Resources\WebReviewPubResource;
use App\Models\WebReview;
use Illuminate\Http\Request;

class WebReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = WebReview::with('client', 'client.photo');

        $subject = $request->input('subject');
        $grade = intval($request->input('grade'));
        $seed = $request->input('seed');

        if ($grade === 11 && in_array($subject, [
            'math',
            'phys',
            'inf',
        ])) {
            $program = match ($subject) {
                'phys' => Program::phys11,
                'inf' => Program::inf11,
                default => Program::math11,
            };

            $exam = $program->getExam();

            $query->with('examScores', fn ($q) => $q->where('exam', $exam))
                ->selectRaw('web_reviews.*')
                ->join(
                    'exam_score_web_review as es_wr',
                    'es_wr.web_review_id',
                    '=',
                    'web_reviews.id'
                )
                ->join('exam_scores as es',
                    fn ($join) => $join
                        ->on('es.id', '=', 'es_wr.exam_score_id')
                        ->where('es.exam', $exam)
                )
                ->where('es.score', '>=', 40)
                ->whereHas('client.photo')
                ->where('is_published', true)
                ->orderByRaw('
                     CASE
                        WHEN (es.score / es.max_score) > 0.8 THEN 6
                        WHEN (es.score / es.max_score) > 0.7 THEN 5
                        WHEN (es.score / es.max_score) > 0.6 THEN 4
                        WHEN (es.score / es.max_score) > 0.5 THEN 3
                        WHEN (es.score / es.max_score) > 0.4 THEN 2
                        ELSE 1
                    END DESC
                ')
                ->inRandomOrder($seed);
        } else {
            // в старой системе отображались только отзывы с фотками
            $query
                ->whereHas('client.photo')
                ->where('rating', 5)
                ->inRandomOrder($seed);
        }

        // $this->filter($request, $query);

        return $this->handleIndexRequest($request, $query, WebReviewPubResource::class);
    }

    // Не используется
    public function show(WebReview $webReview)
    {
        return new WebReviewPubResource($webReview);
    }
}
