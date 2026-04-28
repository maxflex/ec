<?php

namespace App\Utils\AI;

use App\Models\AiPrompt;
use App\Models\Call;
use Gemini\Data\GenerationConfig;
use RuntimeException;
use ValueError;

class CallTranscriptionService extends GeminiService
{
    // Для ASR временно фиксируем отдельную модель, не затрагивая остальные AI-сценарии.
    private const string MODEL = 'gemini-3.1-pro-preview';

    // Минимальная температура: делаем распознавание максимально детерминированным.
    private const float TRANSCRIPTION_TEMPERATURE = 0.1;

    // 14 апреля 2026 в 12:00 включили сохранение записей звонков в стерео.
    // private const string STEREO_RECORDING_ENABLED_AT = '2026-04-14 12:00:00';

    /**
     * Шаг 1: ASR-процесс (audio -> transcript, plain text).
     *
     * @return array{
     *     transcript: string,
     *     instruction: array{
     *         transcript: array{text: string, model: string, created_at: string}|null,
     *         analysis: array{text: string, model: string, created_at: string}|null
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
        $response = self::buildModel($systemInstructionText, self::MODEL)
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
            'instruction' => self::mergeCallInstruction(
                $call->instruction,
                'transcript',
                self::buildInstructionSnapshot($systemInstructionText, $userPromptText),
                self::MODEL,
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
        // Используем единую инструкцию транскрибации без ветвления по типу записи.
        return app(AiPromptRenderer::class)->renderInstructionAndPromptById(
            AiPrompt::CALL_TRANSCRIPTION, [
                'call' => $call,
                ...CallPromptPhonesBuilder::build($call),
            ]);
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

}
