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
    private const string MODEL = 'gemini-3-flash-preview';

    /**
     * Шаг 2: transcript -> summary + analysis_1 + analysis_2 + caller_type.
     *
     * @return array{
     *     summary: string,
     *     analysis_1: string|null, // пустой анализ сохраняется как null
     *     analysis_2: string|null, // пустой анализ сохраняется как null
     *     caller_type: string,
     *     instruction: array{
     *         transcript: array{text: string, model: string, created_at: string}|null,
     *         analysis: array{text: string, model: string, created_at: string}|null
     *     }
     * }
     */
    public static function analyzeTranscript(Call $call): array
    {
        if (! $call->has_recording) {
            throw new RuntimeException("Нельзя анализировать звонок {$call->id}: нет recording_id");
        }

        // Анализ опирается строго на транскрипт, сохраненный в модели звонка.
        $transcript = trim((string) $call->transcript);
        if ($transcript === '') {
            throw new RuntimeException("Нельзя анализировать пустой транскрипт для звонка {$call->id}");
        }

        [$systemInstructionText, $userPromptText] = self::renderCallAnalysisPrompt($call);
        $audioFile = CallAudioFileCacheService::getOrCreateUploadedFile($call);

        // На втором шаге запрашиваем сводку, анализ и тип собеседника.
        // analysis_1/analysis_2 намеренно не делаем обязательными:
        // их наличие/пустота определяется текстом AI-инструкции, а не кодом.
        $schema = new Schema(
            type: DataType::OBJECT,
            properties: [
                'summary' => new Schema(
                    type: DataType::STRING,
                    description: 'КРАТКОЕ СОДЕРЖАНИЕ РАЗГОВОРА'
                ),
                'analysis_1' => new Schema(
                    type: DataType::STRING,
                    description: 'АНАЛИЗ ЗВОНКА 1'
                ),
                'analysis_2' => new Schema(
                    type: DataType::STRING,
                    description: 'АНАЛИЗ ЗВОНКА 2'
                ),
                'caller_type' => new Schema(
                    type: DataType::STRING,
                    description: 'ТИП РАЗГОВОРА'
                ),
            ],
            required: ['summary', 'caller_type']
        );

        $response = self::buildModel($systemInstructionText, self::MODEL)
            ->withGenerationConfig(
                new GenerationConfig(
                    responseMimeType: ResponseMimeType::APPLICATION_JSON,
                    responseSchema: $schema
                )
            )
            ->generateContent([
                $userPromptText,
                // К анализу прикладываем исходную аудиозапись, как и на шаге транскрибации.
                $audioFile,
            ]);

        $result = $response->json(true);

        // Для summary и caller_type поле обязательно и не может быть пустым.
        foreach (['summary', 'caller_type'] as $field) {
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

        // Поддерживаем оба формата: новый (analysis_1/analysis_2) и старый (analysis).
        // Любое пустое/отсутствующее значение анализов нормализуем в null.
        $analysis1 = self::normalizeOptionalText($result['analysis_1'] ?? $result['analysis'] ?? null);
        $analysis2 = self::normalizeOptionalText($result['analysis_2'] ?? null);

        return [
            'summary' => trim($result['summary']),
            'analysis_1' => $analysis1,
            'analysis_2' => $analysis2,
            'caller_type' => $callerType->value,
            // Фиксируем фактические instruction/prompt после Blade-рендера (как в отчетах).
            'instruction' => self::mergeCallInstruction(
                $call->instruction,
                'analysis',
                self::buildInstructionSnapshot($systemInstructionText, $userPromptText),
                self::MODEL,
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
                ...CallPromptPhonesBuilder::build($call),
            ]);
    }

    private static function normalizeOptionalText(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $normalized = stripslashes(trim($value));

        return $normalized === '' ? null : $normalized;
    }

}
