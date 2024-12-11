<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Resources\ScholarshipScoreResource;
use App\Models\ScholarshipScore;
use Illuminate\Http\Request;

class ScholarshipScoreController extends Controller
{
    protected $filters = [
        'equals' => ['month']
    ];

    public function index(Request $request)
    {
        $query = ScholarshipScore::getQuery()
            ->where('teacher_id', auth()->id());

        $this->filter($request, $query);

        return paginate(
            ScholarshipScoreResource::collection($query->get())
        );
    }

    public function store(Request $request)
    {
        auth()->user()->scholarshipScores()->create(
            $request->all()
        );
    }

    public function update(ScholarshipScore $scholarshipScore, Request $request)
    {
        $scholarshipScore->update($request->all());
    }
}
