<?php

namespace App\Utils\AI;

use App\Models\AiPrompt;
use App\Models\Report;
use Gemini\Data\GenerationConfig;
use Gemini\Data\ThinkingConfig;
use ValueError;

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

        // пока хардкодом
        $model = $report->id % 2 === 0 ? 'gemini-3.1-pro-preview' : 'gemini-3-flash-preview';

        return self::generate($systemInstructionText, $userPromptText, $model);
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

    private static function generate(string $systemInstructionText, string $userPromptText, string $model): string
    {
        $response = self::buildModel($systemInstructionText, $model)
            ->withGenerationConfig(new GenerationConfig(
                thinkingConfig: new ThinkingConfig(
                    includeThoughts: false,
                )
            ))
            ->generateContent($userPromptText);

        try {
            return $response->text();
        } catch (ValueError) {
            // Gemini периодически отдает multi-part (с "думающими" кусками), и text() бросает ValueError.
            return collect($response->parts())->last()->text;
        }
    }
}
