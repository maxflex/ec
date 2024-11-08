<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExamScoreResource;
use App\Models\ExamScore;
use Illuminate\Http\Request;

class ExamScoreController extends Controller
{
    protected $filters = [
        'equals' => ['year', 'client_id']
    ];

    public function index(Request $request)
    {
        $query = ExamScore::query()
            ->with(['user', 'client'])
            ->latest();
        if ($request->has('web_review_id')) {
            $query->with('webReviews',
                fn($q) => $q->where('id', '<>', $request->web_review_id)
            );
        } else {
            $query->with('webReviews');
        }
        $this->filter($request, $query);
        return $this->handleIndexRequest(
            $request,
            $query,
            ExamScoreResource::class
        );
    }

    public function store(Request $request)
    {
        $examScore = auth()->user()->entity->examScores()->create($request->all());
        return new ExamScoreResource($examScore);
    }

    public function show(ExamScore $examScore)
    {
        return new ExamScoreResource($examScore);
    }

    public function update(Request $request, ExamScore $examScore)
    {
        $examScore->update($request->all());
        return new ExamScoreResource($examScore);
    }

    public function destroy(ExamScore $examScore)
    {
        $examScore->delete();
    }
}
