<?php

namespace App\Observers;

use App\Enums\LessonStatus;
use App\Jobs\UpdateScheduleJob;
use App\Models\Lesson;
use App\Models\Teacher;
use Illuminate\Validation\ValidationException;

class LessonObserver
{
    public function updating(Lesson $lesson): void
    {
        // Нельзя менять статус уже проведённого урока
        if (
            $lesson->getOriginal('status') === LessonStatus::conducted
            && $lesson->isDirty('status')
        ) {
            throw ValidationException::withMessages([
                'status' => 'Нельзя менять статус уже проведённого урока.',
            ]);
        }
    }

    public function saved(Lesson $lesson): void
    {
        $this->updateComputed($lesson);
    }

    /**
     * Должно обновляться расписание: в группе, у препода, у клиентов
     */
    private function updateComputed(Lesson $lesson): void
    {
        $year = $lesson->group->year;

        UpdateScheduleJob::dispatch($lesson->group, $year);
        UpdateScheduleJob::dispatch($lesson->teacher, $year);

        // Если препод поменялся, обновляем у старого тоже
        if ($lesson->wasChanged('teacher_id')) {
            $oldTeacher = Teacher::find($lesson->getOriginal('teacher_id'));
            UpdateScheduleJob::dispatch($oldTeacher, $year);
        }

        foreach ($lesson->group->clientGroups as $clientGroup) {
            $client = $clientGroup->contractVersionProgram->contractVersion->contract->client;
            UpdateScheduleJob::dispatch($client, $year);
        }
    }

    public function deleted(Lesson $lesson): void
    {
        $this->updateComputed($lesson);
    }
}
