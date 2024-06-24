<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Resources\{LessonConductResource, LessonListResource};
use App\Models\ContractLesson;
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

    public function show(Lesson $lesson)
    {
        return new LessonConductResource($lesson);
    }

    /**
     * Сохранение ранее проведённого занятия
     */
    public function update(Lesson $lesson, Request $request)
    {
        foreach ($request->contracts as $c) {
            $contractLesson = ContractLesson::find($c['id']);
            $contractLesson->update($c);
        }
    }

    /**
     * Проводка занятия
     */
    public function conduct(Lesson $lesson, Request $request)
    {
        $lesson->conduct($request->contracts);
    }
}
