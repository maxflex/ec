<?php

namespace App\Observers;

use App\Enums\ClientLessonStatus;
use App\Enums\TelegramTemplate;
use App\Models\ClientLesson;
use App\Models\TelegramMessage;

class ClientLessonObserver
{
    public function created(ClientLesson $clientLesson): void
    {
        // если ученик опоздал, не был или был удаленно
        if (in_array($clientLesson->status, [
            ClientLessonStatus::late,
            ClientLessonStatus::absent,
            ClientLessonStatus::lateOnline,
            ClientLessonStatus::presentOnline,
        ])) {
            TelegramMessage::sendTemplate(
                TelegramTemplate::clientLessonStatus,
                $clientLesson->contractVersionProgram->contractVersion->contract->client->parent,
                ['clientLesson' => $clientLesson]
            );
        }

        $this->updateComputed($clientLesson);
    }

    private function updateComputed(ClientLesson $clientLesson): void
    {
        $clientLesson->contractVersionProgram->updateStatus();
    }

    public function deleted(ClientLesson $clientLesson): void
    {
        $this->updateComputed($clientLesson);
    }
}
