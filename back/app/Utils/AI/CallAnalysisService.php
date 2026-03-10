<?php

namespace App\Utils\AI;

use App\Models\AiPrompt;
use App\Models\Call;
use Gemini\Data\GenerationConfig;
use Gemini\Data\Schema;
use Gemini\Enums\DataType;
use Gemini\Enums\ResponseMimeType;
use RuntimeException;

class CallAnalysisService extends GeminiService
{
    /**
     * Шаг 2: transcript -> summary + analysis_1..3.
     *
     * @return array{
     *     summary: string,
     *     analysis_1: string,
     *     analysis_2: string,
     *     analysis_3: string
     * }
     */
    public static function analyzeTranscript(Call $call): array
    {
        // Анализ опирается строго на транскрипт, сохраненный в модели звонка.
        $transcript = trim((string) $call->transcript);
        if ($transcript === '') {
            throw new RuntimeException("Нельзя анализировать пустой транскрипт для звонка {$call->id}");
        }

        [$systemInstructionText, $userPromptText] = self::renderCallAnalysisPrompt($call);
        $promptGeminiFiles = GeminiFileService::getPromptGeminiFiles(AiPrompt::CALL_ANALYSIS);

        // На втором шаге запрашиваем только аналитические блоки.
        $schema = new Schema(
            type: DataType::OBJECT,
            properties: [
                'summary' => new Schema(
                    type: DataType::STRING,
                    description: 'КРАТКОЕ СОДЕРЖАНИЕ РАЗГОВОРА'
                ),
                'analysis_1' => new Schema(
                    type: DataType::STRING,
                    description: 'АНАЛИЗ ЗВОНКА №1'
                ),
                'analysis_2' => new Schema(
                    type: DataType::STRING,
                    description: 'АНАЛИЗ ЗВОНКА №2'
                ),
                'analysis_3' => new Schema(
                    type: DataType::STRING,
                    description: 'АНАЛИЗ ЗВОНКА №3'
                ),
            ],
            required: ['summary', 'analysis_1', 'analysis_2', 'analysis_3']
        );

        $response = self::buildModel($systemInstructionText)
            ->withGenerationConfig(
                new GenerationConfig(
                    responseMimeType: ResponseMimeType::APPLICATION_JSON,
                    responseSchema: $schema
                )
            )
            ->generateContent([
                $userPromptText,
                ...$promptGeminiFiles,
            ]);

        $result = $response->json(true);

        foreach (['summary', 'analysis_1', 'analysis_2', 'analysis_3'] as $field) {
            if (! isset($result[$field]) || ! is_string($result[$field]) || trim($result[$field]) === '') {
                throw new RuntimeException("Gemini вернул пустое поле '{$field}' для звонка {$call->id}");
            }
        }

        return [
            'summary' => trim($result['summary']),
            'analysis_1' => trim($result['analysis_1']),
            'analysis_2' => trim($result['analysis_2']),
            'analysis_3' => trim($result['analysis_3']),
        ];
    }

    /**
     * Рендер prompt-пары для шага 2 (анализ по готовому транскрипту).
     * Важно: prompt должен использовать {{ $call->transcript }}.
     *
     * @return array{0: string, 1: string}
     */
    private static function renderCallAnalysisPrompt(Call $call): array
    {
        return app(AiPromptRenderer::class)->renderInstructionAndPromptById(
            AiPrompt::CALL_ANALYSIS, [
                'call' => $call,
                'aon' => Call::aon($call->number),
            ]);
    }
}
