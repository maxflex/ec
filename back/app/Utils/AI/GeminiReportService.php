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
     * @return array{ai_comment: string, ai_model: string, ai_instruction: string}
     */
    public static function improveReport(Report $report): array
    {
        $data = [
            'report' => $report,
            'historyReport' => self::getHistoryReport($report),
        ];

        [$systemInstructionText, $userPromptText] = (new AiPromptRenderer)
            ->renderInstructionAndPromptById(AiPrompt::REPORT, $data);

        // Для повторной генерации используем уже зафиксированную модель, иначе — дефолт по прежней схеме.
        $model = $report->ai_model ?? ($report->id % 2 === 0 ? 'gemini-3-pro-preview' : 'gemini-3-flash-preview');

        return [
            'ai_comment' => self::generate($systemInstructionText, $userPromptText, $model),
            'ai_model' => $model,
            // Сохраняем фактические тексты после Blade-рендера, чтобы можно было восстановить контекст генерации.
            'ai_instruction' => self::buildAiInstructionSnapshot($systemInstructionText, $userPromptText),
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

    private static function buildAiInstructionSnapshot(string $systemInstructionText, string $userPromptText): string
    {
        return implode("\n\n", [
            '[SYSTEM INSTRUCTION]',
            trim($systemInstructionText),
            '[USER PROMPT]',
            trim($userPromptText),
        ]);
    }
}
