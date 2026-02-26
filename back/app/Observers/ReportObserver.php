<?php

namespace App\Observers;

use App\Enums\ReportStatus;
use App\Enums\TelegramTemplate;
use App\Jobs\GenerateReportAiCommentJob;
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
            TelegramMessage::sendTemplate(
                TelegramTemplate::reportPublished,
                $report->client->representative,
                ['report' => $report],
                ['id' => $report->id]
            );
        }
    }

    public function created(Report $report): void
    {
        // Поддержка кейса, когда отчет создается сразу в статусе "на проверку".
        if ($report->status === ReportStatus::toCheck && $report->comment) {
            GenerateReportAiCommentJob::dispatch($report->id);
        }
    }

    public function updated(Report $report): void
    {
        // Автогенерацию запускаем только при фактическом переходе в статус "на проверку".
        if ($report->wasChanged('status') && $report->status === ReportStatus::toCheck && $report->comment) {
            GenerateReportAiCommentJob::dispatch($report->id);
        }

        // Фолбэк для старых сценариев: если ai_comment есть, а модель не зафиксирована — ставим дефолт.
        if ($report->wasChanged('ai_comment') && filled($report->ai_comment) && ! $report->ai_model) {
            $report->ai_model = $report->id % 2 === 0 ? 'gemini-3-pro-preview' : 'gemini-3-flash-preview';
            $report->saveQuietly();
        }
    }
}
