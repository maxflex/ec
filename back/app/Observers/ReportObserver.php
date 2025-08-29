<?php

namespace App\Observers;

use App\Enums\ReportDelivery;
use App\Enums\ReportStatus;
use App\Enums\TelegramTemplate;
use App\Models\Report;
use App\Models\TelegramMessage;

class ReportObserver
{
    public function saving(Report $report): void
    {
        if ($report->isDirty('status')) {
            if ($report->status == ReportStatus::toCheck) {
                $report->to_check_at = now();
            }
        }
    }

    public function updating(Report $report)
    {
        if ($report->isDirty('status') && $report->status === ReportStatus::published) {
            $delivered = TelegramMessage::sendTemplate(
                TelegramTemplate::reportPublished,
                $report->client->representative,
                ['report' => $report],
                ['id' => $report->id]
            );
            if ($delivered && $report->delivery === null) {
                $report->delivery = ReportDelivery::delivered;
            }
        }
    }
}
