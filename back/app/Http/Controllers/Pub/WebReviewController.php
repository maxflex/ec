<?php

namespace App\Http\Controllers\Pub;

use App\Enums\Exam;
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
        $query = WebReview::with('client', 'client.photo');

        $subject = $request->input('subject');
        $grade = intval($request->input('grade'));
        $seed = $request->input('seed');

        // отзывы существуют
        $exists = false;
        $programs = [];

        if ($grade === 14) {
            $programs = Program::getAllExternal();
            $exists = true;
        } elseif ($grade !== 20) {
            $program = Program::tryFrom($subject.$grade);
            if ($program) {
                $exists = DB::table('web_review_programs')->where('program', $program->value)->exists();
                $programs = [$program];
            }
        }

        if (is_localhost()) {
            logger('grade: '.$grade);
            logger('subject: '.is_null($subject) ? 'null' : $subject);
            logger('Programs', $programs);
        }

        if (count($programs) > 0 && $exists) {
            $exams = collect($programs)
                ->map(fn (Program $p) => $p->getExam())
                ->filter(fn ($e) => $e !== null)
                ->map(fn (Exam $exam) => $exam->value)
                ->all();

            // logger('Exams', $exams);

            $query->with('examScores', fn ($q) => $q->whereIn('exam', $exams))
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
                        ->whereIn('es.exam', $exams)
                )
                ->where('es.score', '>=', 40)

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

    public function show(WebReview $webReview)
    {
        return new WebReviewPubResource($webReview);
    }
}
