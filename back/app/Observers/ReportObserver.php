<?php

namespace App\Observers;

use App\Enums\ReportStatus;
use App\Enums\TelegramTemplate;
use App\Models\Report;
use App\Models\TelegramMessage;

class ReportObserver
{
    public function updated(Report $report): void
    {
        if ($report->isDirty('status') && $report->status === ReportStatus::published) {
            TelegramMessage::sendTemplate(
                TelegramTemplate::reportPublished,
                $report->client->parent->phones()->withTelegram()->get()->all(),
                ['report' => $report],
                ['id' => $report->id]
            );
        }
    }
}