<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExamScoreResource;
use App\Models\ExamScore;
use Illuminate\Http\Request;

class ExamScoreController extends Controller
{
    protected $filters = [
        'equals' => ['year', 'client_id'],
    ];

    public function index(Request $request)
    {
        $query = ExamScore::query()
            ->with(['user', 'client'])
            ->latest();

        $this->filter($request, $query);

        return $this->handleIndexRequest(
            $request,
            $query,
            ExamScoreResource::class
        );
    }

    public function store(Request $request)
    {
        $examScore = ExamScore::create($request->all());

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
