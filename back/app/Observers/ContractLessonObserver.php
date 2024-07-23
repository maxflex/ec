<?php

namespace App\Observers;

use App\Enums\ContractLessonStatus;
use App\Enums\TelegramTemplate;
use App\Models\ContractLesson;
use App\Models\TelegramMessage;

class ContractLessonObserver
{
    /**
     * Handle the ContractLesson "created" event.
     */
    public function created(ContractLesson $contractLesson): void
    {
        // если ученик опоздал, не был и/или был удаленно
        if (
            $contractLesson->status <> ContractLessonStatus::present
            || $contractLesson->is_remote
        ) {
            TelegramMessage::sendTemplate(
                TelegramTemplate::clientLessonStatus,
                $contractLesson->contract->client->parent->phones()->withTelegram()->get()->all(),
                ['contractLesson' => $contractLesson]
            );
        }
    }

    /**
     * Handle the ContractLesson "updated" event.
     */
    public function updated(ContractLesson $contractLesson): void
    {
        //
    }

    /**
     * Handle the ContractLesson "deleted" event.
     */
    public function deleted(ContractLesson $contractLesson): void
    {
        //
    }

    /**
     * Handle the ContractLesson "restored" event.
     */
    public function restored(ContractLesson $contractLesson): void
    {
        //
    }

    /**
     * Handle the ContractLesson "force deleted" event.
     */
    public function forceDeleted(ContractLesson $contractLesson): void
    {
        //
    }
}
