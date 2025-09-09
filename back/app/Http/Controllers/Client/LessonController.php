<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    protected $filters = [
        'equals' => ['group_id'],
        'group' => ['year'],
    ];

    public function index(Request $request)
    {
        $lessons = auth()->user()->getLessons($request->year);

        return Lesson::withSequenceNumber($lessons);
    }

    protected function filterGroup($query, $value, $field)
    {
        $query->whereHas('group', fn ($q) => $q->where($field, $value));
    }
}
