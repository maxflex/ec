<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Resources\ScholarshipScoreResource;
use App\Models\ScholarshipScore;
use Illuminate\Http\Request;

class ScholarshipScoreController extends Controller
{
    public function index(Request $request)
    {
        $query = ScholarshipScore::getQuery()
            ->where('l.teacher_id', auth()->id())
            ->having('month', $request->month);

        return paginate(
            ScholarshipScoreResource::collection($query->get())
        );

    }

    public function store()
    {

    }
}
