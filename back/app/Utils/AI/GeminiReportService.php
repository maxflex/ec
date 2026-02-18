<?php

namespace App\Utils\AI;

use App\Models\AiPrompt;
use App\Models\Report;

class GeminiReportService extends GeminiService
{
    /**
     * @return string Улучшенный текст отчета
     */
    public static function improveReport(Report $report): string
    {
        $data = [
            'report' => $report,
            'historyReport' => self::getHistoryReport($report),
        ];

        [$systemInstructionText, $userPromptText] = (new AiPromptRenderer())
            ->renderInstructionAndPromptById(AiPrompt::REPORT, $data);

        return self::generate($systemInstructionText, $userPromptText);
    }

    private static function getHistoryReport(Report $report): ?Report
    {
        // История отчетов в плоскости (по этому ученику, по этой программе, в этом году, у этого препода)
        return Report::where([
            ['id', '<', $report->id],
            ['client_id', $report->client_id],
            ['teacher_id', $report->teacher_id],
            ['program', $report->program],
            ['year', $report->year],
        ])->latest()->first();
    }

    private static function generate(string $systemInstructionText, string $userPromptText): string
    {
        return self::buildModel($systemInstructionText)
            ->generateContent($userPromptText)
            ->text();
    }
}
