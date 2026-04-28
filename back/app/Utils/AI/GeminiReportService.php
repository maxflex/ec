<?php

namespace App\Utils\AI;

use App\Models\AiPrompt;
use App\Models\Report;
use Gemini\Data\GenerationConfig;
use Gemini\Data\ThinkingConfig;
use ValueError;

class GeminiReportService extends GeminiService
{
    private const string MODEL = 'gemini-3-flash-preview';

    /**
     * @return array{
     *     ai_comment: string,
     *     instruction: array{text: string, model: string, created_at: string}
     * }
     */
    public static function improveReport(Report $report): array
    {
        $data = [
            'report' => $report,
            'historyReport' => self::getHistoryReport($report),
        ];

        [$systemInstructionText, $userPromptText] = (new AiPromptRenderer)
            ->renderInstructionAndPromptById(AiPrompt::REPORT, $data);

        return [
            'ai_comment' => self::generate($systemInstructionText, $userPromptText, self::MODEL),
            // Сохраняем фактические тексты после Blade-рендера, чтобы можно было восстановить контекст генерации.
            'instruction' => [
                'text' => self::buildInstructionSnapshot($systemInstructionText, $userPromptText),
                'model' => self::MODEL,
                'created_at' => now()->format('Y-m-d H:i:s'),
            ],
        ];
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

    private static function generate(
        string $systemInstructionText,
        string $userPromptText,
        string $model
    ): string {
        $response = self::buildModel($systemInstructionText, $model)
            ->withGenerationConfig(new GenerationConfig(
                thinkingConfig: new ThinkingConfig(
                    includeThoughts: false,
                )
            ))
            ->generateContent([$userPromptText]);

        try {
            return $response->text();
        } catch (ValueError) {
            // Gemini периодически отдает multi-part (с "думающими" кусками), и text() бросает ValueError.
            return collect($response->parts())->last()->text;
        }
    }
}
