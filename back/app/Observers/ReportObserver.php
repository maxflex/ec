<?php

namespace App\Observers;

use App\Enums\TelegramTemplate;
use App\Models\Report;
use App\Models\TelegramMessage;

class ReportObserver
{
    public function updated(Report $report): void
    {
        if ($report->isDirty('is_published') && $report->is_published) {
            TelegramMessage::sendTemplate(
                TelegramTemplate::reportPublished,
                $report->client->parent->phones()->withTelegram()->get()->all(),
                ['report' => $report],
                ['id' => $report->id]
            );
        }
    }
}