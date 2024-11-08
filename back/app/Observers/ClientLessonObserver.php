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
            $phones = $clientLesson->contractVersionProgram->contractVersion->contract->client->parent
                ->phones()
                ->withTelegram()
                ->get()
                ->all();
            TelegramMessage::sendTemplate(
                TelegramTemplate::clientLessonStatus,
                $phones,
                ['clientLesson' => $clientLesson]
            );
        }
    }
}
