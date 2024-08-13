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

    /**
     * Display a listing of the resource.
     */
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $examScore = auth()->user()->entity->examScores()->create($request->all());
        return new ExamScoreResource($examScore);
    }

    /**
     * Display the specified resource.
     */
    public function show(ExamScore $examScore)
    {
        return new ExamScoreResource($examScore);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ExamScore $examScore)
    {
        $examScore->update($request->all());
        return new ExamScoreResource($examScore);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExamScore $examScore)
    {
        $examScore->delete();
    }
}
