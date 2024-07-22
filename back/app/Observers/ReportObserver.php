<?php

namespace App\Observers;

use App\Enums\TelegramTemplate as EnumsTelegramTemplate;
use App\Models\ClientParent;
use App\Models\Report;
use App\Models\TelegramMessage;
use App\Models\TelegramTemplate;

class ReportObserver
{
    /**
     * Handle the Report "created" event.
     */
    public function created(Report $report): void
    {
        //
    }

    /**
     * Handle the Report "updated" event.
     */
    public function updated(Report $report): void
    {
        if ($report->isDirty('is_published') && $report->is_published) {
            TelegramMessage::sendTemplate(
                EnumsTelegramTemplate::reportPublished,
                $report->client->parent->phones()->withTelegram()->get()->all(),
                ['id' => $report->id]
            );
        }
    }

    /**
     * Handle the Report "deleted" event.
     */
    public function deleted(Report $report): void
    {
        //
    }

    /**
     * Handle the Report "restored" event.
     */
    public function restored(Report $report): void
    {
        //
    }

    /**
     * Handle the Report "force deleted" event.
     */
    public function forceDeleted(Report $report): void
    {
        //
    }
}
