<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Resources\{LessonConductResource, LessonListResource, LessonResource};
use App\Models\ClientLesson;
use App\Models\Lesson;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    protected $filters = [
        'equals' => ['group_id', 'teacher_id'],
        'group' => ['year'],
    ];

    public function index(Request $request)
    {
        $query = Lesson::with(['teacher', 'group', 'clientLessons']);
        $this->filter($request, $query);
        $lessons = $query->get();
        return Lesson::withSequenceNumber($lessons);
    }

    public function show(Lesson $lesson, Request $request)
    {
        if ($request->has('conduct')) {
            return new LessonConductResource($lesson);
        }
        return new LessonResource($lesson);
    }


    /**
     * Сохранение ранее проведённого занятия
     */
    public function update(Lesson $lesson, Request $request)
    {
        foreach ($request->contracts as $c) {
            $clientLesson = ClientLesson::find($c['id']);
            $clientLesson->update($c);
        }
        return new LessonListResource($lesson);
    }

    /**
     * Проводка занятия
     */
    public function conduct(Lesson $lesson, Request $request)
    {
        $lesson->conduct($request->students);
        return new LessonListResource($lesson);
    }

    protected function filterGroup($query, $value, $field)
    {
        $query->whereHas('group', fn($q) => $q->where($field, $value));
    }
}
