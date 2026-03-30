<?php

namespace App\Utils\AI;

use App\Enums\CallerType;
use App\Models\AiPrompt;
use App\Models\Call;
use Gemini\Data\GenerationConfig;
use Gemini\Data\Schema;
use Gemini\Enums\DataType;
use Gemini\Enums\ResponseMimeType;
use RuntimeException;
use ValueError;

class CallAnalysisService extends GeminiService
{
    /**
     * Минимальная длительность разговора для запуска AI-анализа.
     */
    public const int MIN_DURATION_SECONDS = 10;

    /**
     * Можно ли запускать AI-анализ для звонка.
     */
    public static function shouldAnalyze(Call $call): bool
    {
        return $call->duration > self::MIN_DURATION_SECONDS;
    }

    /**
     * Шаг 2: transcript -> summary + analysis + caller_type.
     *
     * @return array{
     *     summary: string,
     *     analysis: string,
     *     caller_type: string,
     *     instruction: array{
     *         transcription: string|null,
     *         analysis: string|null
     *     }
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

        // На втором шаге запрашиваем сводку, анализ и тип собеседника.
        $schema = new Schema(
            type: DataType::OBJECT,
            properties: [
                'summary' => new Schema(
                    type: DataType::STRING,
                    description: 'КРАТКОЕ СОДЕРЖАНИЕ РАЗГОВОРА'
                ),
                'analysis' => new Schema(
                    type: DataType::STRING,
                    description: 'АНАЛИЗ ЗВОНКА'
                ),
                'caller_type' => new Schema(
                    type: DataType::STRING,
                    description: 'ТИП РАЗГОВОРА'
                ),
            ],
            required: ['summary', 'analysis', 'caller_type']
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
            ]);

        $result = $response->json(true);

        foreach (['summary', 'analysis', 'caller_type'] as $field) {
            if (! isset($result[$field]) || ! is_string($result[$field]) || trim($result[$field]) === '') {
                throw new RuntimeException("Gemini вернул пустое поле '{$field}' для звонка {$call->id}");
            }
        }

        try {
            $callerType = CallerType::from(trim($result['caller_type']));
        } catch (ValueError) {
            throw new RuntimeException(
                "Gemini вернул недопустимый caller_type '{$result['caller_type']}' для звонка {$call->id}"
            );
        }

        return [
            'summary' => trim($result['summary']),
            'analysis' => trim($result['analysis']),
            'caller_type' => $callerType->value,
            // Фиксируем фактические instruction/prompt после Blade-рендера (как в отчетах).
            'instruction' => self::buildMergedInstruction(
                $call,
                'analysis',
                self::buildInstructionSnapshot($systemInstructionText, $userPromptText)
            ),
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

    /**
     * @param  'transcription'|'analysis'  $key
     * @return array{transcription: string|null, analysis: string|null}
     */
    private static function buildMergedInstruction(Call $call, string $key, string $snapshot): array
    {
        $currentInstruction = is_array($call->instruction) ? $call->instruction : [];

        // Ключи фиксированные, чтобы формат JSON был предсказуемым.
        $normalizedInstruction = [
            'transcription' => isset($currentInstruction['transcription']) && is_string($currentInstruction['transcription'])
                ? $currentInstruction['transcription']
                : null,
            'analysis' => isset($currentInstruction['analysis']) && is_string($currentInstruction['analysis'])
                ? $currentInstruction['analysis']
                : null,
        ];

        $normalizedInstruction[$key] = $snapshot;

        return $normalizedInstruction;
    }

    private static function buildInstructionSnapshot(string $systemInstructionText, string $userPromptText): string
    {
        return trim($systemInstructionText)."\n\n<USER_PROMPT>\n\n".trim($userPromptText);
    }
}
