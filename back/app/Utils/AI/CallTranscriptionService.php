<?php

namespace App\Utils\AI;

use App\Models\AiPrompt;
use App\Models\Call;
use Gemini\Data\Blob;
use Gemini\Enums\MimeType;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use ValueError;

class CallTranscriptionService extends GeminiService
{
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
        $audioBytes = self::downloadRecording($call);

        // На первом шаге intentionally без JSON-схемы: ожидаем plain text транскрипта.
        $response = self::buildModel($systemInstructionText)
            ->generateContent([
                $userPromptText,
                new Blob(
                    mimeType: MimeType::AUDIO_MP3,
                    data: base64_encode($audioBytes),
                ),
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
        return app(AiPromptRenderer::class)->renderInstructionAndPromptById(
            AiPrompt::CALL_TRANSCRIPTION, [
                'call' => $call,
                ...CallPromptPhonesBuilder::build($call),
            ]);
    }

    /**
     * Загружает запись звонка из нашего Storage и возвращает бинарные данные.
     */
    private static function downloadRecording(Call $call): string
    {
        $path = $call->getRecordingStoragePath();

        if (! Storage::exists($path)) {
            throw new RuntimeException("Не найден аудиофайл звонка {$call->id} в Storage по пути {$path}");
        }

        $body = Storage::get($path);
        if ($body === '') {
            throw new RuntimeException("Получен пустой аудиофайл для звонка {$call->id}");
        }

        return $body;
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
