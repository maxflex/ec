<?php

namespace App\Observers;

use App\Jobs\UpdateScheduleJob;
use App\Models\Lesson;

class LessonObserver
{
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
