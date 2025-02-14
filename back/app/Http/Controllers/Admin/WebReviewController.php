<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\WebReviewResource;
use App\Models\WebReview;
use Illuminate\Http\Request;

class WebReviewController extends Controller
{
    protected $filters = [
        'equals' => ['client_id', 'is_published'],
        'program' => ['program'],
        'examScores' => ['exam_scores'],
    ];

    public function index(Request $request)
    {
        $query = WebReview::query()
            ->with(['client', 'examScores'])
            ->latest();

        $this->filter($request, $query);

        return $this->handleIndexRequest($request, $query, WebReviewResource::class);
    }

    public function store(Request $request)
    {
        $webReview = auth()->user()->webReviews()->create($request->all());
        $webReview->examScores()->sync($request->exam_scores);

        return new WebReviewResource($webReview);
    }

    public function update(WebReview $webReview, Request $request)
    {
        $webReview->update($request->all());
        $webReview->examScores()->sync($request->exam_scores);

        return new WebReviewResource($webReview);
    }

    public function show(WebReview $webReview)
    {
        return new WebReviewResource($webReview);
    }

    public function destroy(WebReview $webReview)
    {
        $webReview->examScores->each->delete();
        $webReview->delete();
    }

    protected function filterProgram(&$query, $programs)
    {
        $query->where(function ($q) use ($programs) {
            foreach ($programs as $program) {
                $q->orWhereRaw('exists(
                    select 1 from web_review_program
                    where web_review_id = web_reviews.id
                    and program = ?
                )', [$program]);
            }
        });
    }

    protected function filterExamScores(&$query, $status)
    {
        switch ($status) {
            case 'notExists':
                // нет доступных оценок к выбору
                $query->whereRaw('NOT EXISTS (
                    select 1 from exam_scores es
                    where web_reviews.client_id = es.client_id and not exists (
                        select 1 from exam_score_web_review es_wr
                        where es_wr.exam_score_id = es.id
                        and es_wr.web_review_id <> web_reviews.id
                    )
                )');
                break;

            case 'existsNotSelected':
                // есть доступные к выбору, но ничего не выбрано
                $query->whereDoesntHave('examScores')->whereRaw('EXISTS (
                    select 1 from exam_scores es
                    where web_reviews.client_id = es.client_id
                    and not exists (
                        select 1 from exam_score_web_review es_wr
                        where es_wr.exam_score_id = es.id
                    )
                )');
                break;

            case 'existsSelected':
                // есть выбранные
                $query->whereHas('examScores');
                break;
        }
    }
}
