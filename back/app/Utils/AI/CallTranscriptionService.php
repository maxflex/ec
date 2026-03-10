<?php

namespace App\Utils\AI;

use App\Models\AiPrompt;
use App\Models\Call;
use Gemini\Data\Blob;
use Gemini\Enums\MimeType;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use ValueError;

class CallTranscriptionService extends GeminiService
{
    /**
     * Шаг 1: ASR-процесс (audio -> transcript, plain text).
     *
     * @return array{transcript: string}
     */
    public static function transcribeAudio(Call $call): array
    {
        if (! $call->has_recording) {
            throw new RuntimeException("Нельзя транскрибировать звонок {$call->id}: нет recording_id");
        }

        [$systemInstructionText, $userPromptText] = self::renderCallTranscriptionPrompt($call);
        $promptGeminiFiles = GeminiFileService::getPromptGeminiFiles(AiPrompt::CALL_TRANSCRIPTION);
        $audioBytes = self::downloadRecording($call);

        // На первом шаге intentionally без JSON-схемы: ожидаем plain text транскрипта.
        $response = self::buildModel($systemInstructionText)
            ->generateContent([
                $userPromptText,
                ...$promptGeminiFiles,
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
                'aon' => Call::aon($call->number),
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

    /**
     * Скачивает запись Mango по прямой ссылке и возвращает бинарные данные.
     */
    private static function downloadRecording(Call $call): string
    {
        $recording = $call->getRecording('download');

        $response = Http::withOptions([
            'proxy' => '37.140.195.195:8888',
            'verify' => false,
            'timeout' => 180,
        ])->get($recording);

        if (! $response->successful()) {
            throw new RuntimeException("Не удалось скачать запись звонка {$call->id} (HTTP {$response->status()})");
        }

        $body = $response->body();
        if ($body === '') {
            throw new RuntimeException("Получен пустой аудиофайл для звонка {$call->id}");
        }

        return $body;
    }
}
