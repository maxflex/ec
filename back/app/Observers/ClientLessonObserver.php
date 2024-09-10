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
        // если ученик опоздал, не был и/или был удаленно
        if (
            $clientLesson->status <> ClientLessonStatus::present
            || $clientLesson->is_remote
        ) {
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
