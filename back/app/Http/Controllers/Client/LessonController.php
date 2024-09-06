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

    public function __invoke(Request $request)
    {
        if ($request->has('client_id')) {
            $lessons = auth()->user()->entity->getSchedule($request->year);
        } else {
            $query = Lesson::with(['teacher', 'group', 'clientLessons']);
            $this->filter($request, $query);
            $lessons = $query->get();
        }

        return Lesson::withSequenceNumber($lessons);
    }


    protected function filterGroup($query, $value, $field)
    {
        $query->whereHas('group', fn($q) => $q->where($field, $value));
    }
}
