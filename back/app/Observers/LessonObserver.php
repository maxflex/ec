<?php

namespace App\Observers;

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
        $lesson->group->updateSchedule();
        $lesson->teacher->updateSchedule($lesson->group->year);

        foreach ($lesson->group->clientGroups as $clientGroup) {
            $clientGroup->contractVersionProgram->contractVersion->contract->client->updateSchedule(
                $lesson->group->year
            );
        }
    }

    public function deleted(Lesson $lesson): void
    {
        $this->updateComputed($lesson);
    }
}
