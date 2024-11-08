<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Resources\{LessonListResource};
use App\Models\ClientLesson;
use App\Models\Lesson;
use Illuminate\Http\Request;

class LessonController extends \App\Http\Controllers\Admin\LessonController
{
    /**
     * Сохранение ранее проведённого занятия
     */
    public function update(Request $request, Lesson $lesson)
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
}
