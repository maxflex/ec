<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\{LessonListResource};
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
            return [
                'data' => LessonListResource::collection(auth()->user()->entity->getSchedule($request->year))
            ];
        }

        $query = Lesson::query()
            ->with(['teacher', 'group', 'clientLessons'])
            ->orderByRaw('date, time');
        $this->filter($request, $query);
        return $this->handleIndexRequest($request, $query, LessonListResource::class);
    }


    protected function filterGroup($query, $value, $field)
    {
        $query->whereHas('group', fn($q) => $q->where($field, $value));
    }
}
