<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Resources\LessonListResource;
use App\Models\ClientLesson;
use App\Models\Lesson;
use Illuminate\Http\Request;

class LessonController extends \App\Http\Controllers\Admin\LessonController
{
    public function update(Request $request, Lesson $lesson)
    {
        // Сохранение ранее проведённого занятия
        if ($request->has('students')) {
            foreach ($request->students as $s) {
                $clientLesson = ClientLesson::find($s['id']);
                $clientLesson->update($s);
            }
        } else {
            $lesson->update($request->all());
        }

        return new LessonListResource($lesson);
    }

    /**
     * Проводка занятия
     */
    public function conduct(Lesson $lesson, Request $request)
    {
        // проводка занятия разрешена минимум 20 мин после начала
        abort_unless(
            $lesson->date_time->diffInMinutes(now()) >= 20,
            422,
            'Провести занятие можно минимум через <b>20 минут</b> после его начала'
        );

        $lesson->conduct($request->students);

        return new LessonListResource($lesson);
    }
}
