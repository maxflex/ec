<?php

namespace App\Jobs;

use App\Models\Report;
use App\Utils\AI\GeminiReportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateReportAiCommentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Максимальное время выполнения одной попытки (5 минут).
     * Генерация отчета может занимать заметное время из-за внешнего AI API.
     */
    public int $timeout = 300;

    public int $tries = 2;

    /**
     * Небольшой backoff на случай временных ошибок сети/API.
     *
     * @var array<int>
     */
    public array $backoff = [60];

    public function __construct(
        private readonly int $reportId,
    ) {}

    public function handle(): void
    {
        $report = Report::findOrFail($this->reportId);

        $aiComment = GeminiReportService::improveReport($report);

        $report->update([
            'ai_comment' => $aiComment,
        ]);
    }
}
