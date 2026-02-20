<?php

namespace App\Utils\AI;

use App\Models\AiPrompt;
use App\Models\Report;
use Gemini\Data\GenerationConfig;
use Gemini\Data\ThinkingConfig;

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

        [$systemInstructionText, $userPromptText] = (new AiPromptRenderer)
            ->renderInstructionAndPromptById(AiPrompt::REPORT, $data);

        return self::generate($systemInstructionText, $userPromptText);
    }

    /**
     * Предыдущий отчет в плоскости, если есть
     */
    private static function getHistoryReport(Report $report): ?Report
    {
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
        $response = self::buildModel($systemInstructionText)
            ->withGenerationConfig(new GenerationConfig(
                thinkingConfig: new ThinkingConfig(
                    includeThoughts: false,
                )
            ))
            ->generateContent($userPromptText);

        // Не используем $response->text(): он работает только для single-part ответа.
        // Gemini периодически отдает multi-part (с "думающими" кусками), и text() бросает ValueError.
        return collect($response->parts())->last()->text;
    }
}
