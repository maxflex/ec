<?php

namespace App\Utils\AI;

use App\Models\AiPrompt;
use App\Models\Call;
use Gemini\Data\GenerationConfig;
use RuntimeException;
use ValueError;

class CallTranscriptionService extends GeminiService
{
    // 14 апреля 2026 в 12:00 включили сохранение записей звонков в стерео.
    private const string STEREO_RECORDING_ENABLED_AT = '2026-04-14 12:00:00';

    // Для ASR временно фиксируем отдельную модель, не затрагивая остальные AI-сценарии.
    private const string TRANSCRIPTION_MODEL = 'gemini-3.1-pro-preview';

    // Минимальная температура: делаем распознавание максимально детерминированным.
    private const float TRANSCRIPTION_TEMPERATURE = 0.1;

    /**
     * Шаг 1: ASR-процесс (audio -> transcript, plain text).
     *
     * @return array{
     *     transcript: string,
     *     instruction: array{
     *         transcription: array{text: string|null, created_at: string|null},
     *         analysis: array{text: string|null, created_at: string|null}
     *     }
     * }
     */
    public static function transcribeAudio(Call $call): array
    {
        if (! $call->has_recording) {
            throw new RuntimeException("Нельзя транскрибировать звонок {$call->id}: нет recording_id");
        }

        [$systemInstructionText, $userPromptText] = self::renderCallTranscriptionPrompt($call);
        $audioFile = CallAudioFileCacheService::getOrCreateUploadedFile($call);

        // На первом шаге intentionally без JSON-схемы: ожидаем plain text транскрипта.
        $response = self::buildModel($systemInstructionText, self::TRANSCRIPTION_MODEL)
            ->withGenerationConfig(new GenerationConfig(
                temperature: self::TRANSCRIPTION_TEMPERATURE,
            ))
            ->generateContent([
                $userPromptText,
                $audioFile,
            ]);

        $transcript = self::extractResponseText($response);
        if (trim($transcript) === '') {
            throw new RuntimeException("Gemini вернул пустой транскрипт для звонка {$call->id}");
        }

        return [
            'transcript' => trim($transcript),
            // Фиксируем фактические instruction/prompt после Blade-рендера (как в отчетах).
            'instruction' => self::buildMergedInstruction(
                $call,
                'transcription',
                self::buildInstructionSnapshot($systemInstructionText, $userPromptText)
            ),
        ];
    }

    /**
     * Рендер prompt-пары для шага 1 (только ASR/транскрипт).
     *
     * @return array{0: string, 1: string}
     */
    private static function renderCallTranscriptionPrompt(Call $call): array
    {
        $transcriptionPromptId = self::resolveTranscriptionPromptId($call);

        return app(AiPromptRenderer::class)->renderInstructionAndPromptById(
            $transcriptionPromptId, [
                'call' => $call,
                ...CallPromptPhonesBuilder::build($call),
            ]);
    }

    /**
     * До включения стерео используем mono-prompt, после — stereo-prompt.
     */
    private static function resolveTranscriptionPromptId(Call $call): int
    {
        if (! $call->created_at) {
            return AiPrompt::CALL_TRANSCRIPTION_STEREO;
        }

        return $call->created_at < self::STEREO_RECORDING_ENABLED_AT
            ? AiPrompt::CALL_TRANSCRIPTION_MONO
            : AiPrompt::CALL_TRANSCRIPTION_STEREO;
    }

    /**
     * Безопасно извлекает текст из ответа Gemini, включая multi-part случаи.
     */
    private static function extractResponseText(object $response): string
    {
        try {
            return $response->text();
        } catch (ValueError) {
            $lastPartText = collect($response->parts())->last()?->text;

            return is_string($lastPartText) ? $lastPartText : '';
        }
    }

    /**
     * @param  'transcription'|'analysis'  $key
     * @return array{
     *     transcription: array{text: string|null, created_at: string|null},
     *     analysis: array{text: string|null, created_at: string|null}
     * }
     */
    private static function buildMergedInstruction(Call $call, string $key, string $snapshot): array
    {
        $currentInstruction = is_array($call->instruction) ? $call->instruction : [];

        // Ключи фиксированные, чтобы формат JSON был предсказуемым.
        $normalizedInstruction = [
            'transcription' => isset($currentInstruction['transcription']) && is_array($currentInstruction['transcription'])
                ? $currentInstruction['transcription']
                : ['text' => null, 'created_at' => null],
            'analysis' => isset($currentInstruction['analysis']) && is_array($currentInstruction['analysis'])
                ? $currentInstruction['analysis']
                : ['text' => null, 'created_at' => null],
        ];

        // При каждом новом прогоне фиксируем фактическое время генерации снапшота.
        $normalizedInstruction[$key] = [
            'text' => $snapshot,
            'created_at' => now()->format('Y-m-d H:i:s'),
        ];

        return $normalizedInstruction;
    }

    private static function buildInstructionSnapshot(string $systemInstructionText, string $userPromptText): string
    {
        return trim($systemInstructionText)."\n\n<USER_PROMPT>\n\n".trim($userPromptText);
    }
}
