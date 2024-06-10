<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Resources\{LessonListResource};
use App\Models\Lesson;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    protected $filters = [
        'equals' => ['group_id']
    ];

    public function index(Request $request)
    {
        $query = Lesson::query()
            ->with('teacher')
            ->orderBy('start_at');
        $this->filter($request, $query);
        return $this->handleIndexRequest($request, $query, LessonListResource::class);
    }
}
