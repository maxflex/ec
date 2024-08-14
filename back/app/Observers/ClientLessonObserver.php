<?php

namespace App\Observers;

use App\Enums\ClientLessonStatus;
use App\Enums\TelegramTemplate;
use App\Models\ClientLesson;
use App\Models\TelegramMessage;

class ClientLessonObserver
{
    /**
     * Handle the ClientLesson "created" event.
     */
    public function created(ClientLesson $clientLesson): void
    {
        // если ученик опоздал, не был и/или был удаленно
        if (
            $clientLesson->status <> ClientLessonStatus::present
            || $clientLesson->is_remote
        ) {
            TelegramMessage::sendTemplate(
                TelegramTemplate::clientLessonStatus,
                $clientLesson->contract->client->parent->phones()->withTelegram()->get()->all(),
                ['clientLesson' => $clientLesson]
            );
        }
    }

    /**
     * Handle the ClientLesson "updated" event.
     */
    public function updated(ClientLesson $clientLesson): void
    {
        //
    }

    /**
     * Handle the ClientLesson "deleted" event.
     */
    public function deleted(ClientLesson $clientLesson): void
    {
        //
    }

    /**
     * Handle the ClientLesson "restored" event.
     */
    public function restored(ClientLesson $clientLesson): void
    {
        //
    }

    /**
     * Handle the ClientLesson "force deleted" event.
     */
    public function forceDeleted(ClientLesson $clientLesson): void
    {
        //
    }
}
