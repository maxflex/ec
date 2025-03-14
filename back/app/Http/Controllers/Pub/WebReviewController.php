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
        $subject = $request->input('subject');
        $grade = intval($request->input('grade'));
        $seed = $request->input('seed');

        $query = match ($grade) {
            20 => $this->getExternalOrSchoolQuery($seed, false),
            14 => $this->getExternalOrSchoolQuery($seed, true),
            default => $this->getCoursesQuery($subject, $grade, $seed),
        };

        return $this->handleIndexRequest($request, $query, WebReviewPubResource::class);
    }

    private function getExternalOrSchoolQuery($seed, bool $isExternal)
    {
        $programs = $isExternal ? Program::getAllExternal() : Program::getAllSchool();

        return WebReview::with('client', 'client.photo')
            ->where('is_published', true)
            ->whereExists(fn ($q) => $q->selectRaw(1)
                ->from('web_review_programs as wrp')
                ->whereColumn('wrp.web_review_id', 'web_reviews.id')
                ->whereIn('wrp.program', $programs)
            )
            ->inRandomOrder($seed)
            ->select('web_reviews.*');
    }

    private function getCoursesQuery($subject, $grade, $seed)
    {
        $program = Program::tryFrom($subject.$grade);

        if (! $program) {
            return $this->getReviewsQuery($seed);
        }

        $exam = $program->getExam();

        $query = WebReview::with('client', 'client.photo')
            ->leftJoin('exam_scores as es', fn ($join) => $join
                ->on('es.client_id', '=', 'web_reviews.client_id')
                ->where('es.is_published', true)
                ->where('es.exam', $exam->value)
            )
            ->join('web_review_programs as wrp', fn ($join) => $join
                ->on('wrp.web_review_id', '=', 'web_reviews.id')
                ->where('wrp.program', '=', $program->value)
            )
            ->where('web_reviews.is_published', true)
            ->orderByRaw('
                 CASE
                    WHEN es.id IS NULL THEN 0
                    WHEN (es.score / es.max_score) > 0.8 THEN 6
                    WHEN (es.score / es.max_score) > 0.7 THEN 5
                    WHEN (es.score / es.max_score) > 0.6 THEN 4
                    WHEN (es.score / es.max_score) > 0.5 THEN 3
                    WHEN (es.score / es.max_score) > 0.4 THEN 2
                    ELSE 1
                END DESC
            ')
            ->inRandomOrder($seed)
            ->select('web_reviews.*');

        if (! $query->exists()) {
            return $this->getReviewsQuery($seed);
        }

        return $query;
    }

    /**
     * Чтобы хоть какие-то отзывы отображались (экстернат, старшая школа)
     */
    private function getReviewsQuery($seed)
    {
        // в старой системе отображались только отзывы с фотками
        return WebReview::with('client', 'client.photo')
            ->where('is_published', true)
            ->inRandomOrder($seed);
    }
}
