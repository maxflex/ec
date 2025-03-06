<?php

namespace App\Http\Controllers\Pub;

use App\Enums\Program;
use App\Http\Controllers\Controller;
use App\Http\Resources\WebReviewPubResource;
use App\Models\WebReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WebReviewController extends Controller
{
    public function index(Request $request)
    {
        $subject = $request->input('subject');
        $grade = intval($request->input('grade'));
        $seed = $request->input('seed');

        $query = match ($grade) {
            20 => $this->getReviewsQuery($seed),
            14 => $this->getExternalQuery($seed),
            default => $this->getCoursesQuery($subject, $grade, $seed),
        };

        return $this->handleIndexRequest($request, $query, WebReviewPubResource::class);
    }

    /**
     * Чтобы хоть какие-то отзывы отображались (экстернат, старшая школа)
     */
    private function getReviewsQuery($seed)
    {
        // в старой системе отображались только отзывы с фотками
        return WebReview::with('client', 'client.photo')
            ->whereHas('client.photo')
            ->where('rating', 5)
            ->inRandomOrder($seed);
    }

    private function getExternalQuery($seed)
    {
        return WebReview::with('client', 'client.photo', 'examScores')
            ->where('is_published', true)
            ->whereExists(fn ($q) => $q->selectRaw(1)
                ->from('web_review_programs as wrp')
                ->whereColumn('wrp.web_review_id', 'web_reviews.id')
                ->whereIn('wrp.program', Program::getAllExternal())
            )
            ->inRandomOrder($seed)
            ->select('web_reviews.*');
    }

    private function getCoursesQuery($subject, $grade, $seed)
    {
        $program = Program::tryFrom($subject.$grade);

        // экзамен: 1 или 0
        $cte = DB::table('web_reviews as wr')
            ->selectRaw('
                wr.id, (
                    select count(*) from exam_score_web_review es_wr
                    where es_wr.web_review_id = wr.id
                ) as cnt
            ')
            ->groupBy('wr.id')
            ->having('cnt', '<=', 1);

        $query = WebReview::with('client', 'client.photo', 'examScores')
            ->joinSub($cte, 'cte', 'cte.id', '=', 'web_reviews.id')
            ->join('web_review_programs as wrp', fn ($join) => $join
                ->on('wrp.web_review_id', '=', 'web_reviews.id')
                ->where('wrp.program', '=', $program->value)
            )
            ->leftJoin('exam_score_web_review as es_wr', 'es_wr.web_review_id', '=', 'web_reviews.id')
            ->leftJoin('exam_scores as es', 'es.id', '=', 'es_wr.exam_score_id')
            ->where('is_published', true)
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
}
